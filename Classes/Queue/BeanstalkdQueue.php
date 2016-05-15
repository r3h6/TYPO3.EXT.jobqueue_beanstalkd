<?php

namespace R3H6\JobqueueBeanstalkd\Queue;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 3 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use R3H6\Jobqueue\Queue\Message;
use R3H6\Jobqueue\Queue\QueueInterface;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * BeanstalkdQueue
 */
class BeanstalkdQueue implements QueueInterface
{
    /**
     * @var Pheanstalk\Pheanstalk
     */
    protected $client = null;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = [
        'host' => '127.0.0.1',
        'port' => PheanstalkInterface::DEFAULT_PORT,
        'timeout' => 1,
    ];
    /**
     * Constructor
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, array $options = array())
    {
        $this->name = $name;
        ArrayUtility::mergeRecursiveWithOverrule($this->options, $options, true, false);
        $this->client = new Pheanstalk($this->options['host'], $this->options['port']);
    }

    /**
     * @param Message $message
     */
    public function publish(Message $message)
    {
        $encodedMessage = $this->encodeMessage($message);
        $messageIdentifier = $this->client->putInTube($this->name, $encodedMessage, $message->getDelay());
        $message->setIdentifier($messageIdentifier);
        $message->setState(Message::STATE_PUBLISHED);
    }

    /**
     * @param int $timeout
     * @return Message
     */
    public function waitAndTake($timeout = null)
    {
        if ($timeout === null) {
            $timeout = $this->options['timeout'];
        }
        $pheanstalkJob = $this->client->reserveFromTube($this->name, $timeout);
        if ($pheanstalkJob === null || $pheanstalkJob === false) {
            return null;
        }
        $message = $this->decodeMessage($pheanstalkJob->getData());
        $message->setIdentifier($pheanstalkJob->getId());
        $this->client->delete($pheanstalkJob);
        $message->setState(Message::STATE_DONE);
        return $message;
    }

    /**
     * @param int $timeout
     * @return Message
     */
    public function waitAndReserve($timeout = null)
    {
        if ($timeout === null) {
            $timeout = $this->options['timeout'];
        }
        $pheanstalkJob = $this->client->reserveFromTube($this->name, $timeout);
        if ($pheanstalkJob === null || $pheanstalkJob === false) {
            return null;
        }
        $message = $this->decodeMessage($pheanstalkJob->getData());
        $message->setIdentifier($pheanstalkJob->getId());
        return $message;
    }

    /**
     * @param Message $message
     */
    public function finish(Message $message)
    {
        $messageIdentifier = $message->getIdentifier();
        $pheanstalkJob = $this->client->peek($messageIdentifier);
        $this->client->delete($pheanstalkJob);
        $message->setState(Message::STATE_DONE);
        return true;
    }

    /**
     * @param int $limit
     * @return array<\R3H6\Jobqueue\Queue\Message>
     */
    public function peek($limit = 1)
    {
        if ($limit !== 1) {
            throw new JobqueueException('The beanstalkd Jobqueue implementation currently only supports to peek one job at a time', 1352717703);
        }
        try {
            $pheanstalkJob = $this->client->peekReady($this->name);
        } catch (ServerException $exception) {
            return array();
        }
        if ($pheanstalkJob === null || $pheanstalkJob === false) {
            return array();
        }

        $message = $this->decodeMessage($pheanstalkJob->getData());
        $message->setIdentifier($pheanstalkJob->getId());
        $message->setState(Message::STATE_PUBLISHED);
        return array($message);
    }

    /**
     * @return array
     */
    public function getMessage($identifier)
    {
        $pheanstalkJob = $this->client->peek($identifier);
        return $this->decodeMessage($pheanstalkJob->getData());
    }

    /**
     * @return int
     */
    public function count()
    {
        $clientStats = $this->client->statsTube($this->name);
        return (integer)$clientStats['current-jobs-ready'];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Pheanstalk\Pheanstalk
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Encode a message
     *
     * Updates the original value property of the message to resemble the
     * encoded representation.
     *
     * @param R3H6\Jobqueue\Queue\Message $message
     * @return string
     */
    protected function encodeMessage(Message $message)
    {
        $value = json_encode($message->toArray());
        return $value;
    }

    /**
     * Decode a message from a string representation
     *
     * @param string $value
     * @return R3H6\Jobqueue\Queue\Message
     */
    protected function decodeMessage($value)
    {
        $decodedMessage = json_decode($value, true);
        $message = new Message(
            $decodedMessage['payload'],
            $decodedMessage['identifier']
        );

        $message->setState($decodedMessage['state']);
        $message->setAttemps($decodedMessage['attemps']);

        return $message;
    }
}

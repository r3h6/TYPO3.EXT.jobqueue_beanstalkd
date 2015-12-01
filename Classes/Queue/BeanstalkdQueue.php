<?php

namespace TYPO3\JobqueueBeanstalkd\Queue;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Queue\QueueInterface;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;

class BeanstalkdQueue implements QueueInterface
{
    /**
     * @var Pheanstalk\Pheanstalk
     * @inject
     */
    protected $client = null;

    protected $name;

    public function __construct($name, $options)
    {
        $this->name = $name;
        $this->options = ArrayUtility::mergeRecursiveWithOverrule([
            'host' => '127.0.0.1',
            'port' => PheanstalkInterface::DEFAULT_PORT,
            'connectTimeout' => null
        ], (array) $options, false, false);

        $this->client = new Pheanstalk($this->options['host'], $this->options['port'], $this->options['connectTimeout']);
    }

    public function publish(Message $message)
    {
        $encodedMessage = $this->encodeMessage($message);
        $messageIdentifier = $this->client->putInTube($this->name, $encodedMessage);
        $message->setIdentifier($messageIdentifier);
        $message->setState(Message::STATE_PUBLISHED);
    }

    public function waitAndTake($timeout = null)
    {
        if ($timeout === null) {
            $timeout = $this->options['connectTimeout'];
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

    public function waitAndReserve($timeout = null)
    {
        if ($timeout === null) {
            $timeout = $this->options['connectTimeout'];
        }
        $pheanstalkJob = $this->client->reserveFromTube($this->name, $timeout);
        if ($pheanstalkJob === null || $pheanstalkJob === false) {
            return null;
        }
        $message = $this->decodeMessage($pheanstalkJob->getData());
        $message->setIdentifier($pheanstalkJob->getId());
        return $message;
    }

    public function finish(Message $message)
    {
        $messageIdentifier = $message->getIdentifier();
        $pheanstalkJob = $this->client->peek($messageIdentifier);
        $this->client->delete($pheanstalkJob);
        $message->setState(Message::STATE_DONE);
        return true;
    }

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

    public function getMessage($identifier)
    {
        $pheanstalkJob = $this->client->peek($identifier);
        return $this->decodeMessage($pheanstalkJob->getData());
    }

    public function count()
    {
        $clientStats = $this->client->statsTube($this->name);
        return (integer)$clientStats['current-jobs-ready'];
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Encode a message
     *
     * Updates the original value property of the message to resemble the
     * encoded representation.
     *
     * @param TYPO3\Jobqueue\Queue\Message $message
     * @return string
     */
    protected function encodeMessage(Message $message)
    {
        $value = json_encode($message->toArray());
        $message->setOriginalValue($value);
        return $value;
    }

    /**
     * Decode a message from a string representation
     *
     * @param string $value
     * @return TYPO3\Jobqueue\Queue\Message
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

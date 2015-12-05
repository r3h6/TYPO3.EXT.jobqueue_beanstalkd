<?php
namespace TYPO3\JobqueueBeanstalkd\Tests\Functional\Queue;

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

use Pheanstalk\Pheanstalk;
use TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Functional test for BeanstalkdQueue
 */
class BeanstalkdQueueTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase
{
    const QUEUE_NAME = 'TestQueue';

    protected $coreExtensionsToLoad = array('extbase');
    protected $testExtensionsToLoad = array('typo3conf/ext/jobqueue', 'typo3conf/ext/jobqueue_beanstalkd');

    /**
     * @var TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue
     */
    protected $queue;

    // {{{ Bugfix phpunit
    protected $disallowChangesToGlobalState = null;

    public function setDisallowChangesToGlobalState($disallowChangesToGlobalState)
    {
        if (is_null($this->disallowChangesToGlobalState) && is_bool($disallowChangesToGlobalState)) {
            $this->disallowChangesToGlobalState = $disallowChangesToGlobalState;
        }
    }
    // }}}

    /**
     * Set up dependencies
     */
    public function setUp()
    {
        parent::setUp();
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // $a = new BeanstalkdQueue();

        $this->queue = $this->objectManager->get(BeanstalkdQueue::class, self::QUEUE_NAME, null);

       /** @var Pheanstalk\Pheanstalk $client */
        $client = $this->queue->getClient();

        // flush queue:
        try {
            while (true) {
                $job = $client->peekDelayed(self::QUEUE_NAME);
                $client->delete($job);
            }
        } catch (\Exception $e) {
        }
        try {
            while (true) {
                $job = $client->peekBuried(self::QUEUE_NAME);
                $client->delete($job);
            }
        } catch (\Exception $e) {
        }
        try {
            while (true) {
                $job = $client->peekReady(self::QUEUE_NAME);
                $client->delete($job);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @test
     */
    public function publishAndWaitWithMessageWorks()
    {
        $message = new Message('Yeah, tell someone it works!');
        $this->queue->publish($message);

        $result = $this->queue->waitAndTake(1);
        $this->assertNotNull($result, 'wait should receive message');
        $this->assertEquals($message->getPayload(), $result->getPayload(), 'message should have payload as before');
    }

    /**
     * @test
     */
    public function waitForMessageTimesOut()
    {
        $result = $this->queue->waitAndTake(1);
        $this->assertNull($result, 'wait should return NULL after timeout');
    }

    /**
     * @test
     */
    public function peekReturnsNextMessagesIfQueueHasMessages()
    {
        $message = new Message('First message');
        $this->queue->publish($message);
        $message = new Message('Another message');
        $this->queue->publish($message);

        $results = $this->queue->peek(1);
        $this->assertEquals(1, count($results), 'peek should return a message');
        /** @var Message $result */
        $result = $results[0];
        $this->assertEquals('First message', $result->getPayload());
        $this->assertEquals(Message::STATE_PUBLISHED, $result->getState(), 'Message state should be published');

        $results = $this->queue->peek(1);
        $this->assertEquals(1, count($results), 'peek should return a message again');
        $result = $results[0];
        $this->assertEquals('First message', $result->getPayload(), 'second peek should return the same message again');
    }

    /**
     * @test
     */
    public function peekReturnsNullIfQueueHasNoMessage()
    {
        $result = $this->queue->peek();
        $this->assertEquals(array(), $result, 'peek should not return a message');
    }

    /**
     * @test
     */
    public function waitAndReserveWithFinishRemovesMessage()
    {
        $message = new Message('First message');
        $this->queue->publish($message);


        $result = $this->queue->waitAndReserve(1);
        $this->assertNotNull($result, 'waitAndReserve should receive message');
        $this->assertEquals($message->getPayload(), $result->getPayload(), 'message should have payload as before');

        $result = $this->queue->peek();
        $this->assertEquals(array(), $result, 'no message should be present in queue');

        $finishResult = $this->queue->finish($message);
        $this->assertTrue($finishResult, 'message should be finished');
    }

    /**
     * @test
     */
    public function countReturnsZeroByDefault()
    {
        $this->assertSame(0, $this->queue->count());
    }

    /**
     * @test
     */
    public function countReturnsNumberOfReadyJobs()
    {
        $message1 = new Message('First message');
        $this->queue->publish($message1);

        $message2 = new Message('Second message');
        $this->queue->publish($message2);

        $this->assertSame(2, $this->queue->count());
    }
}

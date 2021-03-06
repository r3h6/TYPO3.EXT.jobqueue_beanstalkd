<?php
namespace R3H6\JobqueueBeanstalkd\Tests\Functional\Queue;

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
use R3H6\JobqueueBeanstalkd\Queue\BeanstalkdQueue;
use R3H6\Jobqueue\Queue\Message;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Functional test for BeanstalkdQueue
 */
class BeanstalkdQueueTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase
{
    use \R3H6\Jobqueue\Tests\Functional\Queue\QueueTestTrait;
    use \R3H6\Jobqueue\Tests\Functional\Queue\QueueDelayTestTrait;

    const QUEUE_NAME = 'TestQueue';

    protected $coreExtensionsToLoad = array('extbase');
    protected $testExtensionsToLoad = array('typo3conf/ext/jobqueue', 'typo3conf/ext/jobqueue_beanstalkd');

    /**
     * @var TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var R3H6\JobqueueBeanstalkd\Queue\BeanstalkdQueue
     */
    protected $queue;

    /**
     * Set up dependencies
     */
    public function setUp()
    {
        parent::setUp();
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->queue = $this->objectManager->get(BeanstalkdQueue::class, self::QUEUE_NAME, []);

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
}

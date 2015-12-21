*******************
Jobqueue Beanstalkd
*******************

Implements concrete Queue for the beanstalkd workqueue. Requires the exension *jobqueue* to be installed.

.. important::
    If you install this extension not over composer you must install pda/pheanstalk by yourself.



Configuration
-------------

In order to use this queue you should set the *defaultQueue* to ``TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue`` in the *jobqueue* extension settings.
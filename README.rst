.. _start:

*******************
Jobqueue Beanstalkd
*******************

Implements concrete Queue for the beanstalkd workqueue. Requires the exension *jobqueue* to be installed.

Configuration
-------------

In order to use this queue you should set the *defaultQueue* to ``TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue`` in the *jobqueue* extension settings.
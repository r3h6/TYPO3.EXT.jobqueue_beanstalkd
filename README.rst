.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. _start:

=============
Documentation
=============

Job queues for TYPO3. Implements concrete queue for the beanstalkd workqueue. Requires the exension *jobqueue* to be installed.


Configuration
-------------

In order to use this queue you should set the *defaultQueue* to ``TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue`` in the *jobqueue* extension settings.
.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. _start:

=============
Documentation
=============

Job queues for TYPO3 CMS. Implements concrete queue for the beanstalkd workqueue. Requires the exension *jobqueue* to be installed.

This extension is a backport of the flow package Flowpack/jobqueue-beanstalkd.


Configuration
-------------

In order to use this queue you should set the *defaultQueue* to ``TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue`` in the *jobqueue* extension settings.
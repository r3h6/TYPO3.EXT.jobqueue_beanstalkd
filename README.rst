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


Installation
------------

If you are using composer you can also require the package ``"pda/pheanstalk": "3.0.*"``.
If not, the provided pheanstalk phar archive will be used instead, perhaps this is not the most recent version of the library.


Configuration
-------------

In order to use this queue you should set the *defaultQueue* to ``TYPO3\JobqueueBeanstalkd\Queue\BeanstalkdQueue`` in the *jobqueue* extension settings.
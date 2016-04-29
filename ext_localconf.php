<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'] = [];
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue']['TYPO3\\JobqueueBeanstalkd\\Queue\\BeanstalkdQueue'] = (array) unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (!class_exists('Pheanstalk\\Pheanstalk', true)) {
    require_once \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:' . $_EXTKEY . '/Resources/Private/Vendors/pheanstalk.phar');
}

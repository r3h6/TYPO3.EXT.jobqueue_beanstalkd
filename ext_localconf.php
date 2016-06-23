<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'] = [];
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue']['R3H6\\JobqueueBeanstalkd\\Queue\\BeanstalkdQueue'] = [
    'options' => (array) unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]),
];

if (!class_exists('Pheanstalk\\Pheanstalk', true)) {
    require_once 'phar://' . \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:' . $_EXTKEY . '/Resources/Private/Vendors/pheanstalk.phar/autoload.php');
}

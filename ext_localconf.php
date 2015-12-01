<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['TYPO3\\JobqueueBeanstalkd\\Queue\\BeanstalkdQueue'] = (array) unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
} else {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['TYPO3\\JobqueueBeanstalkd\\Queue\\BeanstalkdQueue'] = [];
}

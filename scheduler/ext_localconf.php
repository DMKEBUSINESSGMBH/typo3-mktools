<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mktools_scheduler_GenerateRealUrlConfigurationFile'] = [
        'extension' => $_EXTKEY,
        'title' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:scheduler_GenerateRealUrlConfigurationFile_name',
        'description' => 'LLL:EXT:'.$_EXTKEY.'/locallang_db.xml:scheduler_GenerateRealUrlConfigurationFile_description',
    ];
}

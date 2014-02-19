<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mktools_scheduler_GenerateRealUrlConfigurationFile'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:scheduler_GenerateRealUrlConfigurationFile_name',
	'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xml:scheduler_GenerateRealUrlConfigurationFile_description'
);

<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

tx_rnbase::load('tx_mktools_util_miscTools');
if (!tx_mktools_util_miscTools::loadFixedPostVarTypesTable()) {
	return;
}

$TCA['tx_mktools_fixedpostvartypes'] = array (
	'ctrl' => $TCA['tx_mktools_fixedpostvartypes']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,title,identifier',
		'maxDBListItems' => '10'
	),
	'feInterface' => $TCA['tx_mktools_fixedpostvartypes']['feInterface'],
	'columns' => array (
		'hidden' => array (
			'exclude' => 0,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'	=> 'check',
				'default' => '0'
			)
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes.title',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => 'required,trim',
			)
		),
		'identifier' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes.identifier',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, title,identifier'
					)
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
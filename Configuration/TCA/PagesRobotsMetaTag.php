<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');

// pages erweitern
t3lib_div::loadTCA('pages');
$fields = array(
	'mkrobotsmetatag' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:mktools/locallang_db.xml:pages.tx_mktools_mkrobotsmetatag',
		'config' => array (
			'type' => 'select',
			'items' =>  tx_mktools_util_SeoRobotsMetaTag::getOptionsForTca(),
			'size' => 1,
			'maxitems' => 1,
		),
	),
);
t3lib_extMgm::addTCAcolumns('pages', $fields, 1);
t3lib_extMgm::addToAllTCAtypes('pages','mkrobotsmetatag','');

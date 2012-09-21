<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mktools_util_miscTools');

////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mktools']='layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mktools']='pi_flexform';

t3lib_extMgm::addPiFlexFormValue('tx_mktools','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');
t3lib_extMgm::addPlugin(Array('LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.mktools.label','tx_mktools'));

t3lib_extMgm::addStaticFile($_EXTKEY,'Configuration/TypoScript/action/', 'MK Tools - Show Template');


// default TS für den content replacer
if(tx_mktools_util_miscTools::isContentReplacerActive()) {
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');
}


// Robots-Meta Tag
if(tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
	
	tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');
	
	// default TS
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/seorobotsmetatag', 'MK Tools - SEO Robots Meta Tag');

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
}
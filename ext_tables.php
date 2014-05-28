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
t3lib_extMgm::addStaticFile($_EXTKEY,'Configuration/TypoScript/onsiteseo/', 'MK Tools - Onsite Seo');
t3lib_extMgm::addStaticFile($_EXTKEY,'Configuration/TypoScript/tsbasic/', 'MK Tools - Basis TypoScript');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentrenderer', 'MK Tools - Ajax Content Renderer');

// default TS für den content replacer
if(tx_mktools_util_miscTools::isContentReplacerActive()) {
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');
}


t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentmodal', 'MK Tools - Ajax Modal Renderer');

// Robots-Meta Tag
if(tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {

	tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');

	// default TS
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/seorobotsmetatag', 'MK Tools - SEO Robots Meta Tag');
	require(t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/PagesRobotsMetaTag.php');
}

// realurl optimierungen
if(tx_mktools_util_miscTools::loadFixedPostVarTypesTable()) {

	global $TCA;
	$TCA['tx_mktools_fixedpostvartypes'] = array (
		'ctrl' => array (
			'title'     => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes',
			'label'     => 'title',
			'default_sortby' => 'ORDER BY title',
			'delete' => 'deleted',
			'enablecolumns' => array (
				'disabled' => 'hidden',
			),
			'tstamp'    => 'tstamp',
			'crdate'    => 'crdate',
			'cruser_id' => 'cruser_id',
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/FixedPostVarTypes.php',
			'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
		),
	);
	require(t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/PagesFixedPostVarType.php');
}
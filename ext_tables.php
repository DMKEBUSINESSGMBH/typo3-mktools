<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }


tx_rnbase::load('tx_mktools_util_miscTools');

////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mktools']='layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mktools']='pi_flexform';

tx_rnbase_util_Extensions::addPiFlexFormValue('tx_mktools','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');
tx_rnbase_util_Extensions::addPlugin(Array('LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.mktools.label','tx_mktools'));

tx_rnbase_util_Extensions::addStaticFile($_EXTKEY,'Configuration/TypoScript/action/', 'MK Tools - Show Template');
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY,'Configuration/TypoScript/onsiteseo/', 'MK Tools - Onsite Seo');
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY,'Configuration/TypoScript/tsbasic/', 'MK Tools - Basis TypoScript');
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentrenderer', 'MK Tools - Ajax Content Renderer');

// default TS für den content replacer
if(tx_mktools_util_miscTools::isContentReplacerActive()) {
	tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');
}


tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentmodal', 'MK Tools - Ajax Modal Renderer');

// Robots-Meta Tag
if(tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {

	tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');

	// default TS
	tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'Configuration/TypoScript/seorobotsmetatag', 'MK Tools - SEO Robots Meta Tag');
	require(tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/PagesRobotsMetaTag.php');
}

// realurl optimierungen
if(tx_mktools_util_miscTools::loadFixedPostVarTypesTable()) {

	global $TCA;
	$TCA['tx_mktools_fixedpostvartypes'] = array (
		'ctrl' => array (
			'title' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes',
			'label' => 'title',
			'default_sortby' => 'ORDER BY title',
			'delete' => 'deleted',
			'enablecolumns' => array (
				'disabled' => 'hidden',
			),
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'dynamicConfigFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/FixedPostVarTypes.php',
			'iconfile' => 'EXT:mktools/ext_icon.gif',
		),
	);
	require(tx_rnbase_util_Extensions::extPath($_EXTKEY).'Configuration/TCA/PagesFixedPostVarType.php');
}

if(tx_mktools_util_miscTools::shouldFalImagesBeAddedToCalEvent()) {
	// default TS
	tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'Configuration/TypoScript/cal', 'MK Tools - FAL Images für Cal Event');
}

if(tx_mktools_util_miscTools::shouldFalImagesBeAddedToTtNews()) {
	// default TS
	tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'Configuration/TypoScript/tt_news', 'MK Tools - FAL Images für TT_News');
}

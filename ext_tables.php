<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mktools'] = 'layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mktools'] = 'pi_flexform';

tx_rnbase_util_Extensions::addPiFlexFormValue('tx_mktools', 'FILE:EXT:mktools/flexform_main.xml');
tx_rnbase_util_Extensions::addPlugin(
    ['LLL:EXT:mktools/locallang_db.php:plugin.mktools.label', 'tx_mktools'],
    'list_type',
    'mktools'
);

tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/action/', 'MK Tools - Actions');
tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/onsiteseo/', 'MK Tools - Onsite Seo');
tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/tsbasic/', 'MK Tools - Basis TypoScript');
tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/contentrenderer', 'MK Tools - Ajax Content Renderer');

// default TS für den content replacer
if (tx_mktools_util_miscTools::isContentReplacerActive()) {
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');
}

tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/contentmodal', 'MK Tools - Ajax Modal Renderer');

// Robots-Meta Tag
if (tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
    // default TS
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/seorobotsmetatag', 'MK Tools - SEO Robots Meta Tag');
}

if (tx_mktools_util_miscTools::shouldFalImagesBeAddedToCalEvent()) {
    // default TS
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/cal', 'MK Tools - FAL Images für Cal Event');
}

if (tx_mktools_util_miscTools::shouldFalImagesBeAddedToTtNews()) {
    // default TS
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/tt_news', 'MK Tools - FAL Images für TT_News');
}

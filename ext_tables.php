<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

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
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');

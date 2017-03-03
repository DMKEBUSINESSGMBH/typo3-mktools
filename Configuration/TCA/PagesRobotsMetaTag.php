<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }


tx_rnbase::load('tx_mktools_util_miscTools');
if (!tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
	return;
}

tx_rnbase::load('tx_rnbase_util_TCA');
tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');

// pages erweitern
tx_rnbase_util_TCA::loadTCA('pages');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase_util_Extensions::addTCAcolumns(
	'pages',
	array(
		'mkrobotsmetatag' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:mktools/locallang_db.xml:pages.tx_mktools_mkrobotsmetatag',
			'config' => array (
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' =>  tx_mktools_util_SeoRobotsMetaTag::getOptionsForTca(),
				'size' => 1,
				'maxitems' => 1,
			),
		),
	),
	!tx_rnbase_util_TYPO3::isTYPO62OrHigher()
);
tx_rnbase_util_Extensions::addToAllTCAtypes('pages','mkrobotsmetatag','');

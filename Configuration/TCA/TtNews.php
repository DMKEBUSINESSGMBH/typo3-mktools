<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

t3lib_div::loadTCA('tt_news');
tx_rnbase::load('tx_rnbase_util_TSFAL');
$fields = array(
	'tx_mktools_fal_images' => tx_rnbase_util_TSFAL::getMediaTCA(
		'tx_mktools_fal_images',
		array('label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_images')
	),
	'tx_mktools_fal_media' => tx_rnbase_util_TSFAL::getMediaTCA(
		'tx_mktools_fal_media',
		array(
			'type' => 'media',
			'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_media'
		)
	),
);
t3lib_extMgm::addTCAcolumns('tt_news', $fields, 1);
t3lib_extMgm::addToAllTCAtypes(
	'tt_news', 'tx_mktools_fal_images,tx_mktools_fal_media', '', 'after:image'
);
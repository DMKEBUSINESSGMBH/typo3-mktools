<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

t3lib_div::loadTCA('tx_cal_event');
tx_rnbase::load('tx_rnbase_util_TSFAL');
$fields = array(
	'tx_mktools_fal_images' => tx_rnbase_util_TSFAL::getMediaTCA(
		'tx_mktools_fal_images',
		array('label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_images')
	)
);
t3lib_extMgm::addTCAcolumns('tx_cal_event', $fields, 1);
t3lib_extMgm::addToAllTCAtypes('tx_cal_event', 'tx_mktools_fal_images', '', 'after:image');
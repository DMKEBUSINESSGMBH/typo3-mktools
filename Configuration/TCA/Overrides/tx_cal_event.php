<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

if (!tx_mktools_util_miscTools::shouldFalImagesBeAddedToCalEvent()) {
    return;
}
tx_rnbase_util_Extensions::addTCAcolumns(
    'tx_cal_event',
    [
        'tx_mktools_fal_images' => tx_rnbase_util_TSFAL::getMediaTCA(
            'tx_mktools_fal_images',
            ['label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_images']
        ),
    ],
    false
);
tx_rnbase_util_Extensions::addToAllTCAtypes('tx_cal_event', 'tx_mktools_fal_images', '', 'after:image');

<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

if (!tx_mktools_util_miscTools::shouldFalImagesBeAddedToCalEvent()) {
    return;
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_cal_event',
    [
        'tx_mktools_fal_images' => \Sys25\RnBase\Utility\TSFAL::getMediaTCA(
            'tx_mktools_fal_images',
            ['label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fal_images']
        ),
    ],
    false
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_cal_event', 'tx_mktools_fal_images', '', 'after:image');

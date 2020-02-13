<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (tx_mktools_util_miscTools::isAjaxContentRendererActive()) {
    tx_rnbase_util_Extensions::addTCAcolumns(
        'tt_content',
        [
            'tx_mktools_load_with_ajax' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_load_with_ajax',
                'config' => [
                    'type' => 'check',
                    'default' => '0',
                ],
            ],
        ],
        false
    );

    tx_rnbase_util_Extensions::addToAllTCAtypes('tt_content', 'tx_mktools_load_with_ajax', 'list');
}

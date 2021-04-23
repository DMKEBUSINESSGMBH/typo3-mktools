<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
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

call_user_func(function () {
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['tx_mktools'] = 'layout,select_key,pages';

    // Das tt_content-Feld pi_flexform einblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tx_mktools'] = 'pi_flexform';

    tx_rnbase_util_Extensions::addPiFlexFormValue('tx_mktools', 'FILE:EXT:mktools/flexform_main.xml');
    tx_rnbase_util_Extensions::addPlugin(
        ['LLL:EXT:mktools/locallang_db.php:plugin.mktools.label', 'tx_mktools'],
        'list_type',
        'mktools'
    );
});

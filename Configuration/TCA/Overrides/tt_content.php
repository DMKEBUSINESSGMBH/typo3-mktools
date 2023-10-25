<?php

if (!defined('TYPO3')) {
    exit('Access denied.');
}

if (\DMK\Mktools\Utility\Misc::isAjaxContentRendererActive()) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'tx_mktools_load_with_ajax' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/Resources/Private/Language/locallang_db.xlf:tx_mktools_load_with_ajax',
                'config' => [
                    'type' => 'check',
                    'default' => '0',
                ],
            ],
        ],
        false
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_mktools_load_with_ajax', 'list');
}

call_user_func(function () {
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['tx_mktools'] = 'layout,select_key,pages';

    // Das tt_content-Feld pi_flexform einblenden
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tx_mktools'] = 'pi_flexform';

    $flexformFile = \Sys25\RnBase\Utility\TYPO3::isTYPO121OrHigher() ? 'Main.xml' : 'MainDeprecated.xml';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('tx_mktools', 'FILE:EXT:mktools/Configuration/Flexform/'.$flexformFile);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        ['LLL:EXT:mktools/Resources/Private/Language/locallang_db.xlf:plugin.mktools.label', 'tx_mktools'],
        'list_type',
        'mktools'
    );
});

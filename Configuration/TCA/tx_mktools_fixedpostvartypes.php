<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes',
        'label' => 'title',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'iconfile' => 'EXT:mktools/ext_icon.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,title,identifier',
        'maxDBListItems' => '10',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'required,trim',
            ],
        ],
        'identifier' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:mktools/locallang_db.xml:tx_mktools_fixedpostvartypes.identifier',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'required,trim',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden, title,identifier',
                    ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
];

<?php
/**
 * Extension Manager/Repository config file for ext "mklib".
 */

//
// Extension Manager/Repository config file for ext "mktools".
//
// Manual updates:
// Only the data in the array - everything else is removed by next
// writing. "version" and "dependencies" must not be touched!
//
$EM_CONF[$_EXTKEY] = [
    'title' => 'MK Tools',
    'description' => 'Collection of some useful tools',
    'category' => 'misc',
    'author' => 'DMK E-Business GmbH',
    'author_email' => 'dev@dmk-ebusiness.de',
    'author_company' => 'DMK E-Business GmbH',
    'shy' => '',
    'dependencies' => 'rn_base, mklib',
    'version' => '9.5.9',
    'conflicts' => '',
    'priority' => '',
    'createDirs' => 'typo3temp/mktools/locks/',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'constraints' => [
        'depends' => [
            'rn_base' => '1.10.0-',
            'typo3' => '9.5.99-10.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

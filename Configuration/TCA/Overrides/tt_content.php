<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mktools" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

if (!defined('TYPO3')) {
    exit('Access denied.');
}

if (DMK\Mktools\Utility\Misc::isAjaxContentRendererActive()) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
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
        ]
    );

    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_mktools_load_with_ajax', 'list');
}

call_user_func(function (): void {
    // Das tt_content-Feld pi_flexform einblenden
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('*', 'FILE:EXT:mktools/Configuration/Flexform/Main.xml', 'tx_mktools');
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        ['LLL:EXT:mktools/Resources/Private/Language/locallang_db.xlf:plugin.mktools.label', 'tx_mktools'],
        TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        'mktools'
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'tx_mktools', 'after:subheader');
});

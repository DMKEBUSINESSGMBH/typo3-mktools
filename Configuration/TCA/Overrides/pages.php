<?php

declare(strict_types=1);

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

if (DMK\Mktools\Utility\Misc::isSeoRobotsMetaTagActive()) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'pages',
        [
            'mkrobotsmetatag' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:mktools/Resources/Private/Language/locallang_db.xlf:pages.tx_mktools_mkrobotsmetatag',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => DMK\Mktools\Utility\SeoRobotsMetaTagUtility::getOptionsForTca(),
                    'size' => 1,
                    'maxitems' => 1,
                    // @see SeoRobotsMetaTagUtility::$options
                    'default' => 0,
                ],
            ],
        ]
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'mkrobotsmetatag', '');
}

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

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/action/', 'MK Tools - Actions');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/onsiteseo/', 'MK Tools - Onsite Seo');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/tsbasic/', 'MK Tools - Basis TypoScript');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/contentrenderer', 'MK Tools - Ajax Content Renderer');

// default TS für den content replacer
if (DMK\Mktools\Utility\Misc::isContentReplacerActive()) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/contentmodal', 'MK Tools - Ajax Modal Renderer');

// Robots-Meta Tag
if (DMK\Mktools\Utility\Misc::isSeoRobotsMetaTagActive()) {
    // default TS
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mktools', 'Configuration/TypoScript/seorobotsmetatag', 'MK Tools - SEO Robots Meta Tag');
}

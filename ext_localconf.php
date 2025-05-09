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

use DMK\Mktools\Utility\Misc;

defined('TYPO3') || exit('Access denied.');

defined('ERROR_CODE_MKTOOLS') || define('ERROR_CODE_MKTOOLS', 160);

// Robots-Meta Tag
$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['mktools.seoRobotsMetaTagActive'] = false;
if (Misc::isSeoRobotsMetaTagActive()) {
    if (!Sys25\RnBase\Utility\TYPO3::isTYPO130OrHigher()) {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',mkrobotsmetatag';
    }

    // needed so we can check in the PageTSconfig if the Robots-Meta Tag is active
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['mktools.seoRobotsMetaTagActive'] = true;
}

if (Misc::getExceptionPage()) {
    // wenn wir eine Exception Page haben, wird wohl auch das Exception Handling mit mktools erledigt.
    // In diesem Fall soll das Exception Handling von Content Objects deaktiviert werden.
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('mktools', 'setup', 'config.contentObjectExceptionHandler = 0');
}

// piwa is often used for piwik custom variables
Sys25\RnBase\Utility\CHashUtility::addExcludedParametersForCacheHash([
    'piwa',
]);

define('MKTOOLS_AJAX_REQUEST_PAGE_TYPE', 9267);
// In case ajax requests are done with GET we need to exclude those parameters as they are added on the fly
// when clicking a link. Therefore they are not present when calculating the cHash but when the cHash is validated.
if (MKTOOLS_AJAX_REQUEST_PAGE_TYPE == ($_POST['type'] ?? $_GET['type'] ?? 0)) {
    Sys25\RnBase\Utility\CHashUtility::addExcludedParametersForCacheHash([
        'contentid',
        'href',
        'mktoolsAjaxRequest',
        'page',
        'requestType',
        'useHistory',
    ]);
}

if (TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('mksanitizedparameters')) {
    DMK\MkSanitizedParameters\Rules::addRulesForFrontend(['href' => FILTER_SANITIZE_URL]);
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['StaticNumberRangeMapper'] =
    DMK\Mktools\Routing\Aspect\StaticNumberRangeMapper::class;

if (Misc::areUnmappedPageTypesAllowed()) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Core\Routing\Enhancer\PageTypeDecorator::class] =
        ['className' => DMK\Mktools\Routing\Enhancer\PageTypeDecorator::class];
}

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

namespace DMK\Mktools\Utility;

use Sys25\RnBase\Configuration\Processor;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\Arrays;

/**
 * Miscellaneous common methods.
 */
final class Misc
{
    /**
     * Get fields to expand.
     */
    private static function getExtensionCfgValue(string $configValue)
    {
        return Processor::getExtensionCfgValue('mktools', $configValue);
    }

    public static function isSeoRobotsMetaTagActive()
    {
        return self::getExtensionCfgValue('seoRobotsMetaTagActive');
    }

    public static function isContentReplacerActive()
    {
        return self::getExtensionCfgValue('contentReplaceActive');
    }

    public static function isAjaxContentRendererActive()
    {
        return self::getExtensionCfgValue('ajaxContentRendererActive');
    }

    /**
     * @return string|int
     */
    public static function getExceptionPage()
    {
        return self::getExtensionCfgValue('exceptionPage');
    }

    public static function getConfigurations(string $staticPath, ?string $additionalPath = ''): Processor
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:'.$staticPath.'">'
        );
        if (null !== $additionalPath && '' !== $additionalPath && '0' !== $additionalPath) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                '<INCLUDE_TYPOSCRIPT: source="FILE:'.$additionalPath.'">'
            );
        }

        $pageTSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(0);
        $config = Arrays::mergeRecursiveWithOverrule(
            (array) $pageTSconfig['config.']['tx_mktools.'],
            (array) $pageTSconfig['plugin.']['tx_mktools.']
        );

        $configurations = new Processor();
        $configurations->init($config, $configurations->getCObj(), 'mktools', 'mktools');
        $configurations->setParameters(
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Parameters::class)
        );

        return $configurations;
    }

    public static function areUnmappedPageTypesAllowed(): bool
    {
        return (bool) self::getExtensionCfgValue('allowUnmappedPageTypes');
    }
}

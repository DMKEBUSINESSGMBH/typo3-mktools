<?php

namespace DMK\Mktools\Utility;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Miscellaneous common methods.
 */
final class Misc
{
    public function __construct()
    {
    }

    public function __clone()
    {
    }

    /**
     * Get fields to expand.
     *
     * @return int
     */
    private static function getExtensionCfgValue($configValue)
    {
        return \tx_rnbase_configurations::getExtensionCfgValue('mktools', $configValue);
    }

    /**
     * @return mixed
     */
    public static function isSeoRobotsMetaTagActive()
    {
        return self::getExtensionCfgValue('seoRobotsMetaTagActive');
    }

    /**
     * @return mixed
     */
    public static function isContentReplacerActive()
    {
        return self::getExtensionCfgValue('contentReplaceActive');
    }

    /**
     * @return mixed
     */
    public static function isAjaxContentRendererActive()
    {
        return self::getExtensionCfgValue('ajaxContentRendererActive');
    }

    /**
     * @return mixed
     */
    public static function pageNotFoundHandlingActive()
    {
        return self::getExtensionCfgValue('pageNotFoundHandling');
    }

    /**
     * @return string
     */
    public static function getExceptionPage()
    {
        return self::getExtensionCfgValue('exceptionPage');
    }

    /**
     * @return mixed
     */
    public static function shouldFalImagesBeAddedToCalEvent()
    {
        return self::getExtensionCfgValue('shouldFalImagesBeAddedToCalEvent');
    }

    /**
     * @return mixed
     */
    public static function shouldFalImagesBeAddedToTtNews()
    {
        return self::getExtensionCfgValue('shouldFalImagesBeAddedToTtNews');
    }

    /**
     * @return int
     */
    public static function getSystemLogLockThreshold()
    {
        return self::getExtensionCfgValue('systemLogLockThreshold');
    }

    /**
     * @param string $staticPath
     * @param string $additionalPath
     *
     * @return \Sys25\RnBase\Configuration\Processor
     */
    public static function getConfigurations($staticPath, $additionalPath = '')
    {
        \tx_rnbase_util_Extensions::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:'.$staticPath.'">'
        );
        if (!empty($additionalPath)) {
            \tx_rnbase_util_Extensions::addPageTSConfig(
                '<INCLUDE_TYPOSCRIPT: source="FILE:'.$additionalPath.'">'
            );
        }

        $pageTSconfig = Tx_Rnbase_Backend_Utility::getPagesTSconfig(0, 1);
        $config = \tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            (array) $pageTSconfig['config.']['tx_mktools.'],
            (array) $pageTSconfig['plugin.']['tx_mktools.']
        );

        $configurations = new \Sys25\RnBase\Configuration\Processor();
        $configurations->init($config, $configurations->getCObj(), 'mktools', 'mktools');
        $configurations->setParameters(
            \tx_rnbase::makeInstance('tx_rnbase_parameters')
        );

        return $configurations;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private static function getAbsoluteFileName($filename)
    {
        return tx_rnbase_util_Files::getFileAbsFileName($filename);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/util/class.tx_mktools_util_miscTools.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/util/class.tx_mktools_util_miscTools.php'];
}

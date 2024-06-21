<?php

namespace DMK\Mktools\Utility;

use TYPO3\CMS\Core\Cache\Backend\ApcuBackend;

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
 * DMK\Mktools\Utility\CacheUtility.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CacheUtility
{
    public static function useApcAsCacheBackend(): void
    {
        if (self::isApcUsed()) {
            $cacheBackendClass = self::getApcCacheBackendClass();

            self::setCacheBackend($cacheBackendClass, 'hash');
            self::setCacheBackend($cacheBackendClass, 'pages');
            self::setCacheBackend($cacheBackendClass, 'rootline');
            self::setCacheBackend($cacheBackendClass, 'imagesizes');
        }
    }

    /**
     * APC or APCu extension needs to be loaded and enabled. Furthermore the usage
     * on CLI is not recommended by PHP itself.
     */
    public static function isApcUsed(): bool
    {
        $apcExtensionLoaded = extension_loaded('apc');
        $apcuExtensionLoaded = extension_loaded('apcu');
        $apcAvailable = $apcExtensionLoaded || $apcuExtensionLoaded;
        $apcEnabled = (bool) ini_get('apc.enabled');

        // Use constant method so it can be mocked.
        return (('cli' !== constant('PHP_SAPI')) || 1 == ini_get('apc.enable_cli'))
            && $apcAvailable
            && $apcEnabled;
    }

    public static function getApcCacheBackendClass(): string
    {
        return ApcuBackend::class;
    }

    public static function setCacheBackend(string $backendClassName, string $cacheName): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName]['backend'] = $backendClassName;
        // compression is often set for the database cache backends but it's not supported by the
        // APC cache backend
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName]['options']['compression'])) {
            unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName]['options']['compression']);
        }
    }
}

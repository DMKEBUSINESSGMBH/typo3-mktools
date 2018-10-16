<?php
namespace DMK\Mktools\Utility;

/***************************************************************
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * DMK\Mktools\Utility$CacheUtility
 *
 * @package         TYPO3
 * @subpackage      mktools
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class CacheUtility
{
    /**
     * sets apc or apcu as caching backend for all possible caches
     *
     * @return void
     */
    public static function useApcAsCacheBackend()
    {
        if (self::isApcUsed()) {
            $cacheBackendClass = self::getApcCacheBackendClass();

            self::setCacheBackend($cacheBackendClass, 'cache_hash');
            self::setCacheBackend($cacheBackendClass, 'cache_imagesizes');
            self::setCacheBackend($cacheBackendClass, 'cache_pages');
            self::setCacheBackend($cacheBackendClass, 'cache_pagesection');
            self::setCacheBackend($cacheBackendClass, 'cache_rootline');
            self::setCacheBackend($cacheBackendClass, 'extbase_datamapfactory_datamap');
            if (PHP_SAPI !== 'cli') {
                self::setCacheBackend($cacheBackendClass, 'extbase_object');
            }
            self::setCacheBackend($cacheBackendClass, 'extbase_reflection');
        }
    }

    /**
     * @return boolean
     */
    public static function isApcUsed()
    {
        $apcExtensionLoaded = extension_loaded('apc');
        $apcuExtensionLoaded = extension_loaded('apcu');
        $apcAvailable = $apcExtensionLoaded || $apcuExtensionLoaded;
        $apcEnabled = ini_get('apc.enabled') == TRUE;

        return $apcAvailable && $apcEnabled;
    }

    /**
     * @return boolean
     */
    public static function getApcCacheBackendClass()
    {
        return extension_loaded('apc')
            ? 'TYPO3\\CMS\\Core\\Cache\\Backend\\ApcBackend'
            : 'TYPO3\\CMS\\Core\\Cache\\Backend\\ApcuBackend';
    }

    /**
     * @param string $backendClassName
     * @param string $cacheName
     * @return void
     */
    public static function setCacheBackend($backendClassName, $cacheName)
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName]['backend'] = $backendClassName;
    }
}

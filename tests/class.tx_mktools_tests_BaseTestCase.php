<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 *  All rights reserved
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
 *  ***********************************************************************  */

/**
 * @author Michael Wagner
 */
class tx_mktools_tests_BaseTestCase extends tx_rnbase_tests_BaseTestCase
{
    private static $aExtConf = [];

    /**
     * Sichert eine Extension Konfiguration.
     * Wurde bereits eine Extension Konfiguration gesichert,
     * wird diese nur überschrieben wenn bOverwrite wahr ist!
     *
     * @param string $sExtKey
     * @param bool   $bOverwrite
     */
    public static function storeExtConf($sExtKey = 'mklib', $bOverwrite = false)
    {
        if (!isset(self::$aExtConf[$sExtKey]) || $bOverwrite) {
            self::$aExtConf[$sExtKey] = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey];
        }
    }

    /**
     * Setzt eine gesicherte Extension Konfiguration zurück.
     *
     * @param string $sExtKey
     *
     * @return bool wurde die Konfiguration zurückgesetzt?
     */
    public static function restoreExtConf($sExtKey = 'mklib')
    {
        if (isset(self::$aExtConf[$sExtKey])) {
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = self::$aExtConf[$sExtKey];

            return true;
        }

        return false;
    }

    /**
     * Setzt eine Vaiable in die Extension Konfiguration.
     * Achtung im setUp sollte storeExtConf und im tearDown restoreExtConf aufgerufen werden.
     *
     * @param string $sCfgKey
     * @param string $sCfgValue
     * @param string $sExtKey
     */
    public static function setExtConfVar($sCfgKey, $sCfgValue, $sExtKey = 'mklib')
    {
        // aktuelle Konfiguration auslesen
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey]);
        // wenn keine Konfiguration existiert, legen wir eine an.
        if (!is_array($extConfig)) {
            $extConfig = [];
        }
        // neuen Wert setzen
        $extConfig[$sCfgKey] = $sCfgValue;
        // neue Konfiguration zurückschreiben
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = serialize($extConfig);
    }

    /**
     * Liefert eine DateiNamen.
     *
     * @param $filename
     * @param $dir
     * @param $extKey
     *
     * @return string
     */
    public static function getFixturePath($filename, $dir = 'tests/fixtures/', $extKey = 'mklib')
    {
        return \tx_rnbase_util_Extensions::extPath($extKey).$dir.$filename;
    }

    /**
     * Disabled das Logging über die Devlog Extension für die
     * gegebene Extension.
     *
     * @param string $extKey
     * @param bool   $bDisable
     */
    public static function disableDevlog($extKey = 'devlog', $bDisable = true)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['nolog'] = $bDisable;
    }
}

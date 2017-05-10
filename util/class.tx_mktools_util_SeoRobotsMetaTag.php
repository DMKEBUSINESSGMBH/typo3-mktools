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
 *
 * @package TYPO3
 * @author Christian Riesche <christian.riesche@dmk-ebusiness.de>
 */
class tx_mktools_util_SeoRobotsMetaTag
{

    /**
     * Werte für das robots Meta Tag
     * @var array
     */
    public static $options = array(
        -1 => 'use default value from TypoScript (page.meta.robots.cObject.default)',
        0 => 'use default value from TypoScript (check rootline for explicit value first)',
        1 => 'INDEX,FOLLOW',
        2 => 'INDEX,NOFOLLOW',
        3 => 'NOINDEX,FOLLOW',
        4 => 'NOINDEX,NOFOLLOW',
        5 => 'NOODP,NOINDEX,FOLLOW',
    );


    /**
     * Formattierte Ausgabe der Werte für das TCA
     * @return array
     */
    public static function getOptionsForTCA()
    {
        $tcaOptions = array();
        foreach (self::$options as $key => $option) {
            $tcaOptions[] = array($option, $key);
        }

        return $tcaOptions;
    }

    /**
     * Gibt passenden Wert des Robots Tag zurück
     * @param int $key
     * @return string
     */
    private function getOptionByValue($key)
    {
        if (array_key_exists($key, self::$options)) {
            return self::$options[$key];
        }

        return '';
    }

    /**
     * Liefert den Wert des für diese Seite relevanten Robots Meta Tag
     * zurück. Wird keiner gefunden, dann wird als Default der Wert der
     * Konstanten {$config.tx_mktools.seorobotsmetatag.default} zurückgegeben
     *
     * @param string $content
     * @param array $configuration
     * @return string
     */
    public function getSeoRobotsMetaTagValue($content = '', array $configuration = array())
    {
        $robotsValue = $this->getRobotsValue();
        if ($robotsValue > 0) {
            return $this->getOptionByValue($robotsValue);
        }

        return $configuration['default'];
    }


    /**
     * Sucht rekursiv von der aktuellen Seite aus, ob ein
     * Wert für ein individuelles Robots-Tag gesetzt ist. Wir stoppen sobald eine
     * Seite nicht auf 0 steht.
     * @return int
     */
    protected function getRobotsValue()
    {
        foreach ($this->getRootline() as $page) {
            if (!empty($page['mkrobotsmetatag'])) {
                return $page['mkrobotsmetatag'];
            }
        }

        return 0;
    }

    /**
     * @return array
     */
    protected function getRootline()
    {
        return tx_rnbase_util_TYPO3::getSysPage()->getRootLine($GLOBALS['TSFE']->id);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
        ['ext/mktools/util/class.tx_mktools_util_SeoRobotsMetaTag.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
        ['ext/mktools/util/tx_mktools_util_SeoRobotsMetaTag.php']);
}

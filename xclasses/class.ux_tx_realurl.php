<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

require_once tx_rnbase_util_Extensions::extPath('realurl', 'class.tx_realurl.php');

/**
 * XCLASS to extend realurl.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *
 * @TODO refactoring seit realurl 2.x funktioniert diese xclass nicht mehr
 */
class ux_tx_realurl extends tx_realurl
{
    const MODE_NONE = '';
    const MODE_ENCODE = 'encode';
    const MODE_DECODE = 'decode';

    /**
     * the current mode of realurl.
     *
     * @var string
     */
    protected $currentMode = '';

    /**
     * sets the current mode.
     *
     * @param string $mode
     */
    public function setMode($mode = self::MODE_NONE)
    {
        $this->currentMode = $mode;
    }

    /**
     * Translates a URL with query string (GET parameters) into Speaking URL.
     * Called from t3lib_tstemplate::linkData.
     *
     * @param array &$params
     */
    public function encodeSpURL(&$params)
    {
        $this->setMode(self::MODE_ENCODE);
        parent::encodeSpURL($params);
        $this->setMode();
    }

    /**
     * Parse speaking URL and translate it to parameters understood by TYPO3.
     *
     * @param array $params
     */
    public function decodeSpURL($params)
    {
        $this->setMode(self::MODE_DECODE);
        parent::decodeSpURL($params);
        $this->setMode();
    }

    /**
     * Returns configuration for a postVarSet (default) based on input page id.
     *
     * @param int    $pageId
     * @param string $mainCat Default is "postVarSets" but could be "fixedPostVars"
     *
     * @return array
     */
    public function getPostVarSetConfig($pageId, $mainCat = 'postVarSets')
    {
        // call real url (parent)
        $cfg = parent::getPostVarSetConfig($pageId, $mainCat);
        // remove sets for other localisations
        return $this->getLocalizedPostVarSet($cfg);
    }

    /**
     * reduces the post var set config with categories
     * other than the current language.
     *
     * @param array $postVarSet
     *
     * @return array
     */
    protected function getLocalizedPostVarSet($postVarSet = [])
    {
        // check only, if we have an array and we are in the encode mode.
        // in the decode mode we dont have a language. realurl will find the right mapping it self
        if (self::MODE_ENCODE !== $this->currentMode
            || !is_array($postVarSet)
            || empty($postVarSet)
        ) {
            return $postVarSet;
        }
        // get language from input query parameters
        $language = empty($this->orig_paramKeyValues['L']) ? 0 : $this->orig_paramKeyValues['L'];

        // remove all sets for other languages
        foreach ($postVarSet as $paramKey => $paramCfg) {
            foreach ($paramCfg as $paramSubKey => $paramValue) {
                // no language set, skip!
                if (!isset($paramValue['language'])) {
                    continue;
                }
                // the language ids, to match with the current
                $allowedLanguages = is_array($paramValue['language']) ? $paramValue['language']['ids'] : $paramValue['language'];
                // is current language in the set config? remove if not!
                if (!tx_rnbase_util_Strings::inList($allowedLanguages, $language)) {
                    unset($postVarSet[$paramKey][$paramSubKey]);
                }
                // remove the language config key
                // unset($postVarSet[$paramKey]['language']);
            }
            // remove complete set, if there ar no more get vars
            if (empty($postVarSet[$paramKey])) {
                unset($postVarSet[$paramKey]);
            }
        }

        return $postVarSet;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/xclasses/class.ux_tx_realurl.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/xclasses/class.ux_tx_realurl.php'];
}

<?php

namespace DMK\Mktools\Utility\Menu\Processor;

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

use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\FrontendControllerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TranslatedRecords. Inspired by https://gist.github.com/birger-fuehne/a8a97c94ec9346d691174462ccbfcfcc.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TranslatedRecords
{
    /**
     * Check if we are on the detail view of a record and if the record is translated.
     * If the record is not translated, the ITEM_STATE of the language menu item is set to USERDEF1 or USERDEF2
     * as it is also done for pages (see AbstractMenuContentObject::prepareMenuItemsForLanguageMenu() "Checking if the
     * "disabled" state should be set.").
     *
     * TypoScript configuration example for EXT:news
     *
     * lib.languageMenu = HMENU
     * lib.languageMenu {
     *   special = language
     *   [...]
     *   1 = TMENU
     *   1 {
     *     [...]
     *     itemArrayProcFunc = DMK\Mktools\Utility\Menu\Processor\TranslatedRecords->process
     *     itemArrayProcFunc {
     *         # This is mandatory as we need the complete menu configuration inside the
     *         # the processor.
     *         menuConfiguration < lib.languageMenu
     *         parametersConfiguration {
     *             # GET.PARAMETER.WITH.RECORD.UID = TABLENAME
     *             tx_news_pi1.news = tx_news_domain_model_news
     *         }
     *     }
     * }
     *
     * @see \TYPO3\CMS\Frontend\ContentObject\Menu\AbstractMenuContentObject::prepareMenuItemsForLanguageMenu()
     *
     * @return array $menuItems
     */
    public function process(array $menuItems, array $typoScriptConfiguration): array
    {
        $recordInformationToCheckForTranslation = $this->getRecordInformationToCheckForTranslation(
            $typoScriptConfiguration['parametersConfiguration.'],
            Parameters::getGetParameters()
        );
        if ($recordInformationToCheckForTranslation) {
            foreach ($menuItems as &$menuItem) {
                $sysLanguageUid = intval($menuItem['_PAGES_OVERLAY_LANGUAGE']);
                $translatedRecord = $this->getTranslatedRecord(
                    $sysLanguageUid,
                    $recordInformationToCheckForTranslation['table'],
                    $recordInformationToCheckForTranslation['uid']
                );
                $menuItem = $this->handleDisablingOfMenuItemForNotTranslatedRecord(
                    $menuItem,
                    $translatedRecord,
                    $typoScriptConfiguration
                );
            }
        }

        return $menuItems;
    }

    /**
     * Check if the record is translated in a given sys_language_uid. If so, the text will be processed,
     * else the return value will be false. If the configured parameters are not submitted the value
     * will be returned unchanged.
     *
     * TypoScript configuration example for EXT:cal
     *
     * lib.navi.language = COA
     * lib.navi.language {
     *     10 = TEXT
     *     10.value = Text if SysLang is 1
     *     stdWrap.preUserFunc = DMK\Mktools\Utility\Menu\Processor\TranslatedRecords->processEmptyIfRecordNotExists
     *     stdWrap.preUserFunc {
     *         sysLanguageUid = 1
     *         parametersConfiguration {
     *             # GET.PARAMETER.WITH.RECORD.UID = TABLENAME
     *             tx_cal_controller.uid = tx_cal_event
     *         }
     *     }
     * }
     *
     * @param string $value
     *
     * @return bool
     */
    public function processEmptyIfRecordNotExists($value, array $typoScriptConfiguration)
    {
        $recordInformationToCheckForTranslation = $this->getRecordInformationToCheckForTranslation(
            $typoScriptConfiguration['parametersConfiguration.'],
            Parameters::getGetParameters()
        );

        $sysLanguageUid = (int) $typoScriptConfiguration['sysLanguageUid'];

        if ($recordInformationToCheckForTranslation) {
            $translatedRecord = $this->getTranslatedRecord(
                $sysLanguageUid,
                $recordInformationToCheckForTranslation['table'],
                $recordInformationToCheckForTranslation['uid']
            );

            return empty($translatedRecord) ? false : $value;
        }

        return $value;
    }

    protected function getTranslatedRecord(int $sysLanguageUid, string $table, int $uid): array
    {
        $databaseConnection = $this->getDatabaseConnection();
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();

        if ($sysLanguageUid) {
            $currentRecord = $databaseConnection->doSelect(
                '*',
                $table,
                [
                    'where' => 'uid = '.$uid,
                ]
            )[0];
            $translatedRecord = (array) \tx_rnbase_util_TYPO3::getSysPage()->getRecordOverlay(
                $table,
                $currentRecord,
                $sysLanguageUid,
                ('strict' === FrontendControllerUtility::getLanguageMode($typoScriptFrontendController)) ? 'hideNonTranslated' : ''
            );
        } else {
            $translatedRecord = [];
        }

        return $translatedRecord;
    }

    protected function handleDisablingOfMenuItemForNotTranslatedRecord(
        array $menuItem,
        array $translatedRecord,
        array $typoScriptConfiguration
    ): array {
        $menuItemLanguageUid = intval($menuItem['_PAGES_OVERLAY_LANGUAGE']);
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();

        if (GeneralUtility::hideIfNotTranslated($typoScriptFrontendController->page['l18n_cfg'])
            && $menuItemLanguageUid
            && empty($translatedRecord)
            || GeneralUtility::hideIfDefaultLanguage($typoScriptFrontendController->page['l18n_cfg'])
            && (
                !$menuItemLanguageUid
                || empty($translatedRecord)
            ) ||
            !$typoScriptConfiguration['menuConfiguration.']['special.']['normalWhenNoLanguage']
            && $menuItemLanguageUid
            && empty($translatedRecord)
        ) {
            $menuItem['ITEM_STATE'] = FrontendControllerUtility::getLanguageId($typoScriptFrontendController) == $menuItemLanguageUid ?
                'USERDEF2' : 'USERDEF1';
        }

        return $menuItem;
    }

    /**
     * Walk through the parameters and TypoScript-Configuration to get the record/table which should be checked
     * for a translation. Return an array containing the record uid and the table name.
     */
    protected function getRecordInformationToCheckForTranslation(array $configuration, array $parameters): array
    {
        $result = [];
        foreach ($configuration as $parameterKey => $parameter) {
            if (is_array($parameter)) {
                $parameterKey = rtrim($parameterKey, '.');
            }
            if (isset($parameters[$parameterKey])) {
                if (is_array($parameters[$parameterKey]) && is_array($parameter)) {
                    $result = $this->getRecordInformationToCheckForTranslation($parameter, $parameters[$parameterKey]);
                    break;
                } elseif (is_string($parameter) && 0 !== $parameter && isset($parameters[$parameterKey])) {
                    $result = [
                        'table' => $parameter,
                        'uid' => intval($parameters[$parameterKey]),
                    ];
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return \Tx_Rnbase_Database_Connection
     */
    protected function getDatabaseConnection()
    {
        return \Tx_Rnbase_Database_Connection::getInstance();
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return \tx_rnbase_util_TYPO3::getTSFE();
    }
}

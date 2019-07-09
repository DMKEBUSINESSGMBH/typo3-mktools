<?php

namespace DMK\Mktools\Utility\Menu\Processor;

use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\FrontendControllerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @param array $menuItems
     * @param array $typoScriptConfiguration
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
                $translatedRecord = $this->getTranslatedRecord(
                    $menuItem,
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
     * @param array  $menuItem
     * @param string $table
     * @param int    $uid
     *
     * @return array
     */
    protected function getTranslatedRecord(array $menuItem, string $table, int $uid): array
    {
        $databaseConnection = $this->getDatabaseConnection();
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        $menuItemLanguageUid = intval($menuItem['_PAGES_OVERLAY_LANGUAGE']);
        if ($menuItemLanguageUid) {
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
                $menuItemLanguageUid,
                ('strict' === FrontendControllerUtility::getLanguageMode($typoScriptFrontendController)) ? 'hideNonTranslated' : ''
            );
        } else {
            $translatedRecord = [];
        }

        return $translatedRecord;
    }

    /**
     * @param array $menuItem
     * @param array $translatedRecord
     * @param array $typoScriptConfiguration
     *
     * @return array
     */
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
     *
     * @param array $configuration
     * @param array $parameters
     *
     * @return array
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

<?php
/**
 * @package TYPO3
 * @subpackage tx_mktools
 *
 * Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 */

/**
 * Includes
 */

tx_rnbase::load('tx_mktools_util_T3Loader');

/**
 * Action zum Rendern von ContentElementen
 *
 * @package TYPO3
 * @subpackage tx_mktools
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_action_ajax_ContentRenderer
{

    /**
     * Entry point.
     *
     * @param array Current data record, either a tt_content element or page record.
     * @param string Table name, either "pages" or "tt_content".
     * @return void
     */
    public function renderContent()
    {
        // content id auslesen
        $contentId = (int) tx_rnbase_parameters::getPostOrGetParameter('contentid');
        if (empty($contentId)) {
            $this->sendError(500, 'Missing required parameters.');
        }

        $ttContent = tx_rnbase_util_TYPO3::getSysPage()->checkRecord(
            'tt_content',
            $contentId
        );
        $cObj = tx_mktools_util_T3Loader::getContentObject($contentId);

        // jetzt das contentelement parsen
        $cObj->start($ttContent, 'tt_content');
        $content = $cObj->cObjGetSingle('<tt_content', array());
        $content = trim($content);

        if (empty($content)) {
            // Exception
            $this->sendError(500, 'Could not fetch content.');
        }

        return $content;
    }
}

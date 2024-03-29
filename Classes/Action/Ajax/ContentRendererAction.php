<?php

namespace DMK\Mktools\Action\Ajax;

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

use DMK\Mktools\Utility\T3Loader;
use Sys25\RnBase\Frontend\Request\Parameters;
use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Action zum Rendern von ContentElementen.
 *
 * @author Michael Wagner
 */
class ContentRendererAction
{
    /**
     * Entry point.
     */
    public function renderContent()
    {
        // content id auslesen
        $contentId = (int) Parameters::getPostOrGetParameter('contentid');

        if (0 === $contentId) {
            $this->sendNotFoundHeader();
        }

        $ttContent = TYPO3::getSysPage()->checkRecord('tt_content', $contentId);
        $cObj = T3Loader::getContentObject($contentId);

        // jetzt das contentelement parsen
        $cObj->start($ttContent, 'tt_content');
        $content = $cObj->cObjGetSingle('<tt_content', []);
        $content = trim($content);

        if (empty($content)) {
            // Exception
            $this->sendNotFoundHeader();
        }

        return $content;
    }

    protected function sendNotFoundHeader(): void
    {
        header(HttpUtility::HTTP_STATUS_404);
        exit;
    }
}

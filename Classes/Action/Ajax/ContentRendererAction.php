<?php

namespace DMK\Mktools\Action\Ajax;

use Sys25\RnBase\Frontend\Request\Parameters;
use tx_mktools_util_T3Loader as T3Loader;
use tx_rnbase_util_TYPO3 as TYPO3Util;

/**
 * Action zum Rendern von ContentElementen.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class ContentRendererAction
{
    /**
     * Entry point.
     *
     * @param array current data record, either a tt_content element or page record
     * @param string table name, either "pages" or "tt_content"
     */
    public function renderContent()
    {
        // content id auslesen
        $contentId = (int) Parameters::getPostOrGetParameter('contentid');

        if (0 === $contentId) {
            $this->sendError(500, 'Missing required parameters.');
        }

        $ttContent = TYPO3Util::getSysPage()->checkRecord('tt_content', $contentId);
        $cObj = T3Loader::getContentObject($contentId);

        // jetzt das contentelement parsen
        $cObj->start($ttContent, 'tt_content');
        $content = $cObj->cObjGetSingle('<tt_content', []);
        $content = trim($content);

        if (empty($content)) {
            // Exception
            $this->sendError(500, 'Could not fetch content.');
        }

        return $content;
    }
}

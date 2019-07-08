<?php

/**
 * Includes.
 */
use Sys25\RnBase\Frontend\Request\Parameters;

/**
 * Action zum Rendern von ContentElementen.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_action_ajax_ContentRenderer
{
    /**
     * Entry point.
     */
    public function renderContent()
    {
        // content id auslesen
        $contentId = Parameters::getPostOrGetParameter('contentid');
        if (empty($contentId)) {
            $this->sendError(500, 'Missing required parameters.');
        }

        $ttContent = (array) tx_rnbase_util_TYPO3::getSysPage()->checkRecord(
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

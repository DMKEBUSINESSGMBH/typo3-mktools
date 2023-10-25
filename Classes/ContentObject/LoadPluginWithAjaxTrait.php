<?php

namespace DMK\Mktools\ContentObject;

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

/**
 * DMK\Mktools\ContentObject$UserContentObjectTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
trait LoadPluginWithAjaxTrait
{
    /**
     * {@inheritdoc}
     *
     * @see \TYPO3\CMS\Frontend\ContentObject\UserInternalContentObject::render()
     */
    public function render($conf = [])
    {
        if (($this->getContentObjectRenderer()->data['tx_mktools_load_with_ajax'] ?? false)
           && !($_POST['mktoolsAjaxRequest'] ?? $_GET['mktoolsAjaxRequest'] ?? false)
        ) {
            // we need a link per element so caching (chash) works correct in the ajax
            // page type. Otherwise it's not possible to render more than one element
            // per page
            $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Configuration\Processor::class);
            $typoScriptSetup = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray();
            $configuration->init($typoScriptSetup, $this->getContentObjectRenderer(), 'mktools', 'mktools');
            $link = $configuration
                ->createLink()
                ->initByTS(
                    $configuration,
                    $this->urlTypoScriptConfigurationPath,
                    [
                        // This parameter is just used to trigger the cHash generation so every element has it's
                        // own individual link.
                        '::ajaxcontentid' => $this->getContentObjectRenderer()->data['uid'],
                    ]
                )
                ->makeUrl();

            // We only need dummy content which indicates to start the ajax load.
            // The rest is handled with JS and the surrounding div with the content id.
            // @see AjaxContent.js
            $content = sprintf(
                '<a class="ajax-links-autoload ajax-no-history" tabindex="-1" aria-hidden="true" data-ajaxreplaceid="c%s" href="%s"></a>',
                $this->getContentObjectRenderer()->data['uid'],
                $link
            );
        } else {
            $content = parent::render($conf);
        }

        return $content;
    }
}

<?php
namespace DMK\Mktools\ContentObject;

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
 * If element should be loaded with Ajax, we insert
 * only a placeholder which get's replaced with Ajax by the mktools
 * Ajax Renderer.
 *
 * @package         TYPO3
 * @subpackage      mktools
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class UserInternalContentObject extends \TYPO3\CMS\Frontend\ContentObject\UserInternalContentObject
{
    /**
     * {@inheritDoc}
     * @see \TYPO3\CMS\Frontend\ContentObject\UserInternalContentObject::render()
     */
    public function render($conf = [])
    {
        if ($this->cObj->data['tx_mktools_load_with_ajax'] &&
            !\tx_rnbase_parameters::getPostOrGetParameter('mktoolsAjaxRequest')
        ) {
            // we need a link per element so caching (chash) works correct in the ajax
            // page type. Otherwise it's not possible to render more than one element
            // per page
            $link = \tx_rnbase::makeInstance(\tx_rnbase_util_Link::class)
                ->destination($GLOBALS['TSFE']->id)
                ->parameters(['contentId' => $this->getContentObjectRenderer()->data['uid']])
                ->makeUrl();

            // We only need dummy content which indicates to start the ajax load.
            // The rest is handled with JS and the surrounding div with the content id.
            // @see AjaxContent.js
            $content = '<a class="ajax-links-autoload" href="' . $link . '"></a>';
        } else {
            $content = parent::render($conf);
        }

        return $content;
    }
}

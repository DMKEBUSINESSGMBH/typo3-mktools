<?php
/**
 *  Copyright notice.
 *
 *  (c) DMK E-BUSINESS GmbH
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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Class tx_mktools_hook_ContentReplace.
 *
 * @author  Michael Wagner <michael.wagner@dmk-ebusiness.com>
 * @author  Hannes Bochmann <hannes.bochmann@dmk-ebusiness.com>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_hook_ContentReplace
{
    /**
     * @param array $params
     * @param TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController or tslib_cObj $tsfe
     */
    public function contentPostProcOutput($params, &$tsfe)
    {
        // Only do the replacements again if the page contains COA_INT or USER_INT objects.
        // We need to do the replacements again in case uncacheable objects have added assets.
        if ($tsfe->isINTincScript()) {
            $this->doReplace($tsfe);
        }
    }

    /**
     * @param array $params
     * @param TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController or tslib_cObj $tsfe
     */
    public function contentPostProcAll($params, &$tsfe)
    {
        // We do this always as the done replacements get cached. So when uncachable objects
        // are present at least some replacements are already done and only the assets added
        // by uncacheable objects need to be replaced in contentPostProcOutput().
        $this->doReplace($tsfe);
    }

    /**
     * Search links to resource and replace them with e.g. a CDN-Link.
     *
     * You must set the Search and Replace patterns via TypoScript.
     * usage from TypoScript:
     *   config.tx_mktools.contentreplace {
     *       search {
     *           1="typo3temp/pics/
     *           2="fileadmin/
     *       }
     *       replace {
     *           1="http://mycdn.com/i/
     *           2="http://mycdn.com/f/
     *       }
     *   }
     *
     * Don't forget to clear the cache afterwards!
     *
     * @TODO: use preg_replace instead of str_replace
     * @TODO: write tests
     *
     * @param TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function doReplace(&$tsfe)
    {
        // Fetch configuration
        $config = &$tsfe->config['config']['tx_mktools.']['contentreplace.'];

        // Quit immediately if no replace array setup
        if (!$config
            || !isset($config['enable']) || !intval($config['enable'])
            || !isset($config['search.']) || empty($config['search.'])
            || !isset($config['replace.']) || empty($config['replace.'])
        ) {
            return;
        }

        // Replace page content
        $tsfe->content = str_replace($config['search.'], $config['replace.'], $tsfe->content);
    }
}

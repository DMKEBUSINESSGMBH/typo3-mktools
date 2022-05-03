<?php

declare(strict_types=1);

namespace DMK\Mktools\Utility;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
 * Class ContentReplacer.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ContentReplacerUtility
{
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
     */
    public static function doReplace(string $content, TypoScriptFrontendController $typoScriptFrontendController): string
    {
        $config = $typoScriptFrontendController->config['config']['tx_mktools.']['contentreplace.'] ?? [];

        if (($config['enable'] ?? false)
            && ($config['search.'] ?? false)
            && ($config['replace.'] ?? false)
        ) {
            $content = str_replace($config['search.'], $config['replace.'], $content);
        }

        return $content;
    }
}

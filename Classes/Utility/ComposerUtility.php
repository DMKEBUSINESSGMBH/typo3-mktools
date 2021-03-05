<?php

namespace DMK\Mktools\Utility;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * @author Michael Wagner
 */
final class ComposerUtility
{
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * preloads the composer.
     *
     * @param string $extKey
     * @param string $composerDir
     *
     * @return void
     */
    public static function autoload($extKey = 'mktools', $composerDir = 'Resources/Private/PHP/Composer/')
    {
        require_once ExtensionManagementUtility::extPath($extKey, trim($composerDir, '/').'/autoload.php');
    }
}

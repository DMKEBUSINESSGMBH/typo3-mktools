<?php

$classes = require __DIR__.'/../Migrations/Code/ClassAliasMap.php';
$file = __DIR__.'/../Migrations/Code/LegacyClassesForIde.php';
$template = <<<TEMPLATE
/**	
 * @deprecated since 9.6, removed since 11.0	
 */
class %s extends %s {}


TEMPLATE;

$header = <<<'HEADER'
<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2020 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * This class is only for your IDE.
 */


HEADER;

$content = '';

foreach ($classes as $legacy => $new) {
    $content .= sprintf($template, $legacy, $new);
}

file_put_contents($file, $header.$content);

<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2015 DMk E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *  ***********************************************************************  */

/**
 * @author  Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mktools_tests_hook_BrowseLinks_testcase extends Tx_Phpunit_TestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (isset($_GET['P']['params'])) {
            unset($_GET['P']['params']);
        }
    }

    /**
     * @group unit
     */
    public function testRenderReturnsNull()
    {
        $this->assertNull($this->callHookFunction('render'), 'render liefert nicht NULL');
    }

    /**
     * @group unit
     */
    public function testIsValidReturnsFalse()
    {
        $this->assertFalse($this->callHookFunction('isValid'));
    }

    /**
     * @group unit
     */
    public function testIsValidChangesParamsParameterNotIfIsAlreadyArray()
    {
        $_GET['P']['params'] = array('test');
        $this->callHookFunction('isValid');

        $this->assertEquals(array('test'), $_GET['P']['params'], 'params Parameter doch verändert');
    }

    /**
     * @group unit
     */
    public function testIsValidChangesParamsParameterToEmptyArrayIfIsNotAlreadyArray()
    {
        $_GET['P']['params'] = 'test';
        $this->callHookFunction('isValid');

        $this->assertEquals(array(), $_GET['P']['params'], 'params Parameter kein leeres array');
    }

    /**
     * @param string $function
     *
     * @return mixed
     */
    private function callHookFunction($function)
    {
        $hook = tx_rnbase::makeInstance('tx_mktools_hook_BrowseLinks');
        $browseLinksObject = $this->getMock('SC_browse_links');

        return $hook->$function('', $browseLinksObject);
    }
}

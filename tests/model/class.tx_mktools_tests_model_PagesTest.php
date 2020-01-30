<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * @author Hannes Bochmann
 */
class tx_mktools_tests_model_PagesTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetFixedPostVarTypeReturnsNullIfNoTypeSet()
    {
        $record = array('tx_mktools_fixedpostvartype' => 0);
        $page = \tx_rnbase::makeInstance(\DMK\Mktools\Model\Page::class, $record);

        $this->assertNull($page->getFixedPostVarType());
    }

    /**
     * @group unit
     */
    public function testGetFixedPostVarTypeReturnsCorrectModelIfTypeSet()
    {
        $record = array('tx_mktools_fixedpostvartype' => array('uid' => 123));
        $page = \tx_rnbase::makeInstance(\DMK\Mktools\Model\Page::class, $record);
        $fixedPostVarType = $page->getFixedPostVarType();
        $this->assertInstanceOf(
            \DMK\Mktools\Model\FixedPostVarType::class,
            $fixedPostVarType,
            'falsches model'
        );
        $this->assertEquals(123, $fixedPostVarType->getUid(), 'falsche model uid');
    }
}

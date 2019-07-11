<?php

namespace DMK\Mktools\ContentObject;

/**
 *  Copyright notice.
 *
 *  (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 */

/**
 * DMK\Mktools\ContentObject$UserContentObjectTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class UserContentObjectTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * @var \DMK\Mktools\ContentObject\UserContentObject
     */
    protected $userObject;

    /**
     * @param bool $loadWithAjax
     * @param int  $mktoolsAjaxRequest
     *
     * @group integration
     * @dataProvider dataProviderRenderTest
     */
    public function testRenderIfContentShouldNotBeLoadedWithAjax($loadWithAjax, $mktoolsAjaxRequest)
    {
        $this->markTestIncomplete(
            'This test has to be refactored.'
        );

        $contentObject = $this->createConfigurations([], 'mktools')->getContentObject();
        $contentObject->data['tx_mktools_load_with_ajax'] = $loadWithAjax;
        \tx_rnbase_parameters::setGetParameter($mktoolsAjaxRequest, 'mktoolsAjaxRequest');

        $this->initializeFixtures($contentObject);

        // check if original method is called
        $configuration = ['stdWrap.' => ['cObject' => 'TEXT', 'cObject.' => ['value' => 'test']]];

        self::assertEquals('test', $this->userObject->render($configuration));
    }

    /**
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\DMK\Mktools\ContentObject\UserContentObject
     */
    protected function initializeFixtures($contentObject)
    {
        \DMK\Mklib\Utility\Tests::prepareTSFE(['force' => true]);

        $this->userObject = $this->getMock(
            UserContentObject::class,
            ['callUserFunction'],
            [$contentObject]
            );
        $this->userObject
        ->expects(self::never())
        ->method('callUserFunction')
        ->will(self::returnValue('content rendered'));
    }

    /**
     * @return boolean[][]|number[][]|string[][]
     */
    public function dataProviderRenderTest()
    {
        return [
            [true, 1],
            [false, 1],
            [false, 0],
        ];
    }

    /**
     * @group unit
     */
    public function testRenderIfContentShouldBeLoadedWithAjaxAndUseKeepVarsForLink()
    {
        $this->markTestIncomplete(
            'This test has to be refactored.'
        );

        $contentObject = $this->createConfigurations([], 'mktools')->getContentObject();
        $contentObject->data['tx_mktools_load_with_ajax'] = true;
        $contentObject->data['uid'] = 123;
        \tx_rnbase_parameters::setGetParameter(0, 'mktoolsAjaxRequest');
        \tx_rnbase_parameters::setGetParameter('testValue', 'mktools|test');

        $this->initializeFixtures($contentObject);
        $GLOBALS['TSFE']->tmpl->setup['lib.']['tx_mktools.']['loadUserWithAjaxUrl.'] = [
            'useKeepVars' => true,
            'useKeepVars.' => [
                'add' => 'mktools::test',
            ],
        ];

        self::assertRegExp(
            '/\<a class="ajax-links-autoload ajax-no-history" href="\?id=.*\&amp\;mktools%5Btest%5D=testValue&amp\;contentid=123&amp\;cHash=[a-z0-9]{32}"\>\<\/a\>/',
            $this->userObject->render()
            );
    }

    /**
     * @group unit
     */
    public function testRenderIfContentShouldBeLoadedWithAjax()
    {
        $this->markTestIncomplete(
            'This test has to be refactored.'
        );

        $contentObject = $this->createConfigurations([], 'mktools')->getContentObject();
        $contentObject->data['tx_mktools_load_with_ajax'] = true;
        $contentObject->data['uid'] = 123;
        \tx_rnbase_parameters::setGetParameter(0, 'mktoolsAjaxRequest');
        \tx_rnbase_parameters::setGetParameter('testValue', 'mktools|test');

        $this->initializeFixtures($contentObject);

        self::assertRegExp(
            '/\<a class="ajax-links-autoload ajax-no-history" href="\?id=.*\&amp\;contentid=123&amp\;cHash=[a-z0-9]{32}"\>\<\/a\>/',
            $this->userObject->render()
            );
    }
}

<?php

namespace DMK\Mktools\ContentObject;

/**
 *  Copyright notice.
 *
 *  (c) Hannes Bochmann <dev@dmk-ebusiness.de>
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
 * DMK\Mktools\ContentObject$UserInternalContentObjectTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class UserInternalContentObjectTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var \DMK\Mktools\ContentObject\UserInternalContentObject
     */
    protected $userInternalObject;

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

        self::assertEquals('<!--INT_SCRIPT.123-->', $this->userInternalObject->render(['myConfiguration']));
        self::assertEquals(
            ['myConfiguration'],
            $this->typoScriptFrontendController->config['INTincScript']['INT_SCRIPT.123']['conf']
        );
    }

    /**
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\DMK\Mktools\ContentObject\UserInternalContentObject
     */
    protected function initializeFixtures($contentObject)
    {
        \DMK\Mklib\Utility\Tests::prepareTSFE(['force' => true]);

        $this->typoScriptFrontendController = $this->getMock(
            get_class($GLOBALS['TSFE']),
            ['uniqueHash'],
            [],
            '',
            false
        );
        $this->typoScriptFrontendController
            ->expects(self::any())
            ->method('uniqueHash')
            ->will(self::returnValue(123));

        $this->userInternalObject = $this->getMock(
            UserInternalContentObject::class,
            ['getTypoScriptFrontendController'],
            [$contentObject]
        );
        $this->userInternalObject
            ->expects(self::any())
            ->method('getTypoScriptFrontendController')
            ->will(self::returnValue($this->typoScriptFrontendController));
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
        $GLOBALS['TSFE']->tmpl->setup['lib.']['tx_mktools.']['loadUserIntWithAjaxUrl.'] = [
            'useKeepVars' => true,
            'useKeepVars.' => [
                'add' => 'mktools::test',
            ],
        ];

        self::assertRegExp(
            '/\<a class="ajax-links-autoload ajax-no-history" href="\?id=.*\&amp\;mktools%5Btest%5D=testValue&amp\;contentid=123&amp\;cHash=[a-z0-9]{32}"\>\<\/a\>/',
            $this->userInternalObject->render()
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
            $this->userInternalObject->render()
        );
    }
}

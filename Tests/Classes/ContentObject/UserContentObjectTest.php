<?php

namespace DMK\Mktools\ContentObject;

use Psr\Http\Message\ServerRequestInterface;
use Sys25\RnBase\Utility\Link;
use Sys25\RnBase\Utility\TYPO3;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
class UserContentObjectTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var \DMK\Mktools\ContentObject\UserContentObject
     */
    protected $userObject;

    protected $typoScriptFrontendController;

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @param bool $loadWithAjax
     * @param int  $mktoolsAjaxRequest
     *
     * @group integration
     *
     * @dataProvider dataProviderRenderTest
     */
    public function testRenderIfContentShouldNotBeLoadedWithAjax($loadWithAjax, $mktoolsAjaxRequest)
    {
        $contentObject = $this->getMock(ContentObjectRenderer::class, ['stdWrap', 'callUserFunction']);
        $contentObject->expects(self::any())
            ->method('stdWrap')
            ->will(
                self::returnCallback(function ($content, $configuration) {
                    return $configuration;
                })
            );
        $contentObject->data['tx_mktools_load_with_ajax'] = $loadWithAjax;
        $_GET['mktoolsAjaxRequest'] = $mktoolsAjaxRequest;

        $this->initializeFixtures($contentObject);

        $configuration = ['stdWrap.' => ['stdWrapConfiguration'], 'userFunc' => ''];
        self::assertEquals(
            ['stdWrapConfiguration'],
            $this->userObject->render($configuration)
        );
    }

    /**
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
     */
    protected function initializeFixtures($contentObject)
    {
        $this->typoScriptFrontendController = $this->getMock(
            TypoScriptFrontendController::class,
            [],
            [],
            '',
            false
        );

        if (TYPO3::isTYPO121OrHigher()) {
            $GLOBALS['TYPO3_REQUEST'] = $this->getMock(
                ServerRequestInterface::class,
                [],
                [],
                '',
                false
            );

            $typoScript = new FrontendTypoScript(new RootNode(), []);
            $typoScript->setSetupArray(['config' => 'test']);
            $GLOBALS['TYPO3_REQUEST']
                ->expects(self::any())
                ->method('getAttribute')
                ->with('frontend.typoscript')
                ->willReturn($typoScript);
        } else {
            $this->typoScriptFrontendController->tmpl = $this->getMock(
                TemplateService::class,
                [],
                [],
                '',
                false
            );
            $this->typoScriptFrontendController->tmpl->setup['config'] = 'test';
        }
        $GLOBALS['TSFE'] = $this->typoScriptFrontendController;

        if (TYPO3::isTYPO121OrHigher()) {
            $this->userObject = $this->getMock(
                UserContentObject::class,
                ['callUserFunction']
            );
            $this->userObject->setRequest($this->getMockBuilder(ServerRequestInterface::class)->getMock());
            $this->userObject->setContentObjectRenderer($contentObject);
        } else {
            $this->userObject = $this->getMock(
                UserContentObject::class,
                ['callUserFunction'],
                [$contentObject]
            );
        }
        $this->userObject
            ->expects(self::never())
            ->method('callUserFunction');
    }

    /**
     * @return bool[][]|number[][]|string[][]
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
    public function testRenderIfContentShouldBeLoadedWithAjax()
    {
        $contentObject = $this->getMock(ContentObjectRenderer::class, ['dummy']);
        $contentObject->data['tx_mktools_load_with_ajax'] = true;
        $contentObject->data['uid'] = 123;
        $_GET['mktoolsAjaxRequest'] = 0;

        $this->initializeFixtures($contentObject);

        $configurations = $this->getMock(\Sys25\RnBase\Configuration\Processor::class, ['init']);
        $configurations->expects(self::once())
            ->method('init')
            ->with(['config' => 'test'], $contentObject, 'mktools', 'mktools');
        GeneralUtility::addInstance(\Sys25\RnBase\Configuration\Processor::class, $configurations);

        $linkUtility = $this->getMock(Link::class, ['initByTS', 'makeUrl']);
        $linkUtility->expects(self::once())
            ->method('initByTS')
            ->with($configurations, 'lib.tx_mktools.loadUserWithAjaxUrl.', ['::ajaxcontentid' => 123])
            ->will(self::returnValue($linkUtility));
        $linkUtility->expects(self::once())
            ->method('makeUrl')
            ->will(self::returnValue('rendererdUrl'));
        GeneralUtility::addInstance(Link::class, $linkUtility);

        self::assertEquals(
            '<a class="ajax-links-autoload ajax-no-history" tabindex="-1" aria-hidden="true" data-ajaxreplaceid="c123" href="rendererdUrl"></a>',
            $this->userObject->render()
        );
    }
}

<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mktools" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mktools\ContentObject;

use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ServerRequestInterface;
use Sys25\RnBase\Utility\Link;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
     * @var UserContentObject
     */
    protected $userObject;

    protected $typoScriptFrontendController;

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    #[DataProvider('dataProviderRenderTest')]
    public function testRenderIfContentShouldNotBeLoadedWithAjax(bool $loadWithAjax, int $mktoolsAjaxRequest): void
    {
        $contentObject = $this->getMock(ContentObjectRenderer::class, ['stdWrap', 'callUserFunction']);
        $contentObject->expects(self::any())
            ->method('stdWrap')
            ->willReturnCallback(fn ($content, $configuration) => $configuration);
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
     * @param ContentObjectRenderer $contentObject
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

        $GLOBALS['TYPO3_REQUEST'] = $this->getMock(
            ServerRequestInterface::class,
            [],
            [],
            '',
            false
        );

        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray(['config' => 'test']);
        $GLOBALS['TYPO3_REQUEST']
            ->expects(self::any())
            ->method('getAttribute')
            ->with('frontend.typoscript')
            ->willReturn($typoScript);
        $GLOBALS['TSFE'] = $this->typoScriptFrontendController;

        $this->userObject = $this->getMock(
            UserContentObject::class,
            ['getTypoScriptFrontendController']
        );
        $this->userObject->setRequest($this->getMockBuilder(ServerRequestInterface::class)->getMock());
        $this->userObject->setContentObjectRenderer($contentObject);
        $this->userObject
            ->expects(self::any())
            ->method('getTypoScriptFrontendController')
            ->willReturn($this->typoScriptFrontendController);
    }

    /**
     * @return bool[][]|number[][]|string[][]
     */
    public static function dataProviderRenderTest(): array
    {
        return [
            [true, 1],
            [false, 1],
            [false, 0],
        ];
    }

    public function testRenderIfContentShouldBeLoadedWithAjax(): void
    {
        $contentObject = $this->getMock(ContentObjectRenderer::class, ['stdWrap']);
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
            ->willReturn($linkUtility);
        $linkUtility->expects(self::once())
            ->method('makeUrl')
            ->willReturn('rendererdUrl');
        GeneralUtility::addInstance(Link::class, $linkUtility);

        self::assertEquals(
            '<a class="ajax-links-autoload ajax-no-history" tabindex="-1" aria-hidden="true" data-ajaxreplaceid="c123" href="rendererdUrl"></a>',
            $this->userObject->render()
        );
    }
}

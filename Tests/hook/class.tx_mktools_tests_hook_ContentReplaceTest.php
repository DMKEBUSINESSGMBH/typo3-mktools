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
 * tx_mktools_tests_hook_ContentReplaceTest.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_tests_hook_ContentReplaceTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @test
     */
    public function contentPostProcAll()
    {
        $typoScriptFrontendController = $this->getTypoScriptFrontendController();
        $contentReplacer = new \DMK\Mktools\Hook\ContentReplaceHook();
        $contentReplacer->contentPostProcAll([], $typoScriptFrontendController);

        self::assertSame('test/firstReplaced/test/secondReplaced', $typoScriptFrontendController->content);
    }

    /**
     * @test
     */
    public function contentPostProcAllIfReplacingNotEnabled()
    {
        $typoScriptFrontendController = $this->getTypoScriptFrontendController(false);
        $contentReplacer = new \DMK\Mktools\Hook\ContentReplaceHook();
        $contentReplacer->contentPostProcAll([], $typoScriptFrontendController);

        self::assertSame('test/first/test/second', $typoScriptFrontendController->content);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTypoScriptFrontendController(bool $replacingEnabled = true): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        $typoScriptFrontendController = $this->getMockBuilder(\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typoScriptFrontendController->config = [
            'config' => [
                'tx_mktools.' => [
                    'contentreplace.' => [
                        'enable' => $replacingEnabled,
                        'search.' => [
                            '/first',
                            '/second',
                        ],
                        'replace.' => [
                            '/firstReplaced',
                            '/secondReplaced',
                        ],
                    ],
                ],
            ],
        ];
        $typoScriptFrontendController->content = 'test/first/test/second';

        return $typoScriptFrontendController;
    }
}

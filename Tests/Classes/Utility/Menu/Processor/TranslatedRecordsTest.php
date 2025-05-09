<?php

declare(strict_types=1);

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

namespace DMK\Mktools\Utility\Menu\Processor;

use Prophecy\PhpUnit\ProphecyTrait;
use Sys25\RnBase\Database\Connection;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TranslatedRecordsTest.
 *
 * @author     Mario Seidel <mario.seidel@dmk-ebusiness.com>
 * @license    http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TranslatedRecordsTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    use ProphecyTrait;

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        unset($_GET['tx_cal_controller']);
    }

    public function testProcessEmptyIfRecordNotExists(): void
    {
        $dbConnection = $this->prophesize(Connection::class);
        GeneralUtility::setSingletonInstance(Connection::class, $dbConnection->reveal());

        $pageRepository = $this->prophesize(PageRepository::class);
        GeneralUtility::addInstance(PageRepository::class, $pageRepository->reveal());

        $item = ['uid' => 123, 'title' => 'de'];

        $dbConnection->doSelect(
            '*',
            'tx_cal_event',
            ['where' => 'uid = 123']
        )
            ->shouldBeCalled()
            ->willReturn([$item]);
        $languageAspect = new LanguageAspect(
            1,
            1,
            LanguageAspect::OVERLAYS_ON_WITH_FLOATING
        );
        $pageRepository
            ->getLanguageOverlay(
                'tx_cal_event',
                $item,
                $languageAspect
            )
            ->shouldBeCalled()
            ->willReturn([]);

        $paramConfig = [
            'sysLanguageUid' => 1,
            'parametersConfiguration.' => [
                'tx_cal_controller.' => [
                    'uid' => 'tx_cal_event',
                ],
            ],
        ];
        $transRecord = new TranslatedRecords();
        $_GET['tx_cal_controller'] = ['uid' => 123];

        $result = $transRecord->processEmptyIfRecordNotExists('foobar', $paramConfig);

        $this->assertSame(false, $result, 'result must be false if no record was found');
    }
}

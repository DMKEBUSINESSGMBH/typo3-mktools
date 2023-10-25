<?php

declare(strict_types=1);

namespace DMK\Mktools\Utility\Menu\Processor;

use Prophecy\PhpUnit\ProphecyTrait;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Utility\TYPO3;
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

    /**
     * @test
     *
     * @return void
     */
    public function testProcessEmptyIfRecordNotExists()
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

        // no overlay found
        $expectedLanguageMode = 'hideNonTranslated';
        if (TYPO3::isTYPO121OrHigher()) {
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
        } else {
            $pageRepository
                ->getRecordOverlay(
                    'tx_cal_event',
                    $item,
                    1,
                    $expectedLanguageMode
                )
                ->shouldBeCalled()
                ->willReturn([]);
        }

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

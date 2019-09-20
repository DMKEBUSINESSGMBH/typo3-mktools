<?php
declare(strict_types=1);

namespace DMK\Mktools\Utility\Menu\Processor;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class TranslatedRecordsTest
 *
 * @author     Mario Seidel <mario.seidel@dmk-ebusiness.com>
 * @license    http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TranslatedRecordsTest extends \tx_rnbase_tests_BaseTestCase
{
    /**
     * @return void
     */
    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     *
     * @return void
     */
    public function testProcessValueIfRecordExists()
    {
        $dbConnection = $this->prophesize(\Tx_Rnbase_Database_Connection::class);
        GeneralUtility::setSingletonInstance(\Tx_Rnbase_Database_Connection::class, $dbConnection->reveal());

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

        //no overlay found
        $pageRepository->init(0)->shouldBeCalledOnce();
        $pageRepository
            ->getRecordOverlay(
                'tx_cal_event',
                $item,
                1,
                ''
            )
            ->shouldBeCalled()
            ->willReturn([]);

        $paramConfig = [
            'sysLanguageUid' => 1,
            'parametersConfiguration.' => [
                'tx_cal_controller.' => [
                    'uid' => 'tx_cal_event'
                ]
            ]
        ];
        $transRecord = new TranslatedRecords();
        GeneralUtility::_GETset(['uid' => 123], 'tx_cal_controller');

        $result = $transRecord->processEmptyIfRecordNotExists('foobar', $paramConfig);

        $this->assertSame(false, $result, 'result must be false if no record was found');
    }
}

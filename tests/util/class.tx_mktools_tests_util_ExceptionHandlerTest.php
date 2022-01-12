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

use DMK\Mktools\ErrorHandler\ExceptionHandler;

/**
 * @author Hannes Bochmann
 */
class tx_mktools_tests_util_ExceptionHandlerTest extends tx_mktools_tests_BaseTestCase
{
    /**
     * @var string
     */
    private $defaultPageTsConfig;

    /**
     * @var string
     */
    private $lockFile;

    /**
     * @var string
     */
    protected $devIpMaskBackup;

    /**
     * @var string
     */
    protected $remoteAddressBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->disableDevlog();
        $this->storeExtConf('mktools');

        $this->defaultPageTsConfig = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];

        $this->lockFile = \TYPO3\CMS\Core\Core\Environment::getVarPath().
            '/lock/mktoolsExceptionLock_2e41f8198a125606abc9a71493eebe48';
        \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(\TYPO3\CMS\Core\Core\Environment::getVarPath().'/lock');

        $this->devIpMaskBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];
        $this->remoteAddressBackup = $_SERVER['REMOTE_ADDR'];

        $this->resetIndependentEnvironmentCache();
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->restoreExtConf('mktools');

        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = $this->defaultPageTsConfig;

        @unlink($this->lockFile);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = $this->devIpMaskBackup;
        $_SERVER['REMOTE_ADDR'] = $this->remoteAddressBackup;
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsSendStatusHeaderWithCorrectException()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        //damit der redirect nicht ausgeführt wird
        $this->setExtConfVar('exceptionPage', '', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock();

        $exception = new Exception('test exception');
        $exceptionHandler->expects($this->once())
            ->method('sendStatusHeaders')
            ->with($exception);

        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsWriteLogEntriesCorrect()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        //damit der redirect nicht ausgeführt wird
        $this->setExtConfVar('exceptionPage', '', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock();

        $exception = new Exception('test exception');
        $exceptionHandler->expects($this->once())
            ->method('writeLogEntries')
            ->with($exception, 'WEB');

        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsLogNoExceptionPageDefinedIfNoDefined()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->once())
            ->method('logNoExceptionPageDefined');

        $exceptionHandler->expects($this->never())
            ->method('echoExceptionPageAndExit');

        $exception = new Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsLogNoExceptionPageDefinedNotIfExceptionPageDefined()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->never())
            ->method('logNoExceptionPageDefined');

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit');

        $exception = new Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenFileIsDefinedAsExceptionPage()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit')
            ->with(\Sys25\RnBase\Utility\Network::locationHeaderUrl('index.php'));

        $exception = new Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenTypoScriptIsDefinedAsExceptionPage()
    {
        self::markTestIncomplete(
            'This test has to be refactored.'
        );

        $this->setExtConfVar(
            'exceptionPage',
            'TYPOSCRIPT:typo3conf/ext/mktools/tests/fixtures/typoscript/errorHandling.txt',
            'mktools'
        );

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit')
            ->with(\Sys25\RnBase\Utility\Network::locationHeaderUrl('index.php'));

        $exception = new Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testWriteLogEntriesCallsParentIfExceptionIsNoMktoolsErrorExceptionAndLockCouldBeAcquired()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['writeLogEntriesByParent', 'lockAcquired']
        );

        $exceptionHandler->expects($this->once())
            ->method('lockAcquired')
            ->will($this->returnValue(true));

        $exception = new Exception('test');
        $context = 'egal';
        $exceptionHandler->expects($this->once())
            ->method('writeLogEntriesByParent')
            ->with($exception, $context);

        $method = new ReflectionMethod(
            ExceptionHandler::class,
            'writeLogEntries'
        );
        $method->setAccessible(true);
        $method->invoke($exceptionHandler, $exception, $context);
    }

    /**
     * @group unit
     */
    public function testWriteLogEntriesCallsParentNotIfExceptionIsMktoolsErrorException()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['writeLogEntriesByParent', 'lockAcquired']
        );

        $exceptionHandler->expects($this->never())
            ->method('lockAcquired');

        $exceptionHandler->expects($this->never())
            ->method('writeLogEntriesByParent');

        $method = new ReflectionMethod(
            ExceptionHandler::class,
            'writeLogEntries'
        );
        $method->setAccessible(true);
        $exception = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mktools_util_ErrorException',
            'test'
        );
        $context = 'egal';
        $method->invoke($exceptionHandler, $exception, $context);
    }

    /**
     * @group unit
     */
    public function testWriteLogEntriesCallsParentNotIfExceptionIsNoMktoolsErrorExceptionButLockCouldNotBeAcquired()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['writeLogEntriesByParent', 'lockAcquired']
        );

        $exceptionHandler->expects($this->once())
            ->method('lockAcquired')
            ->will($this->returnValue(false));

        $exceptionHandler->expects($this->never())
            ->method('writeLogEntriesByParent');

        $method = new ReflectionMethod(
            ExceptionHandler::class,
            'writeLogEntries'
        );
        $method->setAccessible(true);
        $exception = new Exception('test');
        $context = 'egal';
        $method->invoke($exceptionHandler, $exception, $context);
    }

    /**
     * @group unit
     */
    public function testGetLockFileByExceptionAndContextTouchesFileAndReturnsCorrectFilename()
    {
        $this->assertFileNotExists(
            $this->lockFile,
            'lock file schon da'
        );

        $exceptionHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExceptionHandler::class);

        $method = new ReflectionMethod(
            ExceptionHandler::class,
            'getLockFileByExceptionAndContext'
        );
        $method->setAccessible(true);
        $exception = new Exception('test');
        $context = 'egal';
        $method->invoke($exceptionHandler, $exception, $context);

        $this->assertFileExists(
            $this->lockFile,
            'lock file nicht angelegt'
        );

        $this->assertEmpty(
            file_get_contents(
                $this->lockFile
            ),
            'lock file nicht leer'
        );
    }

    /**
     * @group unit
     */
    public function testLockAcquiredReturnsFalseIfLockFileWasCreatedLessThanAMinuteAgo()
    {
        file_put_contents($this->lockFile, time());

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['getLockFileByExceptionAndContext']
        );

        $exceptionHandler->expects($this->once())
            ->method('getLockFileByExceptionAndContext')
            ->will($this->returnValue($this->lockFile));

        $method = new ReflectionMethod(
            ExceptionHandler::class,
            'lockAcquired'
        );
        $method->setAccessible(true);
        $exception = new Exception('test');
        $context = 'egal';
        $lockAcquired = $method->invoke($exceptionHandler, $exception, $context);

        $this->assertFalse(
            $lockAcquired,
            'lock doch bekommen'
        );
    }

    /**
     * @group unit
     */
    public function testLockAcquiredReturnsTrueIfLockFileWasCreatedMoreThanAMinuteAgo()
    {
        self::markTestSkipped('Problem with type3 9.5 config');
        file_put_contents($this->lockFile, time() - 61);

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['getLockFileByExceptionAndContext']
        );

        $exceptionHandler->expects($this->once())
            ->method('getLockFileByExceptionAndContext')
            ->will($this->returnValue($this->lockFile));

        $method = new ReflectionMethod(
            ExceptionHandler::class,
            'lockAcquired'
        );
        $method->setAccessible(true);
        $exception = new Exception('test');
        $context = 'egal';
        $lockAcquired = $method->invoke($exceptionHandler, $exception, $context);

        $this->assertTrue(
            $lockAcquired,
            'lock doch bekommen'
        );
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebOutPutsDebug()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        // wir prüfen einfach nur ob scheinbar 2 mal die Debug Meldung
        // von TYPO3 ausgegeben wird.
        $regularExpression = '/.*Mehr.*infos.*specialexception.*/s';
        $this->expectOutputRegex($regularExpression);

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined'], true);

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit');

        $exception = new Exception('test specialexception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testShouldExceptionBeDebuggedIfDevIpMaskMatches()
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REMOTE_ADDR');
        self::assertTrue(
            $this->callInaccessibleMethod(
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExceptionHandler::class),
                'shouldExceptionBeDebugged'
            )
        );
    }

    /**
     * @group unit
     */
    public function testShouldExceptionBeDebuggedIfDevIpMaskMatchesNot()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = 'invalid';
        self::assertFalse(
            $this->callInaccessibleMethod(
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExceptionHandler::class),
                'shouldExceptionBeDebugged'
            )
        );
    }

    /**
     * @param array $methods
     * @param bool  $shouldExceptionBeDebugged
     *
     * @return ExceptionHandler
     */
    private function getExceptionHandlerMock($methods = [], $shouldExceptionBeDebugged = false)
    {
        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            array_merge(
                $methods,
                ['shouldExceptionBeDebugged', 'writeLogEntries', 'sendStatusHeaders', 'echoExceptionPageAndExit']
            )
        );

        $exceptionHandler->expects($this->once())
            ->method('shouldExceptionBeDebugged')
            ->willReturn($shouldExceptionBeDebugged);

        return $exceptionHandler;
    }
}

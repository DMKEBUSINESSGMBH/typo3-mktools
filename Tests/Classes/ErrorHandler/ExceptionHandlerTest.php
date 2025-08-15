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

namespace DMK\Mktools\Tests\ErrorHandler;

use DMK\Mktools\ErrorHandler\ExceptionHandler;
use DMK\Mktools\Exception\RuntimeException;
use DMK\Mktools\Tests\BaseTestCase;
use Sys25\RnBase\Utility\Network;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExceptionHandlerTest.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class ExceptionHandlerTest extends BaseTestCase
{
    /**
     * @var string
     */
    private mixed $defaultPageTsConfig;

    private string $lockFile;

    /**
     * @var string
     */
    protected $devIpMaskBackup;

    /**
     * @var string
     */
    protected $remoteAddressBackup;

    protected function setUp(): void
    {
        $this->disableDevlog();
        $this->storeExtConf('mktools');

        $this->defaultPageTsConfig = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];

        $this->lockFile = Environment::getVarPath().
            '/lock/mktoolsExceptionLock_2e41f8198a125606abc9a71493eebe48';
        GeneralUtility::mkdir_deep(Environment::getVarPath().'/lock');

        $this->devIpMaskBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];
        $_SERVER['REMOTE_ADDR'] = '';

        $this->resetIndependentEnvironmentCache();
    }

    protected function tearDown(): void
    {
        $this->restoreExtConf('mktools');

        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = $this->defaultPageTsConfig;

        @unlink($this->lockFile);

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = $this->devIpMaskBackup;
        unset($_SERVER['REMOTE_ADDR']);

        restore_exception_handler();
    }

    public function testEchoExceptionWebCallsSendStatusHeaderWithCorrectException(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        // damit der redirect nicht ausgeführt wird
        $this->setExtConfVar('exceptionPage', '', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock();

        $exception = new \Exception('test exception');
        $exceptionHandler->expects($this->once())
            ->method('sendStatusHeaders')
            ->with($exception);

        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testEchoExceptionWebCallsWriteLogEntriesCorrect(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        // damit der redirect nicht ausgeführt wird
        $this->setExtConfVar('exceptionPage', '', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock();

        $exception = new \Exception('test exception');
        $exceptionHandler->expects($this->once())
            ->method('writeLogEntries')
            ->with($exception, 'WEB');

        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testEchoExceptionWebCallsLogNoExceptionPageDefinedIfNoDefined(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->once())
            ->method('logNoExceptionPageDefined');

        $exceptionHandler->expects($this->never())
            ->method('echoExceptionPageAndExit');

        $exception = new \Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testEchoExceptionWebCallsLogNoExceptionPageDefinedNotIfExceptionPageDefined(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->never())
            ->method('logNoExceptionPageDefined');

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit');

        $exception = new \Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenFileIsDefinedAsExceptionPage(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $this->setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(['logNoExceptionPageDefined']);

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit')
            ->with(Network::locationHeaderUrl('index.php'));

        $exception = new \Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenTypoScriptIsDefinedAsExceptionPage(): void
    {
        self::markTestSkipped(
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
            ->with(Network::locationHeaderUrl('index.php'));

        $exception = new \Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testWriteLogEntriesCallsParentIfExceptionIsNoMktoolsErrorExceptionAndLockCouldBeAcquired(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['writeLogEntriesByParent', 'lockAcquired']
        );

        $exceptionHandler->expects($this->once())
            ->method('lockAcquired')
            ->willReturn(true);

        $exception = new \Exception('test');
        $context = 'egal';
        $exceptionHandler->expects($this->once())
            ->method('writeLogEntriesByParent')
            ->with($exception, $context);

        $method = new \ReflectionMethod(
            ExceptionHandler::class,
            'writeLogEntries'
        );
        $method->invoke($exceptionHandler, $exception, $context);
    }

    public function testWriteLogEntriesCallsParentNotIfExceptionIsMktoolsErrorException(): void
    {
        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['writeLogEntriesByParent', 'lockAcquired']
        );

        $exceptionHandler->expects($this->never())
            ->method('lockAcquired');

        $exceptionHandler->expects($this->never())
            ->method('writeLogEntriesByParent');

        $method = new \ReflectionMethod(
            ExceptionHandler::class,
            'writeLogEntries'
        );

        $exception = GeneralUtility::makeInstance(
            RuntimeException::class,
            'test'
        );
        $context = 'egal';
        $method->invoke($exceptionHandler, $exception, $context);
    }

    public function testWriteLogEntriesCallsParentNotIfExceptionIsNoMktoolsErrorExceptionButLockCouldNotBeAcquired(): void
    {
        self::markTestSkipped('Problem with type3 9.5 config');

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['writeLogEntriesByParent', 'lockAcquired']
        );

        $exceptionHandler->expects($this->once())
            ->method('lockAcquired')
            ->willReturn(false);

        $exceptionHandler->expects($this->never())
            ->method('writeLogEntriesByParent');

        $method = new \ReflectionMethod(
            ExceptionHandler::class,
            'writeLogEntries'
        );

        $exception = new \Exception('test');
        $context = 'egal';
        $method->invoke($exceptionHandler, $exception, $context);
    }

    public function testGetLockFileByExceptionAndContextTouchesFileAndReturnsCorrectFilename(): void
    {
        $this->assertFileDoesNotExist(
            $this->lockFile,
            'lock file schon da'
        );

        $exceptionHandler = GeneralUtility::makeInstance(ExceptionHandler::class);

        $method = new \ReflectionMethod(
            ExceptionHandler::class,
            'getLockFileByExceptionAndContext'
        );

        $exception = new \Exception('test');
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

    public function testLockAcquiredReturnsFalseIfLockFileWasCreatedLessThanAMinuteAgo(): void
    {
        file_put_contents($this->lockFile, time());

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['getLockFileByExceptionAndContext']
        );

        $exceptionHandler->expects($this->once())
            ->method('getLockFileByExceptionAndContext')
            ->willReturn($this->lockFile);

        $method = new \ReflectionMethod(
            ExceptionHandler::class,
            'lockAcquired'
        );

        $exception = new \Exception('test');
        $context = 'egal';
        $lockAcquired = $method->invoke($exceptionHandler, $exception, $context);

        $this->assertFalse(
            $lockAcquired,
            'lock doch bekommen'
        );
    }

    public function testLockAcquiredReturnsTrueIfLockFileWasCreatedMoreThanAMinuteAgo(): void
    {
        file_put_contents($this->lockFile, time() - 61);

        $exceptionHandler = $this->getMock(
            ExceptionHandler::class,
            ['getLockFileByExceptionAndContext']
        );

        $exceptionHandler->expects($this->once())
            ->method('getLockFileByExceptionAndContext')
            ->willReturn($this->lockFile);

        $method = new \ReflectionMethod(
            ExceptionHandler::class,
            'lockAcquired'
        );

        $exception = new \Exception('test');
        $context = 'egal';
        $lockAcquired = $method->invoke($exceptionHandler, $exception, $context);

        $this->assertTrue(
            $lockAcquired,
            'lock doch bekommen'
        );
    }

    public function testEchoExceptionWebOutPutsDebug(): void
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

        $exception = new \Exception('test specialexception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    public function testShouldExceptionBeDebuggedIfDevIpMaskMatches(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        self::assertTrue(
            $this->callInaccessibleMethod(
                GeneralUtility::makeInstance(ExceptionHandler::class),
                'shouldExceptionBeDebugged'
            )
        );
    }

    public function testShouldExceptionBeDebuggedIfDevIpMaskMatchesNot(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = 'invalid';
        self::assertFalse(
            $this->callInaccessibleMethod(
                GeneralUtility::makeInstance(ExceptionHandler::class),
                'shouldExceptionBeDebugged'
            )
        );
    }

    /**
     * @return ExceptionHandler
     */
    private function getExceptionHandlerMock(array $methods = [], bool $shouldExceptionBeDebugged = false)
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

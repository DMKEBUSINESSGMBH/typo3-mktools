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

/**
 * @author Hannes Bochmann
 */
class tx_mktools_tests_util_ExceptionHandler_testcase extends tx_rnbase_tests_BaseTestCase
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
        \DMK\Mklib\Utility\Tests::disableDevlog();
        \DMK\Mklib\Utility\Tests::storeExtConf('mktools');

        $this->defaultPageTsConfig = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];

        $this->lockFile = \Sys25\RnBase\Utility\Environment::getPublicPath().'typo3temp/mktools/locks/2e41f8198a125606abc9a71493eebe48.txt';

        $this->devIpMaskBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];
        $this->remoteAddressBackup = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        \DMK\Mklib\Utility\Tests::restoreExtConf('mktools');

        $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] = $this->defaultPageTsConfig;

        @unlink(\Sys25\RnBase\Utility\Environment::getPublicPath().'typo3temp/mktools/locks/2e41f8198a125606abc9a71493eebe48.txt');

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = $this->devIpMaskBackup;
        $_SERVER['REMOTE_ADDR'] = $this->remoteAddressBackup;
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsSendStatusHeaderWithCorrectException()
    {
        //damit der redirect nicht ausgeführt wird
        \DMK\Mklib\Utility\Tests::setExtConfVar('exceptionPage', '', 'mktools');

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
        //damit der redirect nicht ausgeführt wird
        \DMK\Mklib\Utility\Tests::setExtConfVar('exceptionPage', '', 'mktools');

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
        \DMK\Mklib\Utility\Tests::setExtConfVar('exceptionPage', 'FILE:', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));

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
        \DMK\Mklib\Utility\Tests::setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));

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
        \DMK\Mklib\Utility\Tests::setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        $exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit')
            ->with(tx_rnbase_util_Network::locationHeaderUrl('index.php'));

        $exception = new Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testEchoExceptionWebCallsEchoExceptionPageAndExitWithCorrectLinkWhenTypoScriptIsDefinedAsExceptionPage()
    {
        \DMK\Mklib\Utility\Tests::setExtConfVar(
            'exceptionPage',
            'TYPOSCRIPT:typo3conf/ext/mktools/tests/fixtures/typoscript/errorHandling.txt',
            'mktools'
        );

        $exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'));

        $exceptionHandler->expects($this->once())
            ->method('echoExceptionPageAndExit')
            ->with(tx_rnbase_util_Network::locationHeaderUrl('index.php'));

        $exception = new Exception('test exception');
        $exceptionHandler->echoExceptionWeb($exception);
    }

    /**
     * @group unit
     */
    public function testWriteLogEntriesCallsParentIfExceptionIsNoMktoolsErrorExceptionAndLockCouldBeAcquired()
    {
        $exceptionHandler = $this->getMock(
            'tx_mktools_util_ExceptionHandler',
            array('writeLogEntriesByParent', 'lockAcquired')
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
            'tx_mktools_util_ExceptionHandler',
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
        $exceptionHandler = $this->getMock(
            'tx_mktools_util_ExceptionHandler',
            array('writeLogEntriesByParent', 'lockAcquired')
        );

        $exceptionHandler->expects($this->never())
            ->method('lockAcquired');

        $exceptionHandler->expects($this->never())
            ->method('writeLogEntriesByParent');

        $method = new ReflectionMethod(
            'tx_mktools_util_ExceptionHandler',
            'writeLogEntries'
        );
        $method->setAccessible(true);
        $exception = tx_rnbase::makeInstance(
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
        $exceptionHandler = $this->getMock(
            'tx_mktools_util_ExceptionHandler',
            array('writeLogEntriesByParent', 'lockAcquired')
        );

        $exceptionHandler->expects($this->once())
            ->method('lockAcquired')
            ->will($this->returnValue(false));

        $exceptionHandler->expects($this->never())
            ->method('writeLogEntriesByParent');

        $method = new ReflectionMethod(
            'tx_mktools_util_ExceptionHandler',
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

        $exceptionHandler = tx_rnbase::makeInstance('tx_mktools_util_ExceptionHandler');

        $method = new ReflectionMethod(
            'tx_mktools_util_ExceptionHandler',
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
            'tx_mktools_util_ExceptionHandler',
            array('getLockFileByExceptionAndContext')
        );

        $exceptionHandler->expects($this->once())
            ->method('getLockFileByExceptionAndContext')
            ->will($this->returnValue($this->lockFile));

        $method = new ReflectionMethod(
            'tx_mktools_util_ExceptionHandler',
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
        file_put_contents($this->lockFile, time() - 61);

        $exceptionHandler = $this->getMock(
            'tx_mktools_util_ExceptionHandler',
            array('getLockFileByExceptionAndContext')
        );

        $exceptionHandler->expects($this->once())
            ->method('getLockFileByExceptionAndContext')
            ->will($this->returnValue($this->lockFile));

        $method = new ReflectionMethod(
            'tx_mktools_util_ExceptionHandler',
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
        \DMK\Mklib\Utility\Tests::setExtConfVar('exceptionPage', 'FILE:index.php', 'mktools');

        // wir prüfen einfach nur ob scheinbar 2 mal die Debug Meldung
        // von TYPO3 ausgegeben wird.
        $regularExpression = '/.*Mehr.*infos.*specialexception.*/s';
        $this->expectOutputRegex($regularExpression);

        $exceptionHandler = $this->getExceptionHandlerMock(array('logNoExceptionPageDefined'), true);

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
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'] = tx_rnbase_util_Misc::getIndpEnv('REMOTE_ADDR');
        self::assertTrue(
            $this->callInaccessibleMethod(
                tx_rnbase::makeInstance('tx_mktools_util_ExceptionHandler'),
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
                tx_rnbase::makeInstance('tx_mktools_util_ExceptionHandler'),
                'shouldExceptionBeDebugged'
            )
        );
    }

    /**
     * @param array $methods
     * @param bool  $shouldExceptionBeDebugged
     *
     * @return tx_mktools_util_ExceptionHandler
     */
    private function getExceptionHandlerMock($methods = array(), $shouldExceptionBeDebugged = false)
    {
        $exceptionHandler = $this->getMock(
            'tx_mktools_util_ExceptionHandler',
            array_merge(
                $methods,
                array('shouldExceptionBeDebugged', 'writeLogEntries', 'sendStatusHeaders', 'echoExceptionPageAndExit')
            )
        );

        $exceptionHandler->expects($this->once())
            ->method('shouldExceptionBeDebugged')
            ->will($this->returnValue($shouldExceptionBeDebugged));

        return $exceptionHandler;
    }
}

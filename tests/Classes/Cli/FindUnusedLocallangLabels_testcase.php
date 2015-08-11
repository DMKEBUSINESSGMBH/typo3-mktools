<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('Tx_Mktools_Cli_FindUnusedLocallangLabels');

/**
 * Tx_Mktools_FindUnusedLocallangLabels_testcase
 *
 * @package 		TYPO3
 * @subpackage	 	mktools
 * @author 			Hannes Bochmann
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class Tx_Mktools_FindUnusedLocallangLabels_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		unset($_SERVER['argv']);
	}

	/**
	 * @group unit
	 */
	public function testShowUnusedLocallangLabelsShowsHelpWhenNoLocallangFilePassed() {
		$_SERVER['argv'] = array();
		$cliController = $this->getMock('Tx_Mktools_Cli_FindUnusedLocallangLabels', array('cli_help', 'cli_echo'));

		$cliController->expects(self::once())
			->method('cli_help');

		$cliController->expects(self::never())
			->method('cli_echo');

		$cliController->showUnusedLocallangLabels();
	}

	/**
	 * @group unit
	 */
	public function testShowUnusedLocallangLabelsShowsHelpWhenLocallangFilePassedButNoSearchFolders() {
		$_SERVER['argv'] = array('--locallangFile=some/file');
		$cliController = $this->getMock('Tx_Mktools_Cli_FindUnusedLocallangLabels', array('cli_help', 'cli_echo'));

		$cliController->expects(self::once())
			->method('cli_help');

		$cliController->expects(self::never())
			->method('cli_echo');

		$cliController->showUnusedLocallangLabels();
	}

	/**
	 * @group unit
	 */
	public function testShowUnusedLocallangLabelsWhenEverythingConfigured() {
		$_SERVER['argv'] = array(
			'--locallangFile=typo3conf/ext/mktools/tests/fixtures/xml/locallang.xml',
			'--searchFolders=typo3conf/ext/mktools/tests/fixtures/searchFolders/1,typo3conf/ext/mktools/tests/fixtures/searchFolders/2',
		);
		$cliController = $this->getMock('Tx_Mktools_Cli_FindUnusedLocallangLabels', array('cli_help', 'cli_echo'));

		$cliController->expects(self::never())
			->method('cli_help');

		$cliController->expects(self::exactly(2))
			->method('cli_echo');

		$cliController->expects(self::at(0))
			->method('cli_echo')
			->with("exists_not_in_files wird nicht verwendet\n");

		$cliController->expects(self::at(1))
			->method('cli_echo')
			->with("exists_also_not_in_files wird nicht verwendet\n");

		$cliController->showUnusedLocallangLabels();
	}
}

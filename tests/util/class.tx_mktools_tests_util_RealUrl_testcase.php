<?php
/*  **********************************************************************  **
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mktools_util_RealUrl');
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class tx_mktools_tests_util_RealUrl_testcase  extends Tx_Phpunit_TestCase {

	/**
	 * @var string
	 */
	private $realUrlConfigurationFile;
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		tx_mklib_tests_Util::storeExtConf('mktools');
		
		$this->realUrlConfigurationFile = t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfig.php';
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', $this->realUrlConfigurationFile, 'mktools'
		);
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationTemplate', 
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate.php', 
			'mktools'
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		tx_mklib_tests_Util::restoreExtConf('mktools');
		@unlink($this->realUrlConfigurationFile);
	}
	
	/**
	 * @group unit
	 */
	public function testGetPagesWithFixedPostVarTypeCallsDoSelectCorrect() {
		$dbUtil = $this->getDbUtilMock();
		
		$expectedWhat = '*';
		$expectedFrom = 'pages';
		$expectedOptions = array(
			'enablefieldsfe'	=> 1,
			'wrapperclass'		=> 'tx_mktools_model_Pages',
			'where'				=> 'tx_mktools_fixedpostvartype > 0'
		);
		
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with($expectedWhat, $expectedFrom, $expectedOptions)
			->will($this->returnValue('test'));
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', array('getDbUtil')
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('getDbUtil')
			->will($this->returnValue($dbUtil));
			
		$this->assertEquals(
			'test',
			$realUrlUtil::getPagesWithFixedPostVarType(),
			'falscher rücgabewert'
		);
	}
	
	/**
	 * @group unit
	 */
	public function testAreTherePagesWithFixedPostVarTypeModifiedLaterThanCallsDoSelectCorrectAndReturnsTrueIfCount() {
		$modificationTimeStamp = 123;
		
		$dbUtil = $this->getDbUtilMock();
		
		$expectedWhat = 'COUNT(uid) AS uid_count';
		$expectedFrom = 'pages';
		$expectedOptions = array(
			'enablefieldsfe'	=> 	1,
			'where'				=> 	'tx_mktools_fixedpostvartype > 0 AND tstamp > ' . 
									$modificationTimeStamp
		);
		
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with($expectedWhat, $expectedFrom, $expectedOptions)
			->will($this->returnValue(array(0 => array('uid_count' => 456))));
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', array('getDbUtil')
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('getDbUtil')
			->will($this->returnValue($dbUtil));
			
		$this->assertTrue(
			$realUrlUtil::areTherePagesWithFixedPostVarTypeModifiedLaterThan(
				$modificationTimeStamp
			)
		);
	}
	
	/**
	 * @group unit
	 */
	public function testAreTherePagesWithFixedPostVarTypeModifiedLaterThanCallsDoSelectCorrectAndReturnsFalseIfNoCount() {
		$modificationTimeStamp = 123;
		
		$dbUtil = $this->getDbUtilMock();
		
		$expectedWhat = 'COUNT(uid) AS uid_count';
		$expectedFrom = 'pages';
		$expectedOptions = array(
			'enablefieldsfe'	=> 	1,
			'where'				=> 	'tx_mktools_fixedpostvartype > 0 AND tstamp > ' . 
									$modificationTimeStamp
		);
		
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with($expectedWhat, $expectedFrom, $expectedOptions)
			->will($this->returnValue(array(0 => array('uid_count' => 0))));
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', array('getDbUtil')
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('getDbUtil')
			->will($this->returnValue($dbUtil));
			
		$this->assertFalse(
			$realUrlUtil::areTherePagesWithFixedPostVarTypeModifiedLaterThan(
				$modificationTimeStamp
			)
		);
	}
	
	/**
	 * @return tx_rnbase_util_DB
	 */
	private function getDbUtilMock() {
		return $this->getMockClass(
			'tx_rnbase_util_DB', array('doSelect')
		);
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreTherePagesWithFixedPostVarTypeModifiedLaterThanWithTimestampZero() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', 'unknown', 'mktools'
		);
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		$expectedTimeStamp = 0;
		$realUrlUtil::staticExpects($this->once())
			->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
			->with($expectedTimeStamp);
			
		$realUrlUtil::needsRealUrlConfigurationToBeGenerated();
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreTherePagesWithFixedPostVarTypeModifiedLaterThanWithTimestampOfConfigFile() {
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		touch($this->realUrlConfigurationFile);
		$expectedTimeStamp = filemtime($this->realUrlConfigurationFile);
		$realUrlUtil::staticExpects($this->once())
			->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
			->with($expectedTimeStamp);
			
		$realUrlUtil::needsRealUrlConfigurationToBeGenerated();
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreThereFixedPostVarTypesModifiedLaterThanWithTimestampZero() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', 'unknown', 'mktools'
		);
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		$expectedTimeStamp = 0;
		$realUrlUtil::staticExpects($this->once())
			->method('areThereFixedPostVarTypesModifiedLaterThan')
			->with($expectedTimeStamp);
			
		$realUrlUtil::needsRealUrlConfigurationToBeGenerated();
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsAreThereFixedPostVarTypesModifiedLaterThanWithTimestampOfConfigFile() {
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		touch($this->realUrlConfigurationFile);
		$expectedTimeStamp = filemtime($this->realUrlConfigurationFile);
		$realUrlUtil::staticExpects($this->once())
			->method('areThereFixedPostVarTypesModifiedLaterThan')
			->with($expectedTimeStamp);
			
		$realUrlUtil::needsRealUrlConfigurationToBeGenerated();
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfPagesAndFixedPostVarTypesWereModified() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', 'unknown', 'mktools'
		);
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
			->will($this->returnValue(true));
		$realUrlUtil::staticExpects($this->once())
			->method('areThereFixedPostVarTypesModifiedLaterThan')
			->will($this->returnValue(true));
			
		$this->assertTrue(
			$realUrlUtil::needsRealUrlConfigurationToBeGenerated()
		);
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfOnlyPagesWereModified() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', 'unknown', 'mktools'
		);
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
			->will($this->returnValue(true));
		$realUrlUtil::staticExpects($this->once())
			->method('areThereFixedPostVarTypesModifiedLaterThan')
			->will($this->returnValue(false));
			
		$this->assertTrue(
			$realUrlUtil::needsRealUrlConfigurationToBeGenerated()
		);
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsTrueIfOnlyFixedPostVarTypesWereModified() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', 'unknown', 'mktools'
		);
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
			->will($this->returnValue(false));
		$realUrlUtil::staticExpects($this->once())
			->method('areThereFixedPostVarTypesModifiedLaterThan')
			->will($this->returnValue(true));
			
		$this->assertTrue(
			$realUrlUtil::needsRealUrlConfigurationToBeGenerated()
		);
	}
	
	/**
	 * @group unit
	 */
	public function testNeedsRealUrlConfigurationToBeGeneratedCallsReturnsFalseIfPagesAndFixedPostVarTypesWerenotModified() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', 'unknown', 'mktools'
		);
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', 
			array(
				'areTherePagesWithFixedPostVarTypeModifiedLaterThan',
				'areThereFixedPostVarTypesModifiedLaterThan'
			)
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('areTherePagesWithFixedPostVarTypeModifiedLaterThan')
			->will($this->returnValue(false));
		$realUrlUtil::staticExpects($this->once())
			->method('areThereFixedPostVarTypesModifiedLaterThan')
			->will($this->returnValue(false));
			
		$this->assertFalse(
			$realUrlUtil::needsRealUrlConfigurationToBeGenerated()
		);
	}
	
	/**
	 * @group unit
	 */
	public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesNoFileIfNoPagesGiven() {
		$this->assertFalse(
			tx_mktools_util_RealUrl::generateSerializedRealUrlConfigurationFileByPages(array())
		);
		
		$this->assertFileNotExists($this->realUrlConfigurationFile, 'Datei doch generiert.');
	}
	
	/**
	 * @group unit
	 */
	public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesNoFileIfPagesGivenButNoTemplate() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationTemplate', 
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/empty', 
			'mktools'
		);
		$pages = array(
			0 => tx_rnbase::makeInstance(
				'tx_mktools_model_Pages', 
				array(
					'tx_mktools_fixedpostvartype' => array('identifier' => 'firstIdentifier'),
					'uid' => 1
				)
			)
		);
		$this->assertFalse(
			tx_mktools_util_RealUrl::generateSerializedRealUrlConfigurationFileByPages($pages)
		);
		
		$this->assertFileNotExists($this->realUrlConfigurationFile, 'Datei doch generiert.');
	}
	
	/**
	 * @group unit
	 */
	public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesNoFileIfPagesGivenButNoDestinationFileConfigured() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationFile', '', 'mktools'
		);
		$pages = array(
			0 => tx_rnbase::makeInstance(
				'tx_mktools_model_Pages', 
				array(
					'tx_mktools_fixedpostvartype' => array('identifier' => 'firstIdentifier'),
					'uid' => 1
				)
			)
		);
		$this->assertFalse(
			tx_mktools_util_RealUrl::generateSerializedRealUrlConfigurationFileByPages($pages)
		);
		
		$this->assertFileNotExists($this->realUrlConfigurationFile, 'Datei doch generiert.');
	}
	
	/**
	 * @group unit
	 */
	public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesFileCorrectIfPagesGiven() {
		$pages = array(
			0 => tx_rnbase::makeInstance(
				'tx_mktools_model_Pages', 
				array(
					'tx_mktools_fixedpostvartype' => array('identifier' => 'firstIdentifier'),
					'uid' => 1
				)
			),
			1 => tx_rnbase::makeInstance(
				'tx_mktools_model_Pages', 
				array(
					'tx_mktools_fixedpostvartype' => array('identifier' => 'secondIdentifier'),
					'uid' => 2
				)
			),
		);	
		$this->assertTrue(
			tx_mktools_util_RealUrl::generateSerializedRealUrlConfigurationFileByPages($pages, false),
			'datei doch nicht geschrieben'
		);
		
		$this->assertEquals(
			file_get_contents(t3lib_extMgm::extPath('mktools') . 'tests/fixtures/expectedRealUrlConfig.php'), 
			file_get_contents($this->realUrlConfigurationFile),
			'Datei falsch generiert'
		);	
	}
	
	/**
	 * @group unit
	 */
	public function testGenerateSerializedRealUrlConfigurationFileByPagesGeneratesFileCorrectIfMarkerExistsSeveralTimes() {
		tx_mklib_tests_Util::setExtConfVar(
			'realUrlConfigurationTemplate', 
			t3lib_extMgm::extPath('mktools') . 'tests/fixtures/realUrlConfigTemplate2.php', 
			'mktools'
		);
		
		$pages = array(
			0 => tx_rnbase::makeInstance(
				'tx_mktools_model_Pages', 
				array(
					'tx_mktools_fixedpostvartype' => array('identifier' => 'firstIdentifier'),
					'uid' => 1
				)
			),
		);	
		$this->assertTrue(
			tx_mktools_util_RealUrl::generateSerializedRealUrlConfigurationFileByPages($pages, false),
			'datei doch nicht geschrieben'
		);
		
		$this->assertEquals(
			file_get_contents(t3lib_extMgm::extPath('mktools') . 'tests/fixtures/expectedRealUrlConfig2.php'), 
			file_get_contents($this->realUrlConfigurationFile),
			'Datei falsch generiert'
		);	
	}
	
	/**
	 * @group unit
	 */
	public function testAreThereFixedPostVarTypesModifiedLaterThanCallsDoSelectCorrectAndReturnsTrueIfCount() {
		$modificationTimeStamp = 123;
		
		$dbUtil = $this->getDbUtilMock();
		
		$expectedWhat = 'COUNT(uid) AS uid_count';
		$expectedFrom = 'tx_mktools_fixedpostvartypes';
		$expectedOptions = array(
			'enablefieldsfe'	=> 	1,
			'where'				=> 	'tstamp > ' . $modificationTimeStamp
		);
		
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with($expectedWhat, $expectedFrom, $expectedOptions)
			->will($this->returnValue(array(0 => array('uid_count' => 456))));
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', array('getDbUtil')
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('getDbUtil')
			->will($this->returnValue($dbUtil));
			
		$this->assertTrue(
			$realUrlUtil::areThereFixedPostVarTypesModifiedLaterThan(
				$modificationTimeStamp
			)
		);
	}
	
	/**
	 * @group unit
	 */
	public function testAreThereFixedPostVarTypesModifiedLaterThanCallsDoSelectCorrectAndReturnsFalseIfNoCount() {
		$modificationTimeStamp = 123;
		
		$dbUtil = $this->getDbUtilMock();
		
		$expectedWhat = 'COUNT(uid) AS uid_count';
		$expectedFrom = 'tx_mktools_fixedpostvartypes';
		$expectedOptions = array(
			'enablefieldsfe'	=> 	1,
			'where'				=> 	'tstamp > ' . $modificationTimeStamp
		);
		
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with($expectedWhat, $expectedFrom, $expectedOptions)
			->will($this->returnValue(array(0 => array('uid_count' => 0))));
			
		$realUrlUtil = $this->getMockClass(
			'tx_mktools_util_RealUrl', array('getDbUtil')
		);
		
		$realUrlUtil::staticExpects($this->once())
			->method('getDbUtil')
			->will($this->returnValue($dbUtil));
			
		$this->assertFalse(
			$realUrlUtil::areThereFixedPostVarTypesModifiedLaterThan(
				$modificationTimeStamp
			)
		);
	}
}
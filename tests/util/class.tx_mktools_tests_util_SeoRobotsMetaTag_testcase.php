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
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_mklib_util_DB');
tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_mktools_util_SeoRobotsMetaTag');
/**
 *
 * @package tx_mktools
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mktools_tests_util_SeoRobotsMetaTag_testcase  extends tx_phpunit_database_testcase {
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $workspaceIdAtStart;
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $name
	 */
	public function __construct ($name=null) {
		parent::__construct ($name);
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
	
		$this->workspaceIdAtStart = $GLOBALS['BE_USER']->workspace;
		$GLOBALS['BE_USER']->setWorkspace(0);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	public function setUp() {
		$this->createDatabase();
		// assuming that test-database can be created otherwise PHPUnit will skip the test
		$db = $this->useTestDatabase();
		$this->importStdDB();
		$extensions = array('cms', 'mktools', 'templavoila', 'realurl');
		
		//tq_seo bringt in der TCA Felder mit, die auch in der DB sein müssen
		if(t3lib_extMgm::isLoaded('tq_seo')){
			$extensions[] = 'tq_seo';
		}
		
		$this->importExtensions($extensions);
		$this->importDataSet( t3lib_extMgm::extPath('mktools').'tests/fixtures/xml/pages.xml');
		tx_rnbase_util_Misc::prepareTSFE();
		tx_mklib_tests_Util::disableDevlog();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	public function tearDown () {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
		$GLOBALS['BE_USER']->setWorkspace($this->workspaceIdAtStart);
	}
	
	/**
	 * @group integration
	 */
	public function testGetDefaultValueWhenNoValueSetAndNoInheritedValueExists() {
		$GLOBALS['TSFE']->id = 1;
		$util = new tx_mktools_util_SeoRobotsMetaTag;
		$value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));
	
		$this->assertEquals('test', $value ,'Falscher Wert zurückgeliefert');
	}
	
	/**
	 * @group integration
	 */
	public function testGetCorrectValue() {
		$GLOBALS['TSFE']->id = 2;
		$util = new tx_mktools_util_SeoRobotsMetaTag;
		$value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));
		$this->assertEquals('NOINDEX,FOLLOW', $value ,'Falscher Wert zurückgeliefert');
		
		$GLOBALS['TSFE']->id = 4;
		$util = new tx_mktools_util_SeoRobotsMetaTag;
		$value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));
		
		$this->assertEquals('INDEX,NOFOLLOW', $value ,'Falscher Wert zurückgeliefert');
	}
	
	/**
	 * @group integration
	 */
	public function testGetCorrectInheritedValue() {
		$GLOBALS['TSFE']->id = 3;
		$util = new tx_mktools_util_SeoRobotsMetaTag;
		$value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));
		$this->assertEquals('NOINDEX,FOLLOW', $value ,'Falscher Wert zurückgeliefert');
		
		$GLOBALS['TSFE']->id = 5;
		$util = new tx_mktools_util_SeoRobotsMetaTag;
		$value = $util->getSeoRobotsMetaTagValue('', array('default' => 'test'));
		$this->assertEquals('INDEX,NOFOLLOW', $value ,'Falscher Wert zurückgeliefert');
	}
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/util/class.tx_mktools_tests_util_SeoRobotsMetaTag_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/tests/util/class.tx_mktools_tests_util_SeoRobotsMetaTag_testcase.php']);
}

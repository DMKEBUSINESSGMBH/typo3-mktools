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
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('t3lib_utility_Http');
/**
 *
 * @package tx_mktools
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mktools_util_PageNotFoundHandling
{

	/**
	 * @var tslib_fe
	 */
	private $tsfe = null;
	private $reason = '';
	private $header = '';

	/**
	 * @var 	tx_rnbase_configurations
	 */
	private $configurations = null;

	/**
	 *
	 * @param tslib_fe $tsfe
	 * @return tx_mktools_util_PageNotFoundHandling
	 */
	public static function getInstance(tslib_fe $tsfe, $reason = '', $header = '')
	{
		return new self($tsfe);
	}

	public function __construct(tslib_fe $tsfe, $reason = '', $header = '')
	{
		if (!($tsfe instanceof tslib_fe)) {
			throw new InvalidArgumentException(
				'The first parameter has to be a instance of "tslib_fe"!',
				intval(ERROR_CODE_MKTOOLS.'100')
			);
		}
		$this->tsfe = $tsfe;
		$this->reason = $reason;
		$this->header = $header;
	}

	/**
	 *
	 * @param string $code der Inhalt von TYPO3_CONF_VARS['FE']['pageNotFound_handling']
	 * @return boolean
	 */
	public function handlePageNotFound($code = '')
	{
		// keine mktools config, weiter machen!
		if (!t3lib_div::isFirstPartOfStr($code, 'MKTOOLS_')) {
			return null;
		}

		$code = substr($code, strlen('MKTOOLS_'));
		$type = substr($code, 0, strpos($code, ':'));
		$data = substr($code, strlen($type)+1);

		if ($type == 'TYPOSCRIPT') {
			if (!empty($data)) {
				$addTS = $data;
			}
		}
		// die Config initial anlegen!
		$confirgurations = $this->getConfirgurations($addTS);

		// Auf zu ignorierende Fehlercodes prüfen.
		$ignorecodes = $confirgurations->get('pagenotfoundhandling.ignorecodes');
		if (t3lib_div::inList($ignorecodes, $this->getTsFe()->pageNotFound)) {
			return;
		}

		// Type und data aus dem TS holen.
		if ($type == 'TYPOSCRIPT') {
			$type = $confirgurations->get('pagenotfoundhandling.type');
			$data = $confirgurations->get('pagenotfoundhandling.data');
			$logPageNotFound = 
				$confirgurations->get('pagenotfoundhandling.logPageNotFound');
		}

		if (empty($type) || empty($data)) {
			throw new InvalidArgumentException(
				'Type or data missing! (MKTOOLS_[TYPE]:[DATA])',
				intval(ERROR_CODE_MKTOOLS.'110')
			);
		}
		
		if($logPageNotFound) {
			$this->logPageNotFound($data, $type);
		}

		switch ($type) {
			case 'READFILE':
				$this->printContent($data);
				break;
			case 'REDIRECT':
				$this->redirectTo($data);
				break;
			default:
				throw new InvalidArgumentException(
					'Unknown type "'.$type.'" found!',
					intval(ERROR_CODE_MKTOOLS.'110')
				);
		}

	}
	
	/**
	 * @param mixed $data
	 * @param string $type
	 * 
	 * @return void
	 */
	private function logPageNotFound($data, $type) {
		tx_rnbase::load('tx_rnbase_util_Logger');
		tx_rnbase_util_Logger::info(
			'Seite nicht gefunden', 
			'mktools',
			array(
				'reason'		=> $this->reason,
				'code'			=> $this->getTsFe()->pageNotFound,
				'REQUEST_URI' 	=> t3lib_div::getIndpEnv('REQUEST_URI'),
				'data'	 		=> $data,
				'type'			=> $type
			)
		);
	}

	private function printContent($url)
	{
		// wir versuchen erstmal den inhalt der URL zu holen
		$content = t3lib_div::getURL(
			$this->getFileAbsFileName($url)
		);

		// wir liefern den 404 aus, ohne einen redirect!
		// damit bleibt auch die url die gleiche :)
		if($content) {
			$content = str_replace(
				'###CURRENT_URL###',
				t3lib_div::getIndpEnv('REQUEST_URI'),
				$content
			);
			$content = str_replace(
				'###REASON###',
				htmlspecialchars($this->reason),
				$content
			);
			$this->setHeaderAndExit($content);
			return; // wichtig für die testcases
		}
		// else, wir leiten weiter.

		$this->redirectTo($url);
		return; // wichtig für die testcases
	}

	private function redirectTo($url)
	{
		$this->setHeaderAndExit(
			$this->getFileAbsFileName($url)
		);
		return; // wichtig für die testcases
	}



	/**
	 *
	 * @return tslib_fe
	 */
	protected function getTsFe()
	{
		return $this->tsfe;
	}

	/**
	 * @param string $additionalPath
	 * @return 	tx_rnbase_configurations
	 */
	protected function &getConfirgurations($additionalPath='')
	{
		if(is_null($this->configurations)) {
			$miscTools = tx_rnbase::makeInstance('tx_mktools_util_miscTools');
			$staticPath = 'EXT:mktools/Configuration/TypoScript/pagenotfoundhandling/setup.txt';
			$this->configurations = $miscTools->getConfirgurations($staticPath, $additionalPath);
		}
		return $this->configurations;
	}

	/**
	 * @param string $contentOrUrl
	 */
	protected function setHeaderAndExit($contentOrUrl)
	{
		$httpStatus = $this->getHttpStatus();
		if ($this->isUri($contentOrUrl)) {
			t3lib_utility_Http::redirect($contentOrUrl, $httpStatus);
		}
		header($httpStatus);
		exit($contentOrUrl);
	}

	private function getFileAbsFileName($filename)
	{
		$filename = trim($filename);
		return substr($filename, 0, 4) == 'EXT:'
			? t3lib_div::getFileAbsFileName($filename)
			: t3lib_div::locationHeaderUrl($filename);
	}
	private function isUri($url)
	{
		return is_array($uI = parse_url($url)) && $uI['scheme'];
	}

	/**
	 * @return string
	 */
	protected function getHttpStatus()
	{
		$httpStatus = $this->header;
		if (empty($httpStatus)) {
			$httpStatus = $this->getConfirgurations()
				->get('pagenotfoundhandling.httpStatus');
		}
		if (empty($httpStatus)) {
			$httpStatus =
				$this->TYPO3_CONF_VARS['FE']['pageNotFound_handling_statheader'];
		}
		return  empty($httpStatus)
			? t3lib_utility_Http::HTTP_STATUS_404 : $httpStatus;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/util/class.tx_mktools_util_PageNotFoundHandling.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/util/class.tx_mktools_util_PageNotFoundHandling.php']);
}

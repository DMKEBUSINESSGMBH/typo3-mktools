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
		$confirgurations = $this->getConfigurations($addTS);

		// Auf zu ignorierende Fehlercodes prüfen.
		$ignorecodes = $confirgurations->get('pagenotfoundhandling.ignorecodes');
		if (t3lib_div::inList($ignorecodes, $this->getTsFe()->pageNotFound)) {
			return;
		}

		// Type und data aus dem TS holen.
		if ($type == 'TYPOSCRIPT') {
			$type = $this->getTypeFromConfiguration();
			$data = $this->getDataFromConfiguration();
			$logPageNotFound =
				$this->getLogPageNotFoundFromConfiguration();
		}

		// Handling von mehrsprachigen 404 Seiten
		$languageCode = $this->getCurrentLanguage();
		if ($languageCode) {
			if ($this->getDataFromConfiguration($languageCode)) {
				$data = $this->getDataFromConfiguration($languageCode);
			}
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
	protected function &getConfigurations($additionalPath='')
	{
		if(is_null($this->configurations)) {
			$miscTools = tx_rnbase::makeInstance('tx_mktools_util_miscTools');
			$staticPath = 'EXT:mktools/Configuration/TypoScript/pagenotfoundhandling/setup.txt';
			$this->configurations = $miscTools->getConfigurations($staticPath, $additionalPath);
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
			$httpStatus = $this->getHttpStatusFromConfiguration();
		}
		if (empty($httpStatus)) {
			$httpStatus =
				$this->TYPO3_CONF_VARS['FE']['pageNotFound_handling_statheader'];
		}
		return  empty($httpStatus)
			? t3lib_utility_Http::HTTP_STATUS_404 : $httpStatus;
	}

	/**
	 * @return string
	 */
	private function getTypeFromConfiguration() {
		return $this->getConfigurationKeyValueByPageNotFoundCode('type');
	}

	/**
	 * @param string $languageCode
	 * @return string
	 */
	private function getDataFromConfiguration($languageCode = false) {
		$typoScriptKey = $languageCode ? $languageCode.'.data' : 'data';
		return $this->getConfigurationKeyValueByPageNotFoundCode($typoScriptKey);
	}

	/**
	 * @return boolean
	 */
	private function getLogPageNotFoundFromConfiguration() {
		return (boolean) $this->getConfigurationKeyValueByPageNotFoundCode('logPageNotFound');
	}

	/**
	 * @return string
	 */
	private function getHttpStatusFromConfiguration() {
		return $this->getConfigurationKeyValueByPageNotFoundCode('httpStatus');
	}

	/**
	 * entweder den default Wert oder den für den spezifischen Code
	 * Beispiel Konfig:
	 * config.tx_mktools.pagenotfoundhandling {
	 		### default
			type = READFILE
			data = /404

			### wenn der Nutzer keine Berechtigungen hat, dann soll er auf die Startseite umgeleitet werden
			pageNotFoundCodes {
				1 {
					type = REDIRECT
					data = /
					httpStatus...
 					logPageNotFound...
				}
				2 {
					data = /
				}
			}
		}
	 */
	private function getConfigurationKeyValueByPageNotFoundCode($typoScriptKey) {
		$pageNotFoundCode = $this->getTsFe()->pageNotFound;
		$configurationKeyValueByPageNotFoundCode = $this->getConfigurations()->get(
			'pagenotfoundhandling.pageNotFoundCodes.' . $pageNotFoundCode . '.' . $typoScriptKey
		);

		return $configurationKeyValueByPageNotFoundCode ? $configurationKeyValueByPageNotFoundCode :
			$this->getConfigurations()->get('pagenotfoundhandling.' . $typoScriptKey);
	}

	/**
	 * Liefert Kürzel der aktuell gesetzten Sprache.
	 * Bei aktivierten realurl kann diese nicht auf dem üblichen Weg ausgewertet
	 * werden. Realurl kann die URL nicht auflösen, da es keine gültige Seite hat.
	 * Demzufolge kann der L-Parameter nicht einfach z.B: über TS abgefragt werden
	 *
	 *  @return string $countrycode
	 */
	private function getCurrentLanguage() {
		if(t3lib_extMgm::isLoaded('realurl')) {
			$realurlConf = array_shift($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']);
			if ($realurlConf &&
				is_array($realurlConf['preVars']) &&
				$realurlConf['pagePath']['languageGetVar']
			) {
				// look for language configuration
				foreach($realurlConf['preVars'] as $conf) {
					if($conf['GETvar'] == $realurlConf['pagePath']['languageGetVar']) {
						foreach($conf['valueMap'] as $countrycode => $value) {
							// we expect a part like "/de/" in requested url
							if(strpos(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'), '/' . $countrycode . '/') !== false) {
								return $countrycode;
							}
						}
					}
				}
			}
		}
		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/util/class.tx_mktools_util_PageNotFoundHandling.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mktools/util/class.tx_mktools_util_PageNotFoundHandling.php']);
}

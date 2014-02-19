<?php
/**
 * 	@package tx_mktools
 *  @subpackage tx_mktools_hook
 *
 *  Copyright notice
 *
 *	Initial Colde:
 *  (c) 2009 John Angel <johnange@gmail.com>
 *
 *  (c) 2011 das Medienkombinat GmbH <kontakt@das-medienkombinat.de>
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

/**
 * Class for the Content Replacer
 * Replaces string patterns from the page content. You can use it to replace URLs for Content Delivery Network (CDN).
 *
 * @author	John Angel <johnange@gmail.com>
 * @author	Michael Wagner <michael.wagner@das-medienkombinat.de>
 * @package	tx_mktools
 * @subpackage tx_mktools_hook
 */
class tx_mktools_hook_ContentReplace {


	/**
	 * Just a wrapper for the main function! It's used for the pageIndexing hook.
	 * @param tslib_fe $obj
	 * @return void The content is passed by reference
	 */
	public function hook_indexContent(&$obj) {
		return $this->doReplace($obj);
	}

	/**
	 * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
	 *
	 * This hook is executed if the page contains *_INT objects! It's called always at the
	 * last hook before the final output. This isn't the case if you are using a
	 * static file cache like nc_staticfilecache.
	 *
	 * @param array $params
	 * @param tslib_fe $obj
	 * @return void The content is passed by reference
	 */
	public function contentPostProcOutput($params, &$obj) {
		// only enter this hook if the page contains COA_INT or USER_INT objects
		if ($obj->isINTincScript()) {
			$this->doReplace($obj);
		}
	}

	/**
	 * Just a wrapper for the main function!  It's used for the contentPostProc-all hook.
	 *
	 * The hook is only executed if the page doesn't contains any *_INT objects. It's called
	 * always if the page wasn't cached or for the first hit!
	 *
	 * @param array $params
	 * @param tslib_fe $obj
	 * @return void The content is passed by reference
	 */
	public function contentPostProcAll($params, &$obj) {
		// only enter this hook if the page doesn't contains any COA_INT or USER_INT objects
		if (!$obj->isINTincScript()) {
			$this->doReplace($obj);
		}
	}

	/**
	 * Search links to resource and replace them with e.g. a CDN-Link
	 *
	 * You must set the Search and Replace patterns via TypoScript.
	 * usage from TypoScript:
	 *   config.tx_mktools.contentreplace {
	 *     search {
	 *       1="typo3temp/pics/
	 *       2="fileadmin/
	 *     }
	 *     replace {
	 *       1="http://mycdn.com/i/
	 *       2="http://mycdn.com/f/
	 *     }
	 *   }
	 *
	 * Don't forget to clear the cache afterwards!
	 *
	 * @TODO: use preg_replace instead of str_replace
	 * @TODO: write tests
	 *
	 * @param tslib_fe $obj
	 * @return void The content is passed by reference
	 */
	protected function doReplace(&$obj){
		// Fetch configuration
		$config = &$obj->config['config']['tx_mktools.']['contentreplace.'];

		// Quit immediately if no replace array setup
		if (!$config
			|| !isset($config['enable']) ||  !intval($config['enable'])
			|| !isset($config['search.']) || empty($config['search.'])
			|| !isset($config['replace.']) || empty($config['replace.'])
			) return;

		// Replace page content
		$obj->content = str_replace($config['search.'], $config['replace.'], $obj->content);

		// Replace additional headers in page
		if (is_array($obj->config['INTincScript_ext']['additionalHeaderData'])) {
			foreach ($obj->config['INTincScript_ext']['additionalHeaderData'] as $key => $value) {
				if ($value) {
					$obj->config['INTincScript_ext']['additionalHeaderData'][$key] = str_replace($config['search.'], $config['replace.'], $value);
				}
			}
		}

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mktools/hooks/class.tx_mktools_hook_ContentReplace.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mktools/hooks/class.tx_mktools_hook_ContentReplace.php']);
}
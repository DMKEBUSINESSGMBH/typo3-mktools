<?php

/**
 * Class for the Content Replacer
 * Replaces string patterns from the page content. You can use it to replace URLs for Content Delivery Network (CDN).
 *
 * @author  John Angel <johnange@gmail.com>
 * @author  Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mktools_hook_ContentReplace
{
    /**
     * Just a wrapper for the main function! It's used for the pageIndexing hook.
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $obj
     */
    public function hook_indexContent(&$obj)
    {
        return $this->doReplace($obj);
    }

    /**
     * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
     *
     * This hook is executed if the page contains *_INT objects! It's called always at the
     * last hook before the final output. This isn't the case if you are using a
     * static file cache like nc_staticfilecache.
     *
     * @param array                                                       $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $obj
     */
    public function contentPostProcOutput($params, &$obj)
    {
        // only enter this hook if the page contains COA_INT or USER_INT objects
        if ($obj->isINTincScript()) {
            $this->doReplace($obj);
        }
    }

    /**
     * Just a wrapper for the main function!    It's used for the contentPostProc-all hook.
     *
     * The hook is only executed if the page doesn't contains any *_INT objects. It's called
     * always if the page wasn't cached or for the first hit!
     *
     * @param array                                                       $params
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $obj
     */
    public function contentPostProcAll($params, &$obj)
    {
        // only enter this hook if the page doesn't contains any COA_INT or USER_INT objects
        if (!$obj->isINTincScript()) {
            $this->doReplace($obj);
        }
    }

    /**
     * Search links to resource and replace them with e.g. a CDN-Link.
     *
     * You must set the Search and Replace patterns via TypoScript.
     * usage from TypoScript:
     *   config.tx_mktools.contentreplace {
     *       search {
     *           1="typo3temp/pics/
     *           2="fileadmin/
     *       }
     *       replace {
     *           1="http://mycdn.com/i/
     *           2="http://mycdn.com/f/
     *       }
     *   }
     *
     * Don't forget to clear the cache afterwards!
     *
     * @TODO: use preg_replace instead of str_replace
     * @TODO: write tests
     *
     * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $obj
     */
    protected function doReplace(&$obj)
    {
        // Fetch configuration
        $config = &$obj->config['config']['tx_mktools.']['contentreplace.'];

        // Quit immediately if no replace array setup
        if (!$config
            || !isset($config['enable']) || !intval($config['enable'])
            || !isset($config['search.']) || empty($config['search.'])
            || !isset($config['replace.']) || empty($config['replace.'])
        ) {
            return;
        }

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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/hooks/class.tx_mktools_hook_ContentReplace.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mktools/hooks/class.tx_mktools_hook_ContentReplace.php'];
}

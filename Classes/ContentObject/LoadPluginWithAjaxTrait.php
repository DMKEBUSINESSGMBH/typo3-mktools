<?php
namespace DMK\Mktools\ContentObject;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

trait LoadPluginWithAjaxTrait
{
    public function render($conf = [])
    {
        if ($this->getContentObjectRenderer()->data['tx_mktools_load_with_ajax'] &&
            !\tx_rnbase_parameters::getPostOrGetParameter('mktoolsAjaxRequest')
            ) {
                // we need a link per element so caching (chash) works correct in the ajax
                // page type. Otherwise it's not possible to render more than one element
                // per page
                $configuration = \tx_rnbase::makeInstance('Tx_Rnbase_Configuration_Processor');
                $configuration->init($GLOBALS['TSFE']->tmpl->setup, $this->getContentObjectRenderer(), 'mktools', 'mktools');
                $link = $configuration
                ->createLink()
                ->initByTS(
                    $configuration,
                    $this->urlTypoScriptConfigurationPath,
                    ['::contentid' => $this->getContentObjectRenderer()->data['uid']]
                    )
                    ->makeUrl();
                    
                    // We only need dummy content which indicates to start the ajax load.
                    // The rest is handled with JS and the surrounding div with the content id.
                    // @see AjaxContent.js
                    $content = '<a class="ajax-links-autoload ajax-no-history" href="' . $link . '"></a>';
            } else {
                $content = parent::render($conf);
            }
            
            return $content;
    }
    
    /**
     * Getter for current ContentObjectRenderer
     * Taken from TYPO3\CMS\Frontend\ContentObject\AbstractContentObject
     * to support TYPO3 7.6
     * 
     * @TODO remove if TYPO3 7.6 isn't supported anymore
     *
     * @return ContentObjectRenderer
     */
    public function getContentObjectRenderer()
    {
        return $this->cObj;
    }
}
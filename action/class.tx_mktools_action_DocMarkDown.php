<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2015 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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
 ***************************************************************/
tx_rnbase::load('tx_mktools_action_ShowTemplate');

/**
 * Controller for markdown documentations.
 *
 * USAGE: see mktools/Configuration/TypoScript/action/setup.txt
 *
 * @package TYPO3
 * @subpackage tx_mktools
 */
class tx_mktools_action_DocMarkDown extends tx_mktools_action_ShowTemplate
{

    /**
     * Returns the data to render for the view
     *
     * @return array
     */
    protected function getData()
    {
        $content = '';
        if ($this->auth()) {
            // get real filenames!
            tx_rnbase::load('tx_rnbase_util_Templates');
            $tmpl = tx_rnbase_util_Templates::getTSTemplate();
            foreach ($this->getFiles() as $file) {
                $file = $tmpl->getFileName($file);
                $rawContent = tx_rnbase_util_Network::getUrl($file);
                $content .= $this->parseContent($rawContent);
            }
        } else {
            $content = '<h1>ACCESS DENIED</h1>';
        }

        return array(
            'content' => $content
        );
    }

    /**
     * check the user for auth rights
     *
     * @return bool
     */
    protected function auth()
    {
        tx_rnbase::load('tx_mklib_util_MiscTools');
        tx_mklib_util_MiscTools::enableHttpAuthForCgi();

        $auth = $this->getConfigurations()->get(
            $this->getConfId() . 'auth.crypt.'
        );

        // zugriff auf die Doku nur in bestimmten fällen
        $hasAccess =
            // be user is loged in
            $GLOBALS['TSFE']->beUserLogin
            // check crypt auth users from ts
            || (
                isset($auth[$_SERVER['PHP_AUTH_USER']])
                && $auth[$_SERVER['PHP_AUTH_USER']] === crypt($_SERVER['PHP_AUTH_PW'], $auth[$_SERVER['PHP_AUTH_USER']])
            );

        if (!$hasAccess) {
            header('WWW-Authenticate: Basic realm="Dokumentation"');
            header('HTTP/1.0 401 Unauthorized');
        }

        return $hasAccess;
    }

    /**
     * returns the markdown parser.
     *
     * @return Parsedown
     */
    protected function getParser()
    {
        if ($this->parser === null) {
            tx_rnbase::load('tx_mktools_util_Composer');
            tx_mktools_util_Composer::autoload();
            $this->parser = new ParsedownExtra();
            $this->parser->setMarkupEscaped(false);
            $this->parser->setBreaksEnabled(false);
        }

        return $this->parser;
    }

    /**
     * oparses md content into html
     *
     * @param string $content
     * @return string
     */
    protected function parseContent($content)
    {
        return $this->getParser()->text($content);
    }

    /**
     * the files to parse.
     *
     * @return array
     */
    protected function getFiles()
    {
        $configurations = $this->getConfigurations();
        $confId = $this->getConfId() . 'files';

        $fiels = $configurations->get($confId . '.');

        return array_merge(
            $configurations->getExploded($confId),
            is_array($fiels) ? $fiels : array()
        );
    }

    /**
     * Gibt den Name des zugehörigen Templates zurück
     *
     * @return string
     */
    public function getTemplateName()
    {
        return 'docmarkdown';
    }
}

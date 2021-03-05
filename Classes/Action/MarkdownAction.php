<?php

namespace DMK\Mktools\Action;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mktools\Utility\ComposerUtility;
use Parsedown;
use ParsedownExtra;
use tx_rnbase_util_Files as FileUtil;
use tx_rnbase_util_Network as NetworkUtil;

/**
 * Controller for markdown documentations.
 *
 * USAGE: see mktools/Configuration/TypoScript/action/setup.txt
 */
class MarkdownAction extends ShowTemplateAction
{
    /**
     * @var ParsedownExtra
     */
    private $parser;

    /**
     * Returns the data to render for the view.
     *
     * @return array
     */
    protected function getData()
    {
        if (false === $this->auth()) {
            return ['content' => '<h1>ACCESS DENIED</h1>'];
        }

        $content = '';

        foreach ($this->getFiles() as $file) {
            $file = FileUtil::getFileName($file);
            $rawContent = NetworkUtil::getUrl($file);
            $content .= $this->parseContent($rawContent);
        }

        return [
            'content' => $content,
        ];
    }

    /**
     * check the user for auth rights.
     *
     * @return bool
     */
    protected function auth()
    {
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
                explode(
                    ':',
                    base64_decode(
                        substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)
                    )
                );
        }

        $auth = $this->getConfigurations()->get(
            $this->getConfId().'auth.crypt.'
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
        if (null === $this->parser) {
            ComposerUtility::autoload();
            $this->parser = new ParsedownExtra();
            $this->parser->setMarkupEscaped(false);
            $this->parser->setBreaksEnabled(false);
        }

        return $this->parser;
    }

    /**
     * Converts markdown to HTML.
     *
     * @param string $content
     *
     * @return string
     */
    protected function parseContent($content)
    {
        return $this->getParser()->text($content);
    }

    /**
     * Returns a list which needs to be parsed.
     *
     * @return array
     */
    protected function getFiles()
    {
        $configurations = $this->getConfigurations();
        $confId = $this->getConfId().'files';

        $fiels = $configurations->get($confId.'.');

        return array_merge(
            $configurations->getExploded($confId),
            is_array($fiels) ? $fiels : []
        );
    }

    /**
     * Gibt den Name des zugehörigen Templates zurück.
     *
     * @return string
     */
    public function getTemplateName()
    {
        return 'docmarkdown';
    }
}

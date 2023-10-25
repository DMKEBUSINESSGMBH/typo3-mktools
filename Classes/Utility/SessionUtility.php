<?php

namespace DMK\Mktools\Utility;

use Sys25\RnBase\Frontend\Request\Parameters;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * Util für session handling.
 *
 * This methods are taken from the great t3users extension.
 * Using this only if t3users not aviable.
 *
 * @see tx_t3users_services_feuser
 *
 * @author Michael Wagner
 */
class SessionUtility
{
    /**
     * Set a session value.
     * The value is stored in TYPO3 session storage.
     *
     * tx_t3users_util_ServiceRegistry::getFeUserService()->setSessionValue()
     *
     * @see tx_t3users_services_feuser::setSessionValue
     *
     * @param string $key
     * @param mixed  $value
     * @param string $extKey
     */
    public static function setSessionValue($key, $value, $extKey = 'mktools')
    {
        $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
        $vars[$key] = &$value;
        $GLOBALS['TSFE']->fe_user->setKey('ses', $extKey, $vars);
    }

    /**
     * Returns a session value.
     *
     * tx_t3users_util_ServiceRegistry::getFeUserService()->getSessionValue()
     *
     * @see tx_t3users_services_feuser::getSessionValue
     *
     * @param string $key    key of session value
     * @param string $extKey optional
     *
     * @return mixed
     */
    public static function getSessionValue($key, $extKey = 'mktools')
    {
        $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);

        return $vars[$key];
    }

    /**
     * Removes a session value.
     *
     * tx_t3users_util_ServiceRegistry::getFeUserService()->removeSessionValue()
     *
     * @see tx_t3users_services_feuser::removeSessionValue
     *
     * @param string $key    key of session value
     * @param string $extKey optional
     */
    public static function removeSessionValue($key, $extKey = 'mktools')
    {
        $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
        unset($vars[$key]);
        $GLOBALS['TSFE']->fe_user->setKey('ses', $extKey, $vars);
    }

    /**
     * Saves the session data to database.
     */
    public static function storeSessionData()
    {
        $GLOBALS['TSFE']->fe_user->storeSessionData();
    }

    /**
     * Diese Methode funktioniert nur wenn der aktuelle Request kein POST
     * Request ist. Wenn es ein POST Request ist, dann einfach vor dem
     * absenden mit JS einen Cookie setzen und ggf. noch checkedIfCookiesAreActivated=1
     * in den Parametern übermitteln. Oder das Formular wird gar nicht erst
     * gerendered wenn diese Methode FALSE liefert.
     *
     * @return bool
     */
    public static function areCookiesActivated()
    {
        if (!empty($_COOKIE) || Parameters::getPostOrGetParameter('checkedIfCookiesAreActivated')) {
            $cookiesActivated = !empty($_COOKIE);
        } else {
            // @TODO diesen Abschnitt testen, aber wie (vor allem auf CLI)?
            // Wir versuchen selbst einen Cookie zu setzen.
            setcookie('cookiesActivated', 1, time() + 3600);
            // Wir setzen einen Parameter für den Reload,
            // um einen Infinite Redirect zu verhindern
            // falls keine Cookies erlaubt sind.
            $parsedUrl = parse_url(GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT'));
            $checkedIfCookiesAreActivatedParameter = ($parsedUrl['query'] ? '&' : '?').'checkedIfCookiesAreActivated=1';
            // Und machen einen Reload um zu sehen ob Cookies gesetzt werden konnten.
            header(
                'Location: /'.
                GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT').
                $checkedIfCookiesAreActivatedParameter
            );
            exit;
        }

        return $cookiesActivated;
    }
}

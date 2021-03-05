<?php

namespace DMK\Mktools\Session;

/***************************************************************
 *  Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mktools\Exception\RuntimeException;
use DMK\Mktools\Utility\SessionUtility;

/**
 * Flash message utility.
 *
 * @method static FlashMessageStorage addPrimary() addPrimary($message)
 * @method static FlashMessageStorage addSuccess() addSuccess($message)
 * @method static FlashMessageStorage addInfo() addInfo($message)
 * @method static FlashMessageStorage addWarning() addWarning($message)
 * @method static FlashMessageStorage addDanger() addDanger($message)
 *
 * @author Michael Wagner
 */
class FlashMessageStorage
{
    const LEVEL_PRIMARY = 'primary';
    const LEVEL_SUCCESS = 'success';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_DANGER = 'danger';

    /**
     * List of Messages from the last Request.
     *
     * @var \ArrayObject
     */
    private $prevMessages;

    /**
     * List of Messages for the next Request.
     *
     * @var \ArrayObject
     */
    private $nextMessages;

    /**
     * Creates the flashmessage singelton.
     *
     * @return self
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = \tx_rnbase::makeInstance(get_called_class())->load();
        }

        return $instance;
    }

    /**
     * Constructor.
     *
     * Loads all Messages from the Session to output on current request.
     */
    public function __construct()
    {
        $this->prevMessages = new \ArrayObject();
        $this->nextMessages = new \ArrayObject();
    }

    /**
     * Loads the messages from the last request and clears the session.
     *
     * @return self
     */
    public function load()
    {
        // load messages from last request
        $prevMessages = SessionUtility::getSessionValue('flash_mesages', 'mktools');
        $prevMessages = unserialize($prevMessages);

        $this->prevMessages = new \ArrayObject(
            empty($prevMessages) ? [] : $prevMessages
        );

        // remove the current mesage stack from session
        SessionUtility::removeSessionValue(
            'flash_mesages',
            'mktools'
        );

        return $this;
    }

    /**
     * Saves the messages for the next request.
     *
     * @return self
     */
    public function save()
    {
        SessionUtility::setSessionValue(
            'flash_mesages',
            serialize($this->nextMessages->getArrayCopy()),
            'mktools'
        );

        SessionUtility::storeSessionData();

        return $this;
    }

    /**
     * Keeps the messages from the last request and addt for the next.
     *
     * @return self
     */
    public function keep()
    {
        $messages = [];
        foreach ($this->prevMessages as $message) {
            $messages[] = $message;
        }
        foreach ($this->nextMessages as $message) {
            $messages[] = $message;
        }

        // cleanup prev messages
        $this->prevMessages->exchangeArray([]);
        // set next messages
        $this->nextMessages->exchangeArray($messages);

        return $this->save();
    }

    /**
     * Appends a new message to for the next request.
     *
     * @param string $message
     * @param string $level
     * @param mixed  $data
     *
     * @return self
     */
    private function addMessage($message, $level, $data = null)
    {
        $message = \tx_rnbase::makeInstance(
            'Tx_Rnbase_Domain_Model_Base',
            [
                'level' => $level,
                'message' => $message,
                'data' => $data,
            ]
        );
        $this->nextMessages->append($message);

        return $this->save();
    }

    /**
     * Returns the list of messages for this and the next request.
     *
     * @return \ArrayObject
     */
    public function getMessages()
    {
        return $this->prevMessages;
    }

    /**
     * Checks for a static add message count for the allowed levels.
     *
     * @param string $method
     * @param array  $args
     *
     * @throws RuntimeException If level or method does not exists
     *
     * @return self
     */
    public static function __callStatic($method, $args)
    {
        if (0 !== strpos($method, 'add') || $method[3] !== strtoupper($method[3])) {
            throw new RuntimeException(sprintf('Method "%s::%s()" does not exists', static::class, $method));
        }

        $level = substr($method, 3);
        $const = 'LEVEL_'.strtoupper($level);

        if (defined('self::'.$const)) {
            return self::getInstance()->addMessage($args[0], constant('self::'.$const));
        }
    }
}

<?php

/**
 * tx_mktools_hook_GeneralUtility.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mktools_hook_GeneralUtility
{
    /**
     * @var string
     */
    private $systemLogConfigurationBackup = '';

    /**
     * wenn die nachricht nicht schon wieder geloggt werden soll
     * leeren wir $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] um
     * das zu verhindern.
     *
     * @param array $parameters
     *
     * @todo refactoring to prevent the flooding in a more
     * sophisticated way
     */
    public function preventSystemLogFlood(array $parameters)
    {
        $this->handleSystemLogConfigurationBackup();

        /* @var $lockUtility tx_rnbase_util_Lock */
        $lockUtility = $this->getLockUtility($parameters);

        if ($lockUtility->isLocked()) {
            // prevent logging
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = '';
        } else {
            $lockUtility->lockProcess();
        }
    }

    private function handleSystemLogConfigurationBackup()
    {
        // initial die systemLog Konfiguration sichern um diese ggf.
        // wieder zurück schreiben zu können
        if (!$this->systemLogConfigurationBackup) {
            $this->systemLogConfigurationBackup =
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'];
        } else {
            // wir schreiben die gesicherte Konfig zurück falls wir
            // vorher $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] geleert haben
            // um das logging zu verhindern
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] =
                $this->systemLogConfigurationBackup;
        }
    }

    /**
     * @param array $parameters
     *
     * @return tx_rnbase_util_Lock
     */
    protected function getLockUtility(array $parameters)
    {
        return tx_rnbase_util_Lock::getInstance(
            md5(
                $parameters['msg'].$parameters['extKey'].$parameters['severity']
            ),
            tx_mktools_util_miscTools::getSystemLogLockThreshold()
        );
    }
}

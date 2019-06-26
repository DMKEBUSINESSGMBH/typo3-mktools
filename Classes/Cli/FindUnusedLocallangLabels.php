<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
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
 * Tx_Mktools_Cli_FindUnusedLocallangLabels.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tx_Mktools_Cli_FindUnusedLocallangLabels extends Tx_Rnbase_CommandLine_Controller
{
    /**
     * @var array
     */
    protected $labelsUsage = array();

    /**
     * Constructor.
     *
     * @todo Define visibility
     */
    public function __construct()
    {
        // Running parent class constructor
        parent::__construct();
        // Adding options to help archive:
        $this->cli_options[] = array('--locallangFile file', 'absolute file path', 'can be passed multiple times');
        $this->cli_options[] = array('--searchFolders folders', 'comma separated list of absolute folder paths to search in', 'can be passed multiple times');
        // Setting help texts:
        $this->cli_help['name'] = 'find unused locallang labels';
        $this->cli_help['synopsis'] = 'toolkey ###OPTIONS###';
        $this->cli_help['description'] = 'Searches recursively the folders given with all the labels found in the locallang file.';
        $this->cli_help['examples'] = '/.../cli_dispatch.phpsh --locallangFile=/some/file/path --foldersToSearchIn=/some/folders,/some/other/folders';
        $this->cli_help['author'] = 'Hannes Bochmann, (c) 2015';
    }

    public function showUnusedLocallangLabels()
    {
        if (!isset($this->cli_args['--locallangFile']) || !isset($this->cli_args['--searchFolders'])) {
            $this->cli_help();
        } else {
            $languageService = tx_rnbase::makeInstance('TYPO3\\CMS\\Lang\\LanguageService');
            foreach ($this->cli_args['--locallangFile'] as $locallangFile) {
                $locallangFile = tx_rnbase_util_Files::getFileAbsFileName($locallangFile);
                $labels = $languageService->includeLLFile($locallangFile, false);
                foreach ($this->cli_args['--searchFolders'] as $folders) {
                    foreach (tx_rnbase_util_Strings::trimExplode(',', $folders) as $folder) {
                        $this->getLabelsUsageInFolder(
                            $labels,
                            tx_rnbase_util_Files::getFileAbsFileName($folder),
                            array($locallangFile)
                        );
                    }
                }
            }

            foreach ($this->labelsUsage as $labelKey => $usage) {
                if (!$usage) {
                    $this->cli_echo($labelKey." wird nicht verwendet\n");
                }
            }
        }
    }

    /**
     * @param array  $labels
     * @param string $folder
     * @param array  $filesToIgnore
     */
    protected function getLabelsUsageInFolder(array $labels, $folder, array $filesToIgnore)
    {
        foreach (scandir($folder) as $file) {
            $absoluteFilePath = $folder.'/'.$file;
            if (!is_dir($absoluteFilePath) && !in_array($absoluteFilePath, $filesToIgnore)) {
                $this->getLabelsUsageInFile($labels, $absoluteFilePath, $filesToIgnore);
            }
            if (is_dir($absoluteFilePath) && '.' != $file && '..' != $file) {
                $this->getLabelsUsageInFolder($labels, $absoluteFilePath, $filesToIgnore);
            }
        }
    }

    /**
     * @param array  $labels
     * @param string $file
     */
    protected function getLabelsUsageInFile(array $labels, $file)
    {
        $fileContents = strtolower(file_get_contents($file));
        foreach ($labels as $labelsByLanguage) {
            foreach ($labelsByLanguage as $labelKey => $label) {
                if (!isset($this->labelsUsage[$labelKey])) {
                    $this->labelsUsage[$labelKey] = 0;
                }
                if (false !== strpos($fileContents, strtolower($labelKey))) {
                    ++$this->labelsUsage[$labelKey];
                }
            }
        }
    }
}

if (tx_rnbase_util_TYPO3::isCliMode() && !defined('MKTOOLS_TESTRUN')) {
    $cleanerObj = tx_rnbase::makeInstance('Tx_Mktools_Cli_FindUnusedLocallangLabels');
    $cleanerObj->showUnusedLocallangLabels();
}

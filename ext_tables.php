<?php
if (!defined ('TYPO3_MODE')) { die ('Access denied.'); }

// default TS für den content replacer
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');

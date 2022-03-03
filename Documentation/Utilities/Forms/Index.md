form Extension
==============

mktools liefert einen Command, um die überschriebenen E-Mail-Finisher Konfigurationen in Plugin zu migrieren.
In den .yaml Dateien muss man sich selbst darum kümmern bzw. diese im Form Editor öffnen und speichern.
Dann wird die Konfiguration in der Formdefinition migriert. In den Plugins kann dieser Command gneommen werden.
Mehr Infos dazu u.a. hier: https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Deprecation-80420-EmailFinisherSingleAddressOptions.html

Command zum initialen befüllen:

~~~~ {.sourceCode .sh
 bin/typo3 mktools:slug-creator -t tx_myext_table -f slug_field
~~~~

Command zum migrieren von realurl:

~~~~ {.sourceCode .sh
 bin/typo3 mktools:migrate-realurl-alias-to-slug -t tx_myext_table -f slug_field
~~~~

Utility zum initialen befüllen:
~~~~ {.sourceCode .php
 \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\DMK\Mktools\Utility\SlugUtility::class, $table, $field)->populateEmptySlugsInTable();
~~~~

Utility zum migrieren von realurl:
~~~~ {.sourceCode .php
 \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\DMK\Mktools\Utility\SlugUtility::class, $table, $field)->migrateRealurlAliasToSlug();
~~~~

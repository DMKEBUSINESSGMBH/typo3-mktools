form Extension
==============

mktools liefert einen Command, um die überschriebenen E-Mail-Finisher Konfigurationen in Plugin zu migrieren.
In den .yaml Dateien muss man sich selbst darum kümmern bzw. diese im Form Editor öffnen und speichern.
Dann wird die Konfiguration in der Formdefinition migriert. In den Plugins kann dieser Command gneommen werden.
Mehr Infos dazu u.a. hier: https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Deprecation-80420-EmailFinisherSingleAddressOptions.html

~~~~ {.sourceCode .sh
 bin/typo3 mktools:migrate-form-finishers
~~~~

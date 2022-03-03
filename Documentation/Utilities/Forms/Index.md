form Extension
==============

mktools liefert einen Command, um die überschriebenen E-Mail-Finisher Konfigurationen in Plugins zu migrieren.
In den .yaml Dateien muss man sich selbst darum kümmern bzw. diese im Form Editor öffnen und speichern.
Dann wird die Konfiguration in der Formdefinition migriert. Die Plugins bleiben davon aber unberührt, womit die bisher eingetragenen
Werte im BE nicht mehr ausgelesen und damit auch nicht mehr bearbeitet werden können. In den Plugins kann dieser Command genommen werden, um
die Finisher Konfiguration zu migrieren.
Mehr Infos dazu u.a. hier: https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.0/Deprecation-80420-EmailFinisherSingleAddressOptions.html

~~~~ {.sourceCode .sh
 bin/typo3 mktools:migrate-form-finishers
~~~~
s

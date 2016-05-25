TCA Overrides
=============

Das laden von TCA Erweiterungen hat sich ab TYPO3 6.2 etwas geändert. Wenn z.B. die TCA einer Extension erweitert wird, die erst nach der Extension geladen wird (z.B. weil diese nicht in den dependencies auftaucht) und diese zu erweiternde Extension die TCA Dateien nicht so vorliegen wie es TYPO3 wünscht, werden die Erweiterungen nicht geladen.

Dafür bietet TYPO3 aber einen Hook, den wir hier nutzen. Damit werden alle TCA Erweiterungen definitv korrekt geladen.

Dazu muss einfach im Extension Manager mktools gewählt werden. Dort gibt es die Möglichkeit in der Option modules.tcaPostProcessingExtensions die gewünschten Extensions kommasepariert zu hinterlegen werden. Also zum Beispiel "myext,mktools". In den angegeben Extensions müssen dann im Ordner Configuration/TCA/Overrides für jede Tabelle eine Datei mit dem Namen der Tabelle liegen. Das könnte so aussehen:

~~~~ {.sourceCode .html}
myext/Configuration/TCA/Overrides/tt_news.php
~~~~

Per default werden die TCA Erweiterungen von mktools geladen damit die FAL Felder für tt\_news und cal, wenn gewünscht, korrekt geladen werden.

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _tca-overrides:

TCA Overrides
=============

Das laden von TCA Erweiterungen hat sich ab TYPO3 6.2 etwas geändert. Wenn z.B. die
TCA einer Extension erweitert wird, die erst nach der Extension geladen wird (z.B.
weil diese nicht in den dependencies auftaucht) und diese zu erweiternde Extension
die TCA Dateien nicht so vorliegen wie es TYPO3 wünscht, werden die Erweiterungen nicht
geladen.

Dafür bietet TYPO3 aber einen Hook, den wir hier nutzen. Damit werden alle TCA Erweiterungen definitv korrekt
geladen.

Dazu muss einfach im Extension Manager die gewünschten Extensions kommasepariert in
modules.tcaPostProcessingExtensions hinterlegt werden.
In den angegeben Extensions müssen dann im Ordner Configuration/TCA/Overrides für jede
Tabelle eine Datei mit dem Namen der Tabelle liegen.

Per default werden die Erweiterungen von mktools geladen damit die FAL Felder
für tt_news und cal, wenn gewünscht, korrekt geladen werden.
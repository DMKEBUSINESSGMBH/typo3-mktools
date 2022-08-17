TCAMigrations
=====

Das group Feld mit dem internal_type file gibt es nicht mehr. Zum einen 
muss die Verknüpfung zu den Dateien migriert werden und zum anderen die Ausgabe
angepasst werden. Für den ersten Teil bietet mktools ein Command um alle Referenzen
zu migrieren. Am Ende wird auch angezeigt wie die TCA danach geändert werden
muss. Wichtig ist, dass es in TYPO3 die passenden Storages für die Uploadfolder der group Felder gibt und dass die group Felder in der TCA vorhanden sind. Für gewöhnlich
dürfte das der uploads Ordner sein. 
Um die Anpassung der Ausgabe muss sich explizit gekümmert werden.

Command für die Migration:

~~~~ {.sourceCode .sh
 bin/typo3 mktools:migrate-tca-file-groups-to-fal
~~~~

Slugs
=====

mktools liefert einen Command und eine Utility, um Slugs in einer beliebigen Tabelle zu erzeugen. 
Das passiert nur wenn das Feld leer ist. Und es wird die TCA Konfiguration für das Slugfeld verwendet.
Das ganze kann auch programmatisch genutzt werden, um das z.B. nach Importen ausführen zu können.

Command:

~~~~ {.sourceCode .sh
 bin/typo3 mktools:slug-creator -t tx_myext_table -f slug_field
~~~~

Utility:
~~~~ {.sourceCode .php
 DMK\Mktools\Utility\SlugUtility::populateEmptySlugsInTable($table, $field);
~~~~

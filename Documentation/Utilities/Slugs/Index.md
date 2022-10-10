Slugs
=====

mktools liefert 2 Commands und eine Utility, um Slugs in einer beliebigen Tabelle zu erzeugen 
bzw. diese aus den realurl Aliasen zu migrieren. 
Das passiert nur wenn das Feld leer ist. Und es wird die TCA Konfiguration für das Slugfeld verwendet.
Das ganze kann auch programmatisch genutzt werden, um das z.B. nach Importen ausführen zu können.
D.h. also die Voraussetzung ist, dass es das Slugfeld bereits in der Tabelle gibt.

Command zum initialen befüllen:

~~~~ {.sourceCode .sh
 bin/typo3 mktools:slug-creator -t tx_myext_table -f slug_field
~~~~

Command zum migrieren von realurl (bei Datensätzen ohne alias wird der Slug einfach generiert):

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

Routing
=======

Mit dem StaticRangeMapper kann man schnell an die Grenzen kommen,
wenn es einen Pagebrowser und z.B. noch Parameter für ein Archiv gibt.
TYPO3 erlaubt von Haus aus max 10000 mögliche Kombinationen, was schon
nach ein paar Seiten im PageBrowser erreicht ist. Daher gibt es nun den
StaticNumberRangeMapper, der sich genau wie das Original verhält. Aber es
sind nur Zahlen als Werte erlaubt und der Mapper wird von TYPO3 nicht berücksichtigt,
wenn das Limit der Kombinationen ermittelt wird.

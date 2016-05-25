translatable postVarSets
========================

Aktuell ist es mit RealURL nicht möglich, Parameternamen in Verbindung mit der für den Link relevanten Sprache zu übersetzen. Mit der XCLASS für realurl und einer zusätzlichen Konfiguration wird dies nun möglich. Dazu muss die Extension-Konfiguration "realUrlXclass" aktiviert werden. Beispielkonfiguration:

~~~~ {.sourceCode .php}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['_DEFAULT']['postVarSets'] => array(
   'category' => array(
      array(
         'GETvar' => 'mktools[cat]',
         'language' => array('ids' => '0'), // language en
         'noMatch' => 'null',
      ),
   ),
   'kategorie' => array(
      array(
         'GETvar' => 'mktools[cat]',
         'language' => array('ids' => '1'), // language de
         'noMatch' => 'null',
      ),
   ),
   'categorie' => array(
      array(
         'GETvar' => 'mktools[cat]',
         'language' => array('ids' => '2'), // language nl
         'noMatch' => 'null',
      ),
   ),
   'item' => array(
      array(
         'GETvar' => 'mktools[item]',
         'language' => '0,2', // language en & nl
         'noMatch' => 'null',
      ),
   ),
   'element' => array(
      array(
         'GETvar' => 'mktools[item]',
         'language' => '1', // language de
         'noMatch' => 'null',
      ),
   ),
);
~~~~

Der neue Abschnit "language" wird von mktools ausgewertet und an realurl weitergegeben. Damit werden folgende URL's erzeugt:

-   ?l=0&mktools[cat]=foo&mktools[item]=bar \> /en/category/foo/item/bar.html
-   ?l=1&mktools[cat]=foo&mktools[item]=bar \> /de/kategorie/foo/element/bar.html
-   ?l=2&mktools[cat]=foo&mktools[item]=bar \> /nl/categorie/foo/item/bar.html


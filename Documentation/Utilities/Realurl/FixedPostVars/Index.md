fixedPostVars
=============

Mit diesen lassen sich Parametername aus der URL entfernen. So kann z.B. aus /artikel/Artikel/\$newsname.html /artikel/\$newsname.html werden. Siehe dazu <http://www.verkon.de/agentur/neues/artikel/url_pfadsegmente_in_typo3_mit_realurl_entfernen/>

Das Problem ist dass im BE ständig neue Seite hinzukommen können, die dann erst für realurl konfiguriert werden müssen. Dazu bietet mktools aber eine Möglichkeit damit diese Konfiguration direkt generiert wird wenn eine Seite für fixedPostVars konfiguriert wird. Folgendes Vorgehen ist notwendig:

-   in Extension Konfig unter realurl wählen dass die TCA geladen werden soll
-   in Extension Konfig unter realurl die realurl Konfigurationsdatei angeben (absoluter Pfad, relativer Pfad oder einer mit "EXT:..." am Anfang)
-   in Extension Konfig unter realurl das Template für die Konfigurationsdatei angeben (absoluter Pfad, relativer Pfad oder einer mit "EXT:..." am Anfang)
-   in der Listenansicht einen FixedPostVar Typen anlegen
-   mindestens einer Seite einen FixedPostVar zuweisen !!
-   in der realurl Templatedatei die allgemeine Konfiguration für diesen Parameter setzen
-   in der realurl Templatedatei den Marker "\#\#\#FIXEDPOSTVARPAGES\#\#\#" an geeigneter Stelle einfügen
-   den mktools Scheduler für die Generierung der Konfigurationsdatei anlegen (sollte jede Minute laufen da die Konfigurationsdatei nur neu generiert wird, wenn sich eine Seite mit fixedPostVar Typ geändert/angelegt hat oder ein fixedPostVar Typ geändert wurde)

Beispiel für Template:

~~~~ {.sourceCode .php}
<?php
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
   'fixedPostVars' => array(
      'Artikel' => array(
         array(
            'GETvar' => 'tx_ttnews[tt_news]',
            'lookUpTable' => array(
               'table' => 'tt_news',
               'id_field' => 'uid',
               'alias_field' => 'title',
               'addWhereClause' => ' AND NOT deleted',
               'useUniqueCache' => 1,
               'useUniqueCache_conf' => array(
                  'strtolower' => 1,
                  'spaceCharacter' => '-',
               ),
            ),
         ),
      ),
      //wird durch mktools ersetzt
      ###FIXEDPOSTVARPAGES###
   ),
);
?>
~~~~

Nun wird der fixedPostVar Typ im Backend angelegt mit den Namen "Newsdetailseite" und dem Identifier "Artikel" und für das Beispiel der Seite mit Uid 123 vergeben.

Diese Konfiguration wird dann erzeugt:

~~~~ {.sourceCode .php}
<?php
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
   'fixedPostVars' => array(
      'Artikel' => array(
         array(
            'GETvar' => 'tx_ttnews[tt_news]',
            'lookUpTable' => array(
               'table' => 'tt_news',
               'id_field' => 'uid',
               'alias_field' => 'title',
               'addWhereClause' => ' AND NOT deleted',
               'useUniqueCache' => 1,
               'useUniqueCache_conf' => array(
                  'strtolower' => 1,
                  'spaceCharacter' => '-',
               ),
            ),
         ),
      ),
      123 => 'Artikel'
   ),
);
?>
~~~~

Hinweis
-------

Die Konfiguration wird direkt serialisiert gespeichert.

Wenn die URLs noch nicht verkürzt dargestellt werden, dann sollte das Leeren des Cache genügen.

Es gilt noch darauf zu achten dass alte URLs mit den fixedPostVars nicht mehr erreichbar sind. Bei obigen Beispiel ist dann z.B. /einzelartikel/Artikel/\$newname.html nicht mehr erreichbar. Dass lässt sich aber durch htaccess umleiten:

~~~~ {.sourceCode .html}
RewriteRule ^aktuell/einzelartikel/Artikel/(.*)$ http://mydomain.tld/aktuell/einzelartikel/$1 [R=301,L]
~~~~

Wenn die Seite bisher direkt über Parameter angesteuert wurde und jetzt eine sprechende URL besitzt, dann werden die alten URLs mit Parameter nicht mehr funktionieren. Außer in der Konfig wird 'optional' =\> true gesetzt.

~~~~ {.sourceCode .php}
<?php
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
   'fixedPostVars' => array(
      'Artikel' => array(
         array(
            'GETvar' => 'tx_ttnews[tt_news]',
            'lookUpTable' => array(
               'table' => 'tt_news',
               'id_field' => 'uid',
               'alias_field' => 'title',
               'addWhereClause' => ' AND NOT deleted',
               'useUniqueCache' => 1,
               'useUniqueCache_conf' => array(
                  'strtolower' => 1,
                  'spaceCharacter' => '-',
               ),
            ),
            'optional' => true
         ),
      ),
      //wird durch mktools ersetzt
      ###FIXEDPOSTVARPAGES###
   ),
);
?>
~~~~

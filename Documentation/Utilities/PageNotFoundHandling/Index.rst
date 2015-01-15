.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _page-not-found-handling:

Page Not Found Handling
=======================

Muss im Extension-Manager aktiviert werden. Damit wird die Typo3-Fehlerbehandlung über $TYPO3_CONF_VARS['FE']['pageNotFound_handling'] erweitert.

Standard wird das verhalten insofern verändert, das bei den Fehlercodes 1 (ID was not an accessible page) oder 2 (Subsection was found and not accessible) der Handler übersprungen und Typo3 normal ausgeführt wird.

**WICHTIG**: Wenn RealURL genutzt wird, sollte die Option postVarSet_failureMode => redirect_goodUpperDir nicht aktiv sein!

**Weiter ist es möglich spezielle Requests anzugeben:**

READFILE
--------

Bei READFILE wird der Inhalt der Seite mit dem in $TYPO3_CONF_VARS['FE']['pageNotFound_handling_statheader'] angegebenem Header ausgegeben. Im erhaltenem Content werden die Marker ###CURRENT_URL### und ###REASON### entsprechend ersetzt. Liefert die angegebene Datei keinen Inhalt, wird REDIRECT mit der Datei ausgeführt.

.. code-block:: php

   $TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'MKTOOLS_READFILE:http://project.dmknet.de/notfound.html'
   
REDIRECT
--------

Bei REDIRECT wird einfach ein Redirect auf die Datei ausgeführt.

.. code-block:: php

   $TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'MKTOOLS_REDIRECT:http://project.dmknet.de/notfound.html'
   
MKTOOLS_TYPOSCRIPT
------------------

Die wohl interessanteste Möglichkeit anzugeben, was genau getan werden soll, da hier auch Conditions und somit eine Domainabhängige Konfiguration möglich ist.

.. code-block:: php

   $TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = 'MKTOOLS_TYPOSCRIPT:EXT:mkextension/Configuration/TypoScript/pagenotfoundhandling.tss'
   
**Basiskonfigurationsmöglichkeit**:

Der Handler kann mit verschiedenen Fehlercodes aufgerufen werden. Mit ignorecodes kann definiert werden, welche Fehlercodes ignoriert werden sollen. Es wird dann kein Fehler ausgegeben, Typo3 läuft einfach Weiter.

.. code-block:: ts

   # Mögliche Fehlercodes
   # 0 > Unknown
   # 1 > ID was not an accessible page
   # 2 > Subsection was found and not accessible
   # 3 > ID was outside the domain
   # 4 > The requested page alias does not exist
   config.tx_mktools.pagenotfoundhandling.ignorecodes = 1,2
   
Der HTTPStatus. Ist dieser Leer wird der unter $TYPO3_CONF_VARS['FE']['pageNotFound_handling_statheader'] angegebene genutzt.

.. code-block:: ts

   config.tx_mktools.pagenotfoundhandling.httpStatus = HTTP/1.0 404 Not Found
   
Der Typ für die Konfiguration (REDIRECT, READFILE)

.. code-block:: ts

   config.tx_mktools.pagenotfoundhandling.type = READFILE
   
Die Daten für den Handler. Momentan der Pfad oder die Url für Typ.

.. code-block:: ts

   config.tx_mktools.pagenotfoundhandling.data = READFILE

Die gesamte Konfig (bis auf ignorecodes) kann auch für jeden der o.g. pageNotFoundCodes einzeln konfiguriert/überschrieben werden. Man kann also z.B. per default den type auf REDIRECT setzen und dann für jeden Code eine eigene Seite angeben.

.. code-block:: ts

   ### bei diesem Beispiel wird ein redirect auf die Startseite gemacht bei fehlenden Berechtigungen
   config.tx_mktools.pagenotfoundhandling.pageNotFoundCodes {
      1 {
         type = REDIRECT
         data = /
         httpStatus...
         logPageNotFound...
      }
      2 {
         type = REDIRECT
         data = /
      }
   }
   
   
Abfangen von fehlenden Datien in bestimmten Ordnern
---------------------------------------------------

.. code-block:: ts

   [globalString = ENV:REQUEST_URI = /Pfad/wo/die/Dateien/liegen/*]
      config.tx_mktools.pagenotfoundhandling.data = /zielseite.html
   [global]
   
mehrsprachige 404-Seiten
------------------------

Um für bestimmte Sprachen andere URLs zu laden, gibt man das Länderkürzel im TS-Pfad vor dem "data" an:

.. code-block:: ts

   plugin.tx_mktools.pagenotfoundhandling.en.data = en/404.html
   plugin.tx_mktools.pagenotfoundhandling.de.data = de/404.html
   
Hintergrund: Per TS-gesetzt Conditions auf eine bestimmte Sprache ([globalVar = GP:L=1]) greifen nicht, da diese im 404-Fall nicht ausgewertet werden können
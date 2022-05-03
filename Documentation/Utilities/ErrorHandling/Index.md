Error Handling
==============

Im Gegensatz zu den TYPO3 Error Handlern, behandeln die mktools Handler auch Fatal Errors und ermöglichen es eine Fehlerseite für Exceptions (nicht für Errors) zu konfigurieren. Damit die Seite also genutzt wird, müssen zunächst die errorHandlerErrors konfiguriert werden. Diese werden alle geloggt. Bei Fehlern in exceptionalErrors wird zusätzlich eine Fehlerseite ausgegeben. In Live Umgebungen sollten z.B. Warnungen in den errorHandlerErros sein aber nicht in den exceptionalErrors. Dadruch werden diese geloggt, führen aber nicht zur Anzeige der Fehlerseite.

Dafür gibt es 2 Dateien. Einen Error Handler und einen Exception Handler. In mktools selbst kann nur konfiguriert werden, welche Seite/Datei ausgegeben wird im Exceptionfall. Dies geht über die Ext Conf, genauer gesagt den Punkt exceptionPage.

exceptionPage
-------------

Die Fehlerseite kann auf 2 Arten angegeben werden.

**FILE**:

Relativer Link zu einer Seite innerhalb von TYPO3.

Beispiel: <FILE:fehler/>

**TYPOSCRIPT**:

Hier wird der Pfad zum TS angegeben.

Beispiel: TYPOSCRIPT:EXT:myext/static/mktools.txt

Das TS sollte wie folgt aussehen:

~~~~ {.sourceCode .ts}
config.tx_mktools.errorhandling {
   ### relativer link zu Seite in TYPO3
   exceptionPage = fehler/
}
~~~~

Anmelden der Error und Exception Handler
----------------------------------------

Dazu muss folgendes in der localconf eingetragen werden:

~~~~ {.sourceCode .php}
$TYPO3_CONF_VARS['SYS']['productionExceptionHandler'] = \DMK\Mktools\ErrorHandler\ExceptionHandler::class;
$TYPO3_CONF_VARS['SYS']['debugExceptionHandler'] = \DMK\Mktools\ErrorHandler\ExceptionHandler::class;
$TYPO3_CONF_VARS['SYS']['errorHandler'] = \DMK\Mktools\ErrorHandler\ErrorHandler::class;
~~~~

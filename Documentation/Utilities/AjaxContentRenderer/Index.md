Ajax Content Renderer
=====================

Ermöglicht das Laden einzelner Content-Elemente per Ajax.

Angestoßen wird der Ajax-Call per Klick auf einen Link mit der Klasse "ajax-link", beim Absenden eines Formulars mit Klasse "ajax-form" oder beim Klicken auf einen Radiobutton oder mit Checkbox mit Klasse "ajax-autotrigger". Die ID des zu ersetzenden Content-Element muss im Quelltext als

~~~~ {.sourceCode .html}
< div id="c1110" >
~~~~

mit gerendert werden (TYPO3 Funktion "Indentation and Frames" auf "Linked Element". Standardmäßig wird das Contentelement neu geladen, in dem das Event ausgelöst wurde. Alternativ dazu kann man im Template eine beliebige ID eines Contentelements angeben, z.B.:

~~~~ {.sourceCode .html}
<form id="mksearch" class="default-form noactionurl ajax-form" method="GET" data-ajaxreplaceid="c1110" >
~~~~

Mit der Klasse ajax-autotrigger im form Element, wird bei Änderungen an nicht text inputs direkt das Formular per Ajax abgeschickt. Das lässt sich für einzelne Formularelemente verhindern indem diese die Klasse ajax-autotrigger-ignore bekommen.

~~~~ {.sourceCode .html}
<form id="mksearch" class="ajax-autotrigger ajax-form" method="GET" data-ajaxreplaceid="c1110" >
   <input type="checkbox" class="ajax-autotrigger-ignore"...
~~~~

Die ajaxreplaceid ist optional. Per default wird ein Elternelement des Formular gesucht mit einer Content ID. Diese wird dann ersetzt.

Hinweis: Per default wird ein div mit der Klasse waiting eingefügt wenn es dieses noch nicht gibt. Dafür muss das gewünschte CSS bereitgestellt werden.

mkforms Formulare
-----------------

Diese nutzen eigene Events zum Abschicken, die überschrieben werden müssen mit folgendem TS (dann gehen aber draft submits etc. nicht mehr)

~~~~ {.sourceCode .ts}
### damit das abschicken per ajax funktioniert. sonst werden die mktools events
### durch mkforms JS überschrieben
config.tx_mkforms.loadJsFramework = 0
~~~~

Damit draft mode etc. funktionieren, muss man diese beim Klick auf den betreffenden Button selbst setzen. Dazu einfach ein JS nach folgendem Schema aufrufen:

~~~~ {.sourceCode .js}
jQuery('.draftButton').on('click',function(event) {
   event.preventDefault();
   $("#gameForm_AMEOSFORMIDABLE_SUBMITTED").val('AMEOSFORMIDABLE_EVENT_SUBMIT_DRAFT');
});
~~~~

Caching
-------

Wenn das Caching nicht gewünscht ist, dann einfach noch die Klasse "notcachable" hinzufügen.

Formulare mit Bildupload
------------------------

In diesem Fall müssen noch 2 Dinge gemacht werden. Zum einen muss die folgende Zeile im TS ergänzt werden:

~~~~ {.sourceCode .ts}
page.includeJS.mktoolsAjaxForm = typo3conf/ext/mktools/Resources/Public/JavaScript/jquery.form.min.js
~~~~

Dann muss dem Formular noch die Klasse "notcachable" gegeben.

cHash Probleme vermeiden
------------------------
Formulare mit der Ajaxfunktion sollten immer mit abgeschickt POST stehen. Ansonsten kann folgendes passieren: Es wird eine Seite mit Parametern und cHash aufgerufen, z.B. eine Newsdetailseite. Beim Ajaxrequest werden die Daten des Formular an die URL angehangen, was dann je nach TYPO3 Konfiguration einen 404 Fehler erzeugt weil der cHash nicht zu den Parametern passt. Auf einer Seite ohne weitere Parameter würde das wahrscheinlich nicht passieren. Es ist dennoch ratsam Formulare immer per POST abzuschicken.

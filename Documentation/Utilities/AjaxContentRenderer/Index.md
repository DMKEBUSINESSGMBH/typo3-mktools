Ajax Content Renderer
=====================

Ermöglicht das Laden einzelner Content-Elemente per Ajax. Muss im Extension Manager aktiviert werden im Konfigurationskey ajaxContentRendererActive. Außerdem muss das TypoScript Template eingebunden werden.

~~~~ {.sourceCode .ts}
<INCLUDE_TYPOSCRIPT: source="FILE: EXT:mktools/Configuration/TypoScript/contentrenderer/setup.txt">
~~~~

Die JavaScript-Bibliotheken benötigen kein jQuery. Angestoßen wird der Ajax-Call per Klick auf einen Link mit der Klasse "ajax-link", beim Absenden eines Formulars mit Klasse "ajax-form" oder beim Klicken auf einen Radiobutton oder mit Checkbox mit Klasse "ajax-autotrigger". Die ID des zu ersetzenden Content-Element muss im Quelltext als

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

Hinweis: Es sollten keine Formularefelder mit 
gleichem Name vorkommen außer multiplen Select-/Checkboxen. Ansonsten wird immer nur der Wert
des ersten Feldes übermittelt. Browser würden hingegen den letzten übermitteln.

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
document.querySelectorAll('.draftButton').forEach(function(button) {
  button.addEventListener('click', function(event) {
   event.preventDefault();
   document.getElementById('gameForm_AMEOSFORMIDABLE_SUBMITTED').value = 'AMEOSFORMIDABLE_EVENT_SUBMIT_DRAFT';
  });
});
~~~~

Caching
-------

Wenn das clientseitige Caching der Ajax Requests nicht gewünscht ist, dann einfach noch die Klasse "notcachable" hinzufügen.

Formulare mit Bildupload
------------------------

Datei-Uploads werden direkt über die native FormData-API übertragen. Ein zusätzliches Formular-Plugin ist nicht nötig. Requests mit ausgewählten Dateien werden nicht gecacht. Mit der Klasse "notcachable" lässt sich das Caching für das Formular generell deaktivieren.

cHash Probleme vermeiden
------------------------
Formulare mit der Ajaxfunktion sollten immer mit abgeschickt POST stehen. Ansonsten kann folgendes passieren: Es wird eine Seite mit Parametern und cHash aufgerufen, z.B. eine Newsdetailseite. Beim Ajaxrequest werden die Daten des Formular an die URL angehangen, was dann je nach TYPO3 Konfiguration einen 404 Fehler erzeugt weil der cHash nicht zu den Parametern passt. Auf einer Seite ohne weitere Parameter würde das wahrscheinlich nicht passieren. Es ist dennoch ratsam Formulare immer per POST abzuschicken.

Inhaltselemente direkt initial mit Ajax nachladen
------------------------
Es ist auch möglich, dass ein Inhaltselement direkt initial per Ajax nachgeladen wird. Dies funktioniert für die Plugins vom Typ USER und USER_INT. Für andere Typen macht das wenig Sinn und wurde daher nicht implementiert. Somit kann eine Seite z.B. über Varnish ausgeliefert, obwohl diese USER_INT enthält, da nach außen keine USER_INT Objekte enthalten sind. Es muss nichts weiter gemacht werden, als im betroffenen tt_content Element den Haken bei "mit Ajax nachladen?" zu setzen.

Wenn in dem Plugin allerdings ein Formular vorhanden ist, dann muss sichergestellt werden, dass das Formular selbst auch per Ajax abgeschickt wird. Dazu muss z.B. einfach die Klassen ajax-form ins form-Tag.

Wenn das Plugin selbst JS etc. nachlädt, dann muss das natürlich auch bedacht werden, da das auf Grund des nachladens nicht berücksichtigt werden kann.

Der Link, welcher für das nachladen eines Inhaltselement verwendet wird, kann per TypoScript beeinflusst werden. 
So kann z.B. der Parameter für eine News übernommen werden, um ein Kommentarplugin dafür per Ajax nachzuladen. 
Per default wird auf die aktuelle Seite verlinkt, ohne irgendwelche Parameter zu berücksichtigen. Der Parameter 
contentid wird außerdem automatisch hinzugefügt. Dinge wie noHash oder noCache sollten aus Gründen der
Performance nicht gesetzt werden.

~~~~ {.sourceCode .ts}
lib.tx_mktools.loadUserIntWithAjaxUrl{
    ### This adds the tx_ttnews[tt_news] parameter to the link if present.
    ### Might be useful if you want to load a comment plugin for news for example
    useKeepVars = 1
    useKeepVars.add = tx_ttnews::tt_news
}
~~~~

Ajax Request Typ festlegen
------------------------
Per default werden alle Ajax Requests als POST abgeschickt. In Formularen lässt sich das wie üblich konfigurieren. Bei normalen Links kann die Klasse "ajax-get-request" genutzt werden. Bei GET Requests ist es manchmal wünschenswert, dass die URL so verwendet wird, wie sie ist (normalerweise fügt mktools Parameter wie den type 9267 hinzu etc.). Dazu muss die Klassen "ajax-dont-add-parameters-to-request" gesetzt werden. Dann muss man sich aber natürlich selbst darum kümmern, dass die URL alle notwendigen Parameter wie den type etc. enthält.

Redirects per Ajax auswerten
------------------------
Wenn der normale Location Header für Redirects verwendet wird, dann folgen Browser diesem Redirect innerhalb des Ajax Requests, womit am Ende der Inhalt des Redirects steht und das JavaScript nicht weiß, dass es einen Redirect gab. Daher muss in solchen Fällen ein dedizierter Header verwendet werden. Dieser heißt **Mktools_Location**.

~~~~ {.sourceCode .php}
header('Mktools_Location: https://www.example.com');
~~~~

Event Handling
--------------
Per default verhindert mktools bei jedem Element in einem Formular, dass das Default-Verhalten
ausgelöst wird. Das bedeutet z.B. dass Checkboxen nicht den checked Status erhalten. Wenn das Formular
z.B. ein anderes Element lädt und selbst gar nicht aktualisiert wird, dann bekommt eine Checkbox
keinen checked Status. Wenn das betrffende Element die CSS Klasse "ajax-dont-prevent-default" bekommt,
wird das verhindert.

Eigene Methode zum ersetzen des Inhalts
---------------------------------------

Wenn ein Element, welches den Ajax Request startet, das data-Attribut
**data-ajax-custom-replace-method** hat, dann wird diese Method zum ersetzen
des Inhalts aufgerufen. Die Method muss in der DMK.Objects.AjaxContent Klasse 
ergänzt werden. z.B. so:

~~~~ {.sourceCode .js}
DMK.Objects.AjaxContent.prototype.replaceContentForSomethingElse = function(contentId, html, from, to) {
    appendContent(contentId, html)
};

~~~~

Ansonsten wird der komplette Inhalt des Elements 1:1 ersetzt.

JavaScript-Schnittstellen
------------------------

`DMK.Request.doCall`, `DMK.AjaxContent.handleAjaxClick` und die Modal-Methoden erwarten native DOM-Elemente. `getLoader` und `getBox` liefern ebenfalls DOM-Elemente; ein Request ohne eigenen Loader liefert `null`. Ein laufender Request liefert ein `XMLHttpRequest` mit `abort()` und `getResponseHeader()` zurück. Die Hooks `onStart`, `onSuccess`, `onFailure` und `onComplete` bleiben erhalten und erhalten gegebenenfalls dieses native Request-Objekt.

Die Modal-Skripte verwenden native HTML-Dialoge und behalten die CSS-Klassen von Bootstrap bzw. Foundation bei. Sie benötigen die jeweiligen Styles, aber kein JavaScript dieser Frameworks. Wie bisher ist nur eine der beiden Modal-Implementierungen einzubinden. `ModalFoundation.js` benötigt nun ebenfalls `Base.js` und `Request.js`.

Die Skripte setzen einen aktuellen Browser mit Unterstützung für `FormData`, `URLSearchParams`, die Web Animations API und `HTMLDialogElement.showModal()` voraus.

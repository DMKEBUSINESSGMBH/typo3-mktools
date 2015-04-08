.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _ajax-content-renderer:

Ajax Content Renderer
=====================

Ermöglicht das Laden einzelner Content-Elemente per Ajax.

Angestoßen wird der Ajax-Call per Klick auf einen Link mit der Klasse "ajax-link", beim Absenden eines Formulars mit Klasse "ajax-form" oder beim Klicken auf einen Radiobutton oder mit Checkbox mit Klasse "ajax-autotrigger". Die ID des zu ersetzenden Content-Element muss im Quelltext als

.. code-block:: html

   < div id="c1110" >

mit gerendert werden (TYPO3 Funktion "Indentation and Frames" auf "Linked Element". Standardmäßig wird das Contentelement neu geladen, in dem das Event ausgelöst wurde. Alternativ dazu kann man im Template eine beliebige ID eines Contentelements angeben, z.B.:

.. code-block:: html

   <form id="mksearch" class="default-form noactionurl ajax-form" method="GET" data-ajaxreplaceid="c1110" >
   
Mit der Klasse ajax-autotrigger im form Element, wird bei Änderungen an nicht text inputs direkt das Formular per Ajax abgeschickt. Das lässt sich für einzelne Formularelemente verhindern indem diese die Klasse ajax-autotrigger-ignore bekommen.

.. code-block:: html

   <form id="mksearch" class="ajax-autotrigger ajax-form" method="GET" data-ajaxreplaceid="c1110" >
      <input type="checkbox" class="ajax-autotrigger-ignore"...

mkforms Formulare
-----------------

Diese nutzen eigene Events zum Abschicken, die überschrieben werden müssen mit folgendem TS (dann gehen aber draft submits etc. nicht mehr)

.. code-block:: ts

   ### damit das abschicken per ajax funktioniert. sonst werden die mktools events
   ### durch mkforms JS überschrieben
   config.tx_mkforms.loadJsFramework = 0
   
Damit draft mode etc. funktionieren, muss man diese beim Klick auf den betreffenden Button selbst setzen. Dazu einfach ein JS nach folgendem Schema aufrufen:

.. code-block:: js

   jQuery('.draftButton').on('click',function(event) {
      event.preventDefault();
      $("#gameForm_AMEOSFORMIDABLE_SUBMITTED").val('AMEOSFORMIDABLE_EVENT_SUBMIT_DRAFT');
   });
   
Caching
-------

Wenn das Caching nicht gewünscht ist, dann einfach noch die Klasse "notcachable" hinzufügen.


Formulare mit Bildupload
------------------------

In diesem Fall müssen noch 2 Dinge gemacht werden. Zum einen muss die folgende Zeile im TS ergänzt werden:

.. code-block:: ts

   page.includeJS.mktoolsAjaxForm = typo3conf/ext/mktools/Resources/Public/JavaScript/jquery.form.min.js
   
Dann muss dem Formular noch die Klasse "notcachable" gegeben.
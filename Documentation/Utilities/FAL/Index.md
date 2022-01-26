FAL
===

mktools bietet die Möglichkeit FAL Dateien für cal und tt\_news einzubinden und ersetzt damit ab TYPO3 6.2 die Extensions cal\_\_dam\_reference und dam\_ttnews.


FAL für Cal
-----------

Einfach in der Extension Konfig unter Module aktivieren und das Static TS einfügen. (evtl. noch den Cache Ordner leeren) Anschließend stehen FAL Bilder für Cal Events bereit. Anpassungen sollten in lib.tx\_mktools.cal.event.image vorgenommen werden. (z.B. Template setzen, Bildgrößen konfigurieren etc.)

FAL Bilder für locations und organizer stehen noch nicht zur Verfügung.

Natürlich muss mktools nach cal geladen werden.

Beispiel TypoScript (es gibt noch mehr Konfigurationsmöglichkeiten in rn\_base):

~~~~ {.sourceCode .ts}
lib.tx_mktools.cal.event {
   image = USER
   image {
      userFunc = Sys25\RnBase\Utility\TSFAL->printImages
      refField = tx_mktools_fal_images
      refTable = tx_cal_event
      #template = ...
      subpartName = ###DOWNLOADS###
      media {
         file = IMG_RESOURCE
         file.file.import.field = file
      }
   }
}
~~~~

Beispiel Template:

~~~~ {.sourceCode .html}
<!-- ###DOWNLOADS### START -->
   <!-- ###MEDIAS### START -->
   <ul class="downloads">
      <!-- ###MEDIA### START -->
      <li><a href="###MEDIA_FILE_PATH######MEDIA_FILE_NAME###" rel="external">###MEDIA_TITLE###</a></li>
      <!-- ###MEDIA### END -->
   </ul>
   <!-- ###MEDIAS### END -->
<!-- ###DOWNLOADS### END -->
~~~~

**Migration von cal\_\_dam\_reference**

Um DAM Bilder nach FAL zu migrieren, einfach mittels der DAM2FAL Migration das Feld dam\_images in der Tabelle tx\_cal\_event nach tx\_mktools\_fal\_images übertragen.

FAL für tt\_news
----------------

Einfach in der Extension Konfig unter Module aktivieren und das Static TS einfügen. (evtl. noch den Cache Ordner leeren) Anschließend stehen FAL Bilder für tt\_news Beiträge bereit. Anpassungen sollten in lib.tx\_mktools.tt\_news.news.image vorgenommen werden. (z.B. Template setzen, Bildgrößen konfigurieren etc.)

Natürlich muss mktools nach cal geladen werden.

Beispiel TypoScript (es gibt noch mehr Konfigurationsmöglichkeiten in rn\_base):

~~~~ {.sourceCode .ts}
lib.tx_mktools.tt_news.news {
   image = USER
   image {
      userFunc = Sys25\RnBase\Utility\TSFAL->printImages
      refField = tx_mktools_fal_images
      refTable = tt_news
      #template = ...
      subpartName = ###IMAGES_LIST###
      media {
         file = IMG_RESOURCE
         file.file.import.field = file
      }
   }

   media = USER
   media {
      userFunc = Sys25\RnBase\Utility\TSFAL->printImages
      refField = tx_mktools_fal_media
      refTable = tt_news
      #template = ...
      media {
         file = IMG_RESOURCE
         file.file.import.field = file
      }
   }
}
~~~~

Beispiel Template:

~~~~ {.sourceCode .html}
<!-- ###IMAGES_LIST### START -->
   <!-- ###MEDIAS### START -->
      <div class="imagelist">
         <!-- ###MEDIA### START -->
            <div class="img###MEDIA_DCCLASS###"><img src="###MEDIA_FILE###" alt="###MEDIA_ALTERNATIVE###" title="###MEDIA_TITLE###" /></div>###MEDIA_DCBREACK###
         <!-- ###MEDIA### END -->
      </div>
   <!-- ###MEDIAS### END -->
<!-- ###IMAGES_LIST### END -->
~~~~

**Migration von dam\_ttnews**

Um DAM Bilder nach FAL zu migrieren, einfach das folgende Mapping verwenden:

| Tabelle | Feldname | Referenz DAM | Referenz FAL |
| --- | --- | --- | --- |
| tt_news | Images | tx\_damnews\_dam\_images | tx\_mktools\_fal\_images |
| tt_news | Media | tx\_damnews\_dam\_media | tx\_mktools\_fal\_media |



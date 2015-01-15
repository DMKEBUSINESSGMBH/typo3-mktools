.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _fal:

FAL
===

mktools bietet die Möglichkeit FAL Dateien für cal und tt_news einzubinden und ersetzt damit
ab TYPO3 6.2 die Extensions cal__dam_reference und dam_ttnews.

FAL für Cal
-----------

Einfach in der Extension Konfig unter Module aktivieren und das Static TS einfügen. (evtl. noch den Cache Ordner leeren) Anschließend stehen FAL Bilder für Cal Events bereit. Anpassungen sollten in lib.tx_mktools.cal.event.image vorgenommen werden. (z.B. Template setzen, Bildgrößen konfigurieren etc.)

FAL Bilder für locations und organizer stehen noch nicht zur Verfügung.

Natürlich muss mktools nach cal geladen werden.

Beispiel TypoScript (es gibt noch mehr Konfigurationsmöglichkeiten in rn_base):

.. code-block:: ts

   lib.tx_mktools.cal.event {
      image = USER
      image {
         userFunc = tx_rnbase_util_TSFAL->printImages
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

Beispiel Template:

.. code-block:: html

   <!-- ###DOWNLOADS### START -->
      <!-- ###MEDIAS### START -->
      <ul class="downloads">
         <!-- ###MEDIA### START -->
         <li><a href="###MEDIA_FILE_PATH######MEDIA_FILE_NAME###" rel="external">###MEDIA_TITLE###</a></li>
         <!-- ###MEDIA### END -->
      </ul>
      <!-- ###MEDIAS### END -->
   <!-- ###DOWNLOADS### END -->
   
**Migration von cal__dam_reference**

Um DAM Bilder nach FAL zu migrieren, einfach mittels der DAM2FAL Migration das Feld dam_images in der Tabelle tx_cal_event nach tx_mktools_fal_images übertragen.


FAL für tt_news
---------------

Einfach in der Extension Konfig unter Module aktivieren und das Static TS einfügen. (evtl. noch den Cache Ordner leeren) Anschließend stehen FAL Bilder für tt_news Beiträge bereit. Anpassungen sollten in lib.tx_mktools.tt_news.news.image vorgenommen werden. (z.B. Template setzen, Bildgrößen konfigurieren etc.)

Natürlich muss mktools nach cal geladen werden.

Beispiel TypoScript (es gibt noch mehr Konfigurationsmöglichkeiten in rn_base):

.. code-block:: ts

   lib.tx_mktools.tt_news.news {
      image = USER
      image {
         userFunc = tx_rnbase_util_TSFAL->printImages
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
         userFunc = tx_rnbase_util_TSFAL->printImages
         refField = tx_mktools_fal_media
         refTable = tt_news
         #template = ...
         media {
            file = IMG_RESOURCE
            file.file.import.field = file
         }
      }
   }

Beispiel Template:

.. code-block:: html

   <!-- ###IMAGES_LIST### START -->
      <!-- ###MEDIAS### START -->
         <div class="imagelist">
            <!-- ###MEDIA### START -->
               <div class="img###MEDIA_DCCLASS###"><img src="###MEDIA_FILE###" alt="###MEDIA_ALTERNATIVE###" title="###MEDIA_TITLE###" /></div>###MEDIA_DCBREACK###
            <!-- ###MEDIA### END -->
         </div>
      <!-- ###MEDIAS### END -->
   <!-- ###IMAGES_LIST### END -->
   
   
**Migration von dam_ttnews**

Um DAM Bilder nach FAL zu migrieren, einfach mittels der DAM2FAL Migration das Feld tx_damnews_dam_images in der Tabelle tt_news nach tx_mktools_fal_images und tx_damnews_dam_media nach tx_mktools_fal_media übertragen.

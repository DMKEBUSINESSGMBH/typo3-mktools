Views
=====

FlashMessages
-------------

Mit den FlashMessages können Fehler, Bestätigungen, etc. erzeugt und beim nächsten Request ausgegeben werden.

Beispielsweise beim Absenden eines Formulars, sollte anschließen ein Redirect durchgeführt werden, um Doppelte Posts durch neu laden der Seite zu verhindern.

Um dann auf der Nächsten Seite eine Entsprechende Meldung auszugeben, muss diese vor dem Redirect in der Session abgelegt und beim Nächsten Request dort wieder ausgelesen werden.

Dafür gibt es die FlasMessages in MKTOOLS.

Beispiel für das hinzufügen einer Meldung für den Nächsten Request:

~~~~ {.sourceCode .php
 tx_mktools_util_FlashMessage::addSuccess(
     'Ihre Daten wurden erfolgreich gespeichert.'
 );}
~~~~

Für die Ausgabe ist die FlashMessage-Action von MKTOOLS zuständig. Diese kann entweder direkt im Backend über ein Plugin auf der Seite abgelegt oder über eine lib direkt im Rahmentemplate immer mit abgefragt werden.

Show Template
-------------

Ein HTML Template wird als Seitencontent ausgegeben. Dabei ist es möglich Language Marker (\#\#\#LABEL\_MYTEXT\#\#\#) auszugeben.

Im Plugin kann zusätzlich der gewünschte Subpart gewählt werden. Wenn der nicht angegeben wird, dann wird das gesamte Template ausgegeben.

Beispiel Template:

~~~~ {.sourceCode .html}
###FIRSTSUBPART###
   ###LABEL_ONE###
###FIRSTSUBPART###
###SECONDSUBPART###
   ###LABEL_TWO###
###SECONDSUBPART###
~~~~

Beispiel TypoScript:

~~~~ {.sourceCode .ts}
plugin.tx_mktools.locallangFilename.1 = Pfad-zur-eigenen-locallang-Datei
~~~~

Beispiel Locallang:

~~~~ {.sourceCode .xml}
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3locallang>
   <meta type="array">
      <type>general</type>
      <description></description>
   </meta>
   <data type="array">
      <languageKey index="default" type="array">
         <label index="label_one">erstes Label</label>
         <label index="label_two">zweites Label</label>
      </languageKey>
   </data>
</T3locallang>
~~~~

Im Plugin wird dann der gewünschte Subpart gewählt, womit entweder "erstes Label" oder "zweites Label" ausgegeben wird.

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
 \DMK\Mktools\Session\FlashMessageStorage::addSuccess(
     'Ihre Daten wurden erfolgreich gespeichert.'
 );}
~~~~

Für die Ausgabe ist die FlashMessage-Action von MKTOOLS zuständig. Diese kann entweder direkt im Backend über ein Plugin auf der Seite abgelegt oder über eine lib direkt im Rahmentemplate immer mit abgefragt werden.

TypoScript Lib
--------------

Rendered TypoScript Objekte im Pfad plugin.tx_mktools.tslib

Im TypoScript Setup Teil des Plugins (Flexform) kann das wie folgt konfiguriert werden:

```
    tslib = COA
    tslib {
        10 = TEXT
        10.value = Hello World
    }
```

Wenn ein ungeachtes Objekt wie COA_INT oder USER_INT gerendert werden soll, dann muss das mktools tslib Plugin ebenfalls ungeached sein. Das kann mit der TypoScript Konfiguration plugin.tx_mktools.toUserInt = 1 gesetzt werden. Im Flexform eines Plugins kann das im TypoScript Setup Teil des Plugins (Flexform) ebenfalls mit toUserInt = 1 gesetzt werden.

**Vorsicht wenn andere rn_base Actions gerendert werden sollen.** Das Problem ist, dass die Flexform Konfiguration des mktools Plugins ebenfalls für die andere rn_base Action verwendet wird. Das hat zur Folge dass die action Konfiguration immer durch den Wert aus dem Flexform überschrieben wird, womit man in einer Endlosschleife landet, die immer wieder das mktools Plugin rendert. Die Lösung ist rn_base mitzuteilen, dass die für die andere Action die Flexform Konfiguration ignoriert werden soll. Das geht so:

Flexform Konfiguration im TypoScript Setup Teil des Plugins:
```
    tslib =< lib.myOtherAction
```

TypoScript Konfiguration für lib.:
```
    lib.myOtherAction < plugin.tx_myotherext
    lib.myOtherAction.action = myOtherAction
    lib.myOtherAction.ignoreFlexFormConfiguration = 1
```

**Migration**

Es gibt ein Migration Command für die tsobj Extension. 
Diese macht aus allen tsobj Plugins das mktools Pendant. Dazu einfach das Command 
mktools:migrate-tscobj-plugins über den Scheduler Task "Execute console commands" oder 
auf der CLI ausführen (bin/typo3 mktools:migrate-tscobj-plugins). 

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

switchableControllerActions (extbase)
======================================

Mit TYPO3 12 sind die switchableControllerActions in extbase ersatzlos entfallen. Die Migration
der Konfiguration ist relativ simpel und im Changelog beschrieben. TYPO3 bietet aber keine 
Möglichkeit die bestehenden Plugins zu migrieren. Dafür stellt mktools ein Command bereit.
Dieses migriert eine switchableControllerAction zu einem neuen list_type. Dabei werden
auch direkt etwaige Berechtigungen in BE Gruppen migiriert.

Command für die Migration:

~~~~ {.sourceCode .sh
 bin/typo3 mktools:migrate-switchable-controller-actions --actions="News->detail;News->list" --list-type=news_list --new-list-type=news_list_detail
~~~~

actions sollte genau der Wert sein, der bisher im Flexform/TypoScript gewählt wurde und
für den ein neues dediziertes Plugin angelegt wurde. Das Command entfernt aus dem Flexform
in den Plugins alle Werte, die im aktuellen Flexform nicht mehr enthalten sind.

Content Replacer
================

Muss im Extension-Manager aktiviert werden. Bietet zurzeit die selbe Funktionalität wie die ja\_replacer Extension, mit der Ausnahme, das sie auch im Zusammenhang mit der Scriptmerger Extension funktioniert und eine Basiskonfiguration bietet. Dazu muss nur sicher gestellt werden, das mktools in der extlist der localconf.php nach dem scriptmerger angegeben wird.

Basiskonfiguration
------------------

Mit dem statischen TypoScript Template "MK Tools - Content Replacer" wird eine Basiskonfiguration bereitgestellt, welche bereits Links in folgenden Ordnern auf eine definierte statische Domain mappt:

    fileadmin, uploads, typo3conf, typo3temp

Die statische Domain ist im Config Bereich wie folgt zu konfigurieren:

~~~~ {.sourceCode .ts}
config.tx_mktools.contentreplace.staticBaseUrl = http://example.com/
~~~~

Kann über TypoScript wahlweise deaktiviert werden. Standard ist 1.

~~~~ {.sourceCode .ts}
config.tx_mktools.contentreplace.enable = 0
~~~~

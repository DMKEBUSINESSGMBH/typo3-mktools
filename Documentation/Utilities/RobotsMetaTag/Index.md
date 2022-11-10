Robots Meta Tag
===============

Achtung
-------

Diese Funktion ist nur interessant wenn Redakteure das Robots Meta Tag setzen wollen. Ansonsten kommt zu viel Balast mit. Außerdem ist es ansonsten nicht mehr möglich den Wert über page.meta.robots zu setzen. D.h. also in einem bestehenden Portal kann das nicht einfach eingesetzt werden, wenn bisher auf Seiten die Einstellungen über page.meta.robots vorgenommen wurden, da diese durch den default Wert überschrieben werden.

Allgemein
---------

Muss im Extension-Manager aktiviert werden.

Mit dieser Option kann für jede Seite ein individuelles Robots Meta Tag vergeben werden.

Dazu gibt es in den Seiteneigenschaften eine Select-Box mit den möglichen Werten. Wird der default Wert verwendet ("use default value from TypoScript (check rootline for explicit value first)"), dann wird rekursiv in den parent-Seiten nach einem gesetzten Robots-Tag gesucht und dieser übernommen falls vorhanden. Wird keiner gefunden, dann wird der default Wert aus TypoScript (page.meta.robots.cObject.default) verwendet. Wird der Wert auf "use default value from TypoScript (page.meta.robots.cObject.default)" gesetzt, dann wird direkt der default Wert aus TypoScript verwendet, ohne die ganze Rootline zu prüfen.

Falls kein Wert gefunden werden kann, dann gilt der TS-Default, der wie folgt gesetzt wird:

~~~~ {.sourceCode .ts}
config.tx_mktools.seorobotsmetatag.default = NOINDEX,NOFOLLOW
~~~~

Mit ein bisschen TSConfig kann der default Wert z.B. auf -1 gesetzt werden. Damit würde immer nur der default Wert aus TypoScript verwendet, ohne die Rootline zu prüfen.

~~~~ {.sourceCode .ts}
TCAdefaults.pages.mkrobotsmetatag = -1
~~~~

Hinweis: Das statische TypoScript Template "MK Tools - SEO Robots Meta Tag" muss inkludiert werden.

Allgemein
---------

Echte "by TS" Option einbauen und bestehende in "use parent or default" umbenennen, damit Funktion auch in bestehenden Portalen eingesetzt werden kann ohne dass bisherige page.meta.robots überschrieben werden.

Zusammenspiel mit EXT:seo XML Sitemap
-------------------------------------
Damit das Robots Meta Tag in der Sitemap berücksichtigt wird, wird ein eigener
XML Sitemap Dataprovider im statischen TypoScript Template registriert. Das muss daher
nach den statischen Templates von EXT:seo registriert werden. Per default wird dann auch

~~~~ {.sourceCode .ts}
plugin.tx_seo.settings.xmlSitemap.sitemaps.pages.additionalWhere = mkrobotsmetatag IN (-1,0,1,2) AND no_index = 0
~~~~ 
gesetzt. Also vorsicht, falls das überschrieben wird.

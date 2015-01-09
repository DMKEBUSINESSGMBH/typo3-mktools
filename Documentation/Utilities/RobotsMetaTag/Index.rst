.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _robots-meta-tag:

Robots Meta Tag
===============

Achtung
-------

Diese Funktion ist nur interessant wenn Redakteure das Robots Meta Tag setzen wollen.
Ansonsten kommt zu viel Balast mit.
Außerdem ist es ansonsten nicht mehr möglich den Wert über page.meta.robots zu setzen.
D.h. also in einem bestehenden Portal kann das nicht einfach eingesetzt werden,
wenn bisher auf Seiten die Einstellungen über page.meta.robots vorgenommen wurden,
da diese durch den default Wert überschrieben werden.

Allgemein
---------

Muss im Extension-Manager aktiviert werden.

Mit dieser Option kann für jede Seite ein individuelles Robots Meta Tag vergeben werden.

Dazu gibt es in den Seiteneigenschaften eine Select-Box mit den möglichen Werten. Wird kein Wert gesetzt ("by TS"), dann wird rekursiv in den parent-Seiten nach einem gesetzten Robots-Tag gesucht und dieser übernommen falls vorhanden.

Falls kein Wert gefunden werden kann, dann gilt der TS-Default, der wie folgt gesetzt wird:

.. code-block:: ts

   config.tx_mktools.seorobotsmetatag.default = NOINDEX,NOFOLLOW
   
Somit ist eigentlich "by TS" missverständlich. Besser wäre z.B. "use parent or default".

Hinweis: Das statische TypoScript Template "MK Tools - SEO Robots Meta Tag" muss
inkludiert werden.


Allgemein
---------

Echte "by TS" Option einbauen und bestehende in "use parent or default" umbenennen, damit Funktion auch in bestehenden Portalen eingesetzt werden kann ohne dass bisherige page.meta.robots überschrieben werden.
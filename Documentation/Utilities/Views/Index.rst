.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _views:

Views
=====

Show Template
-------------

Ein HTML Template wird als Seitencontent ausgegeben. Dabei ist es möglich Language Marker (###LABEL_MYTEXT###) auszugeben.

Im Plugin kann zusätzlich der gewünschte Subpart gewählt werden. Wenn der nicht angegeben wird, dann wird das gesamte Template ausgegeben.

Beispiel Template:

.. code-block:: html

   ###FIRSTSUBPART###
      ###LABEL_ONE###
   ###FIRSTSUBPART###
   ###SECONDSUBPART###
      ###LABEL_TWO###
   ###SECONDSUBPART###

Beispiel TypoScript:

.. code-block:: ts

   plugin.tx_mktools.locallangFilename.1 = Pfad-zur-eigenen-locallang-Datei

Beispiel Locallang:

.. code-block:: xml

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

Im Plugin wird dann der gewünschte Subpart gewählt, womit entweder "erstes Label" oder "zweites Label" ausgegeben wird.

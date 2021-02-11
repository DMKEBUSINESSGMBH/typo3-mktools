MK TOOLS
========

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-8.7%20%7C%209.5-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mktools.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/mktools)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mktools.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/mktools)
[![Build Status](https://img.shields.io/github/workflow/status/DMKEBUSINESSGMBH/typo3-mktools/PHP-CI.svg?maxAge=3600&style=flat-square&logo=github-actions)](https://github.com/DMKEBUSINESSGMBH/typo3-mktools/actions?query=workflow%3APHP-CI)
[![License](https://img.shields.io/packagist/l/dmk/mktools.svg?maxAge=3600&style=flat-square&logo=gnu)](https://packagist.org/packages/dmk/mktools)

What does it do?
----------------

MK Tools ist eine Bibliothek von Funktionalitäten, welche universell einsetzbar sind.

Einzelne Funktionalitäten müssen über den Extension-Manager aktiviert werden.

Folgende Features zählen u.a. dazu:

-   Content Replacer, mit welchem einfach CDNs für Bilder in typo3temp etc. genutzt werden können
-   Page not found handling mit umfangreichen Konfigurationsmöglichkeiten
-   Error handling, welches das TYPO3 error handling erweitert und z.B auch fatal errors behandelt
-   Robots Meta Tag für Redakteure in Seiten wählbar
-   Tool zur Generierung der realurl Konfiguration mit fixed postvartypes
-   Tool, um Inhalte mittels Ajax nachzuladen
-   FAL Medien für cal und tt\_news um cal\_\_dam\_reference und dam\_ttnews zu migrieren in TYPO3 6.2
-   TCA Overrides korrekt auslesen, womit eine einfachere Erweiterung von TCA möglich ist
-   verhindern dass das syslog mit Meldungen überflutet wird


[Utilities](Documentation/Utilities/Index.md)

[Changelog](Documentation/Changelog.md)

Changelog
=========

11.0.0
-----

- support for TYPO3 10.4 and 11.5 only
- [BREAKING] remove all deprecated classes. Only namespaced classes are now used.
- add update wizard to migrate mktools plugins which use old classes
- cleanup and streamlining
- removed FAL fields for cal and tt_news
- add command to migrate and generate slugs for arbitrary tables (check documentation)
- add command to migrate old form finisher email override settings in plugins (check documentation)
- add command to migrate old file group fields in the database to FAL references (check documentation)
- add StaticNumberRangeMapper which overcomes common limits with StaticRangeMapper in cinjuction with pagination and filters (check documentation)

10.1.2
-----
- update parsedown library

10.1.1
-----
- bugfixes

10.1.0
-----

- require rn_base >= 1.15.0
- bugfixes


10.0.4 - 10.0.8
-----

- bugfixes


10.0.3
-----

- cleanup

10.0.2
-----

- enable usage of page type parameter through $_POST
- bugfixes


10.0.1
-----

- added command and utility to migrate/generate slugs and old tscobj plugins
- bugfixes


10.0.0
-----

- compatibility to TYPO3 9.5 and 10.4 only
- moved all classes to namespaces. deprecated old classes.
- removed unused/obsolete features likes the find unused locallang labels command
or the custom page not found handling.


9.5.9
-----

- add aria-hidden attribute to placeholder links


9.5.8
-----

-   fixed content replacement when USER_INT/COA_INT objects are present

9.5.7
-----

-   add aria label to placeholder link when loading USER(_INT) with AJAX
-   Add TYPO3 10, PHP 7.4 and nightlies to travis ci

9.5.6
-----

-   cleanup replaced deprecated Tx_Rnbase_Configuration_Processor
-   don't use APC/APCu on CLI

9.5.5
-----

-   fix using deprecated _GETset
-   Fix extension key in composer json
-   bugfix support new rn_base version

9.5.4
-----

-   dont use old devlog anymore
-   fix ext_conf labels 
-   another method to check translation overlay for single records

9.5.3
-----

-   add Travis-CI 

9.5.2
-----

-   fixed support of translated records in language menus in TYPO3 9.4. 
Please take care to add the assignment of the menuConfiguration inside your TypoScript. (see documentation)

9.5.1
-----

-   new feature to support translated records in language menus

9.5.0
-----

-   added support for TYPO3 9.5 (page not found handling is dropped as well as all realurl features for TYPO3 9.5. In 8.7 everything as before.)
-   dropped support for TYPO3 6.2 and 7.6
-   ajax content renderer is not working as long as issue 88631 of TYPO3 core is not fixed

3.0.24 - 3.0.29
-----

-   bugfixes

3.0.23
-----

-   ajax requests can have a dedicated redirect. check documentation

3.0.22
-----

-   bugfixes

3.0.21
-----

-   GET Request can be triggered instead of POST in ajax requests

3.0.20
-----

-   update bootswatch
-   bugfixes

3.0.18 - 3.0.19
-----

-   cleanup
-   bugfixes

3.0.17
-----

-   new utility to use apc(u) easy as cache backend

3.0.16
-----

-   take care of multi-select/-checkboxes for ajax POST requests
-   cleanup
-   bugfixes 

3.0.15
-----

-   add hook after fetching pagenotfound content

3.0.14
-----

-   optimized image handling in ajax requests

3.0.13
-----

-   added JS hook after content is replaced in ajax requests

3.0.12
-----

-   make it possible to lazy load tt_content USER_INT plugins via ajax at page

3.0.11
-----

-   don't ignore codes 1 and 2 for any typeNum except 0 by default in page not found handling

3.0.10
-----

-   cleanup

3.0.9
-----

-   added documentation
-   bugfixes
-   AjaxContentRenderer: support the content id in div, section and article

3.0.8
-----

-   updated documentation

3.0.7
-----

-   added Parsedown Extra class for more flexibility
-   made plugin USER

3.0.4 - 3.0.6
-----

-   bugfixes

3.0.3
-----

-   updated parsdown library from 1.1.1 to 1.6.0
-   bugfixes
-   added documentation

3.0.2
-----

-   add cHashExcludedParameters for common tracking tools
-   bugfixes

3.0.1
-----

-   deactivate Content Object Exception Handling if mktools Error Handling is active

3.0.0
-----

-   Initial TYPO3 8.7 LTS Support

2.0.2
-----

-   static action ts template renamed
-   converted manual to markdown

2.0.1
-----

-   added support for TYPO3 7.6
-   New FlashMessage feature implemented
-   check Typoscript cObject upon PagaNotFoundHandling

1.0.24
------

-   [BUGFIX] für XClass Registrierung in TYPO3 6.2

1.0.23
------

-   [BUGFIX] generate realurl file even if no pages with FixedPostVar type exist

1.0.20
------

-   [BUGFIX] execute tests not before TYPO3 6.2 of CLI task to find unused locallang labels

1.0.19
------

-   [FEATURE] CLI task to find unused locallang labels

1.0.18
------

-   [TASK] Responsive Images for MarkDown Action

1.0.16
------

-   [BUGFIX] fixed docu

1.0.15
------

-   [TASK] refactored database testcase and SEO Meta Robots Tag

1.0.13
------

-   [BUGFIX] libs fixed. using prefer dist instead of prefer source.


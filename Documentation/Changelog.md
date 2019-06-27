Changelog
=========

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


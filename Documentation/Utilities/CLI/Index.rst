.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. _cli_task:



Find unused locallang labels
============================

This CLI Task lists all locallang labels, that can't be found in the given folders except the locallang file itself.

Call it like:

typo3/cli_dispatch.phpsh mktools_find_unused_locallang_labels --locallangFile=typo3conf/ext/myext/locallang.xml --searchFolders=fileadmin,typo3conf/ext/myotherext...

You have to provide the path to the locallang file and the commaseparated folders to search in recursively.

Of course, as always you have to create a Backend user named _cli_mktools_find_unused_locallang_labels.

This task is only available in TYPO3 6.2 and upwards.



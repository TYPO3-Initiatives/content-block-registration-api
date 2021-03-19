.. include:: /Includes.rst.txt
.. _cb_definition:

============================
Definition of Content Blocks
============================

Localization of labels
======================

Labels for the editing interface, as well as frontend labels, are stored in the
`src/Language/Default.xlf`.
It is also possible to use different language files for frontend and backend by
providing a `src/Language/EditorInterface.xlf`
and a `src/Language/Frontend.xlf` instead.
The automated backend label detection in the registration process will look for
a `Default.xlf` first and, if not present, for an `EditorInterface.xlf` as well.

In general the `coding guidelines of the TYPO3 core for labels
<https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/
Internationalization/XliffFormat.html#xliff-id-naming>`__
are to be applied here as well. For backend labels that would be:
`<code-block-identifier>.<field-identifier>`.

Although the fields are not actual database fields it is recommended to use
the **snake_case** for the field identifier.
For frontend labels developers are free to use the structure they like,
although it is recommended to comply with the coding guidelines of the TYPO3 core.

Configuration of the editing interface
======================================

See :ref:`yaml_reference`.


Templating
----------

By default the content blocks installer expects a fluid template provided as
a `.html` file.
However, automated template engine detection via file ending (e.g. ".twig") is
possible as a future feature.


Data processing
---------------

An automated registration of data processors for certain field types (e.g. inline/
collection or images) is
targeted as a `feature <https://github.com/TYPO3-Initiatives/c
ontent-block-registration-api/issues/41>`__.

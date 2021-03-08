.. include:: /Includes.rst.txt
.. _yaml_reference:

Editing interface field types (YAML reference)
==============================================

The editing interface configuration only contains view related properties of the fields (unlike in TCA).
Therefore, a descriptive language (as YAML) is sufficient and does not open up a possible security flaw.

A strict schema for field types is used to ease up the validation process for field definitions.
To keep it slim and easy to read, the mapping to TCA uses strong defaults for field properties (e.g. default size for input is 30).

The field types for the EditorInterface.yaml are heavily inspired by the `Symfony field types <https://symfony.com/doc/current/reference/forms/types.html>`__
and will be mapped to TCA.
Because Symfony is quite mainstream, well-established and documented it makes it easier to understand those types for TYPO3 newcomers/ beginners/ frontend-only devs
than TYPO3's exclusive TCA, thus providing a kind of ubiquitous language.

.. note::
   With Symfony based field types the content blocks could even be integrated into a different CMS or database or file based system.


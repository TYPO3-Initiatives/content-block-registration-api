.. include:: /Includes.rst.txt
.. _faq:

===
FAQ
===

Why is the editing interface for Content Blocks defined in YAML? Why not just use TCA?
======================================================================================

There are several reasons:

*  Separation of a field's view related properties from it's database related properties
*  Simplification of the definition
*  Prevention of a possible security flaw
*  Opening up to TYPO3 newcomers/ beginners/ frontend-only devs

See :ref:`yaml_reference`.

.. attention::
   Currently there is a long term goal to refactor TCA, but it is unknown when
   this will happen. With Symfony based field types we would not have a breaking
   change in the configuration, but only “under the hood” then.


Switching the CType
===================

Switching the CType without having to re-enter the content is not possible with
different prefixes per CType. Perhaps a manual prefix could be defined so that,
for example, an agency can define all its own content blocks on its own
responsibility.

For now switching the CType without data loss won't be enabled. However,
if the survey for editors proves that it is necessary, a strategy for this will
be needed first.
A `discussion <https://decisions.typo3.org/t/
switchable-ctypes-how-to-solve-consistency-issues/660/2>`__ is currently open.


Compatibility with extensions
=============================

Introducing the content blocks package approach will be a breaking change. We
offer working together with the extension authors.

Localization of content
=======================

A field is localize-able by default, so setting the localization property is
only necessary if special localization method is required.

Can I reuse an existing field / column?
=======================

Yes you can. You can use the useExistingField property.

For example if you want to use the existing column "bodytext", or "header_layout" or "image" you can do one of the following:

.. code-block:: yaml

    group: common
    fields:
        -
            identifier: header_layout
            type: Select
            properties:
                useExistingField: true
        -
            identifier: bodytext
            type: Textarea
            properties:
                enableRichtext: true
                useExistingField: true
        -
            identifier: image
            type: Image
            properties:
                useExistingField: true



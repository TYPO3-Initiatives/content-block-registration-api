.. include:: /Includes.rst.txt
.. _cb_distribution:

==============================
Distribution of Content Blocks
==============================

Bundling
========

One composer package represents exactly one content block. Bundles can be
realized as distributions (e.g. like TYPO3 minimal distribution)
or within a bundling extension. This decision was made to reduce complexity,
anyway if it proves bad in test phase, it will have to be adopted.

Sharing assets across multiple content blocks
=============================================

Currently this is not possible. As this problem does not only refer to this
content block approach it won't be handled here.
However, it is #1 issue the Rendering Group of the Structured Content Initiative
is tackling with.
Check out the `github repository <https://github.com/TYPO3-Initiatives/
structured-asset-rendering>`__
and be welcome to help.

Overriding of content blocks
============================

To override existing content blocks you can choose between two methods:

**Variant a**: Duplicate declaration to new package

**Variant b**: Introduce inheritance (which makes maintenance more complex again)

Things like CSS, JS, HTML, XLF labels can be overridden via site extensions.
Due to complexity inheritance will be no option for now.
This might be discussed later again if the necessity proves unavoidable.

Availability on platforms
=========================

Unlike extensions the content blocks won't be available in the TYPO3 extension
repository (TER).
The main registry for content blocks is packagist.org. However, a distribution
platform similar to TER is in discussion.

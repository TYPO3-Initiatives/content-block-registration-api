.. include:: /Includes.rst.txt
.. _cb_package_files:

Directory structure of a content block
======================================

===============================  ==========  =============================
Directory / File                 Mandatory?  Could be created by generator
===============================  ==========  =============================
composer.json                    x           x
ContentBlockIcon.(svg/png/gif)   x           x
EditorInterface.yaml             x           x
src/Language/Default.xlf         x           x
src/EditorPreview.html           -           x
src/Frontend.html                -           x
dist/EditorPreview.css           -           x
dist/Frontend.css                -           x
dist/Frontend.js                 -           x
==============================  ============ ===============================


Content block package files explained
=====================================

composer.json
-------------

refers to: `Composer schema <https://getcomposer.org/doc/04-schema.md>`__

The content block ID (CType) derives from the package name. Therefore one composer package represents exactly one content block.

**You must**

*  provide this file
*  set the type property to: typo3-cms-contentblock

**You may**

*  use the full composer.json config and define autoloading for ViewHelpers etc.


EditorInterface.yaml
--------------------

refers to: `YAML RFC <https://github.com/yaml/summit.yaml.io/wiki/YAML-RFC-Index>`__

The content block ID (CType) derives from the package name. Therefore one composer package represents exactly one content block.

**You must**

*  provide this file
*  define the editor interface of exactly one content block
*  define all the fields and their position in the editing interface

The field types for the EditorInterface.yaml are heavily inspired by the `Symfony field types <https://symfony.com/doc/current/reference/forms/types.html>`__
and will be mapped to TCA. See :ref:`yaml_reference` for the mapping overview.


ContentBlockIcon.(svg|png|gif)
------------------------------

This is the icon for the content block. There is no fallback by intention, but it is easy to generate an SVG with the content block name as a graphical representation.

**You must**

*  provide this file
*  provide that file in the format svg or png or gif
*  provide a file with 1:1 dimensions


src/Language/Default.xlf
------------------------

**You may**

*  provide that file
*  define your labels with the XLF links in the configuration file

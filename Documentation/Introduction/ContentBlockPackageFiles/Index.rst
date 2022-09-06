.. include:: /Includes.rst.txt
.. _cb_package_files:

======================================
Directory structure of a content block
======================================

Although the classic structure of a TYPO3 extension is easier to understand for
TYPO3 developers, we chose to use the `Symfony <https://symfony.com/>`__
compliant **src/dist** structure instead. As TYPO3 uses more and more of Symfony
it could be beneficial, especially for people new to TYPO3, to get used to these
structures. Also, src/dist is more easy to understand by frontend developers as
this kind of naming is also common there.

+--------------------------------+------------+---------------------------------+
| Directory / File               | Mandatory? | Could be created by a generator |
+================================+============+=================================+
| composer.json                  |      x     |                x                |
+--------------------------------+------------+---------------------------------+
| ContentBlockIcon.(svg/png/gif) |      x     |                x                |
+--------------------------------+------------+---------------------------------+
| EditorInterface.yaml           |      x     |                x                |
+--------------------------------+------------+---------------------------------+
| src/Language/Default.xlf       |      x     |                x                |
+--------------------------------+------------+---------------------------------+
| src/EditorPreview.html         |            |                x                |
+--------------------------------+------------+---------------------------------+
| src/Frontend.html              |            |                x                |
+--------------------------------+------------+---------------------------------+
| dist/EditorPreview.css         |            |                x                |
+--------------------------------+------------+---------------------------------+
| dist/Frontend.css              |            |                x                |
+--------------------------------+------------+---------------------------------+
| dist/Frontend.js               |            |                x                |
+--------------------------------+------------+---------------------------------+


Content block package files explained
=====================================

composer.json
-------------

refers to: `Composer schema <https://getcomposer.org/doc/04-schema.md>`__

The content block ID (CType) derives from the package name. Therefore one
composer package represents exactly one content block.

**You must**

*  provide this file
*  set the type property to: `typo3-contentblock`

**You may**

*  use the full composer.json config and define autoloading for ViewHelpers etc.


EditorInterface.yaml
--------------------

refers to: `YAML RFC <https://github.com/yaml/summit.yaml.io/wiki/YAML-RFC-Index>`__

The content block ID (CType) derives from the package name. Therefore one
composer package represents exactly one content block.

**You must**

*  provide this file
*  define the editor interface of exactly one content block
*  define all the fields and their position in the editing interface

See :ref:`yaml_reference`.


ContentBlockIcon.(svg|png|gif)
------------------------------

This is the icon for the content block. There is no fallback by intention, but
it is easy to generate an SVG with the content block name as a graphical
representation.

**You must**

*  provide this file
*  provide that file in the format svg or png or gif
*  provide a file with 1:1 dimensions


src/Language/Default.xlf
------------------------

**You may**

*  provide that file
*  define your labels with the XLF links in the configuration file

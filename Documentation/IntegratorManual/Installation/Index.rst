.. include:: /Includes.rst.txt
.. _installation_use:

=====================================
Installation for using Content Blocks
=====================================

Requirements
============

*  TYPO3 v10+
*  In TYPO3 v10, backend previews require the `Fluid based Page module <https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView.html>`__ to be enabled.


Installation steps
==================

The API required to use content blocks consists of 2 composer packages: an API
extension and a composer plugin.

#. Run `composer req typo3-contentblocks/contentblocks-reg-api:dev-master`
#. Activate the extension `contentblocks_reg_api`
#. Add new database fields in your TYPO3 backend:
   `Maintenance` → `Analyze Database Structure`

Extension configuration
-----------------------

Sometimes it might be necessary to inherit content blocks from your ow
n definition (e. g. if you want to inherit content blocks from lib.contentElement).
In that case you can extend the default TypoScript as you need.
This is the default Code:

.. code-block:: typoscript

   lib.contentBlock = FLUIDTEMPLATE
   lib.contentBlock {
       layoutRootPaths {
           -5 = EXT:contentblocks_reg_api/Resources/Private/Layouts/
       }

       partialRootPaths {
           0 = EXT:contentblocks_reg_api/Resources/Private/Partials/
       }

       dataProcessing {
           10 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\CbProcessor
           20 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\FlexFormProcessor
       }
   }

.. attention::
   If you change the code, you are responsible for that the code is working.
   Beware of data processing. Your content block won't work without that.

You can inject your code via the extension settings in the install tool.
There you can set the default TypoScript to your specific file like
`EXT:sitepackage/Configuration/TypoScript/contentBlock.typoscript`.

The second thing in the extension configuration is to enable the frame pallet
in the appearance section. This might be a helpful feature if you are using
`EXT:fluid_styled_content` or `EXT:bootstrap_package`.

.. figure:: ./content_blocks_reg_api_ext_conf.png
   :alt: Extension configuration for EXT:contentblocks_reg_api
   :class: with-shadow
   :width: 700px

   Extension configuration for EXT:contentblocks_reg_api in "Configure Extensions"
   of backend module "Settings"

Creation/ registration of content blocks
========================================

Via composer
------------

Simply run `composer req typo3-contentblocks/<your-cb-package>:dev-master`

Example:
`composer req typo3-contentblocks/call-to-action:dev-master`

For using custom content blocks in your project we recommend a local "path"
composer repository.

Via GUI (Content Blocks Wizard)
-------------------------------

The registration API offers a simple GUI that helps you defining a content block.

Non-composer mode
-----------------

A content block can also be added manually in `typo3conf/contentBlocks` in
non-composer mode.

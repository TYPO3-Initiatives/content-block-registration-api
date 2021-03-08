.. include:: /Includes.rst.txt
.. _installation_use:

Installation for using Content Blocks
=====================================

Requirements
------------

*  TYPO3 v10+
*  In TYPO3 v10, backend previews require the `Fluid based Page module <https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.3/Feature-90348-NewFluid-basedReplacementForPageLayoutView.html>`__ to be enabled.


Installation steps
------------------

The API required to use content blocks consists of 2 composer packages: an API extension and a composer plugin.

#. Run ::`composer req typo3-contentblocks/contentblocks-reg-api:dev-master`
#. Activate the extension ::`contentblocks_reg_api`
#. Add new database fields in your TYPO3 backend: ::`Maintenance` › ::`Analyze Database Structure`

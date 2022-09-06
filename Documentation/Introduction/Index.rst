.. include:: /Includes.rst.txt
.. _introduction:

============
Introduction
============

   **Motivation:**

   Defining "Content Elements" in TYPO3 can be hard and the learning curve is
   steep. You need to learn PHP, TCA, TypoScript and Fluid and maybe other
   languages.

Therefore, this API provides an easy and reliable way to register content blocks.
A content block is defined as a small chunk of information, which is connected
to a view and then rendered in the TYPO3 frontend.

The configuration of a content block is reduced and simplified via abstraction
and convention. A content block is configured as a reusable standalone composer
package, that needs all its dependencies to be defined. This idea is heavily
inspired by the web components approach, even if here it is done on a different
level.


Storage of content block in the TYPO3 directory structure
=========================================================

Because each content block is described in a separate composer package, they
must define their type property as `typo3-contentblock`. TYPO3 then uses a
custom composer installer to place these packages in a dedicated location.

.. attention::
   **Composer package type changed!** Due to problems with the TYPO3 composer
   installer the required composer package type changed from `typo3-cms-contentblock`
   to `typo3-contentblock`. Please adjust it in your content block packages, when
   upgrading to version 3.0.0 and above.

To separate the working directories for “classic extensions” (plugins, …), usual
libraries and content blocks, the target folder is `typo3conf/contentBlocks/`.
This is also compatible with the local package repository approach you would
normally use if you ship packages, which are very specific to a single project.

Positive side effects of this approach
--------------------------------------

*  You can render the content blocks without having a complete TYPO3 installed yet.
*  You may reuse the content blocks in other projects.
*  You can define review rules in common VCS so that the frontend engineers
   are notified for changes.

**Further information**

.. toctree::
	:maxdepth: 3
	:titlesonly:

	ContentBlockPackageFiles/Index
	DataStorageVariants/Index
	RegistrationProcesses/Index

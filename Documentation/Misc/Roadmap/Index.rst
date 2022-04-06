.. include:: /Includes.rst.txt
.. _roadmap:

======================
Implementation Roadmap
======================

In order to speed up the development process, being able to get tester's
feedback faster and react on it, the implementation process is split into
following phases. Those phases represent a rough roadmap and may be adapted
during the development process.

Phase 1 (completed)
===================

*  Composer installer for Content Blocks
*  Validation of editing interface YAML
*  Extend tt_content with a field content_block
*  Use Flexforms and XML blob to store data
*  or eventually and individual use of the FormEngine
*  Generate TSConfig
*  Generate TypoScript


Phase 2 (dropped)
=================

This phase might be skipped. The decision depends on the research results for
data storage.

*  Use JSON blob instead of XML blob (`FlexForm storage method driver <https://review.typo3.org/c/Packages/TYPO3.CMS/+/53813>`__)

Phase 2 may, if deemed vital, be done as part of phase 1. The necessary patch is
nearly complete and can easily be adapted to, for example, writing a more
condensed JSON blob.

Phase 3 (dropped)
=================

*  Refactor Flexforms to freely organize fields in the editing interface
   (new feature, ability to extract field definitions from a DS and render them
   as part of the “showitems” instruction from TCA)

Phase 3 also could be created right now as a `feature request on forge <https://forge.typo3.org/projects/typo3cms-core/issues>`__.

Phase 4 (completed)
===================

*  Based on investigation of performance, decide on a different storage strategy
   for the data that (according to phase 1) is stored as blobs based on FlexForm
   fields.
*  FlexForm storage driver potentially allows EAV or flat table implementations
   (actual storage strategy is arbitrary)

.. note::
   After further evaluation we decided not to use blobs, but to extend `tt_content`
   and use a custom table for collections.

Phase 5
=======

Further development:
- Refactor the content blocks kickstart module
- Compatibility with TYPO3 v12

.. note::
    Have a look at our `milestones <https://github.com/TYPO3-Initiatives/content-block-registration-api/milestones3>`__
    to see which features are planned for which major version.

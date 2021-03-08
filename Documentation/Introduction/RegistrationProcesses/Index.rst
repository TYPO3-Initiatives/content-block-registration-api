.. include:: /Includes.rst.txt
.. _registration_processes:

Processes that happen during content block registration
=======================================================

Abstraction Requirements
------------------------

To achieve the goal of reducing the complexity of content block registration the `facade pattern <https://en.wikipedia.org/wiki/Facade_pattern>`__
approach needs to be used for some of TYPO3s internal APIs. These are

*  Validation
*  Mapping to the database
*  TCA generation for

   *  ext_tables.php
   *  Configuration/TCA/….
   *  registration of the icon in the CType field in TCA

*  Registration of the plugin to display the content for frontend rendering including DataProcessors
*  Registration of the icon in the new content element wizard (PageTS)
*  Configuration of the template path(s)
*  Registration for the preview in the backend


Processes in detail
-------------------

Detecting a content block
~~~~~~~~~~~~~~~~~~~~~~~~~

The detection of content blocks depends on the composer package type. The custom composer installer then retrieves all packages, which are of the above defined type.

Validating a content block
~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::
   **Not yet implemented!**
   Basically a YAML schema validation (based on JSON schema) is needed here. Exchange with the Form Framework team is targeted.


Mapping to the database
~~~~~~~~~~~~~~~~~~~~~~~

There are ref:`data_storage_variants:several variants` of how data of a content block can be stored and retrieved from the database.
Currently, there is no decision on the desired storage method, because performance research is still in progress.

Virtual generation of TCA (ext_tables.php)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Requirements:

*  Has to be after non override TCA loading
*  Has to be before the caching of the TCA
*  Has to be before merging the overrides for TCA

TCA is virtually generated from the class implementing a content block field type.

Generate registration of the plugin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Requirements:

*  Register icon
*  Add TCA entry in CTypes list including the icon
*  Register plugin in TYPO3
*  Add TypoScript to render the content plugin
*  Add PageTS for the content block

   *  Define where to display (group / location) the content block in the new content element wizard

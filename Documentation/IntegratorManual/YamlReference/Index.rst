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

Field definitions
-----------------

Common field properties
~~~~~~~~~~~~~~~~~~~~~~~
.. rst-class:: dl-parameters

identifier
   :sep:`|` :aspect:`Required:` true
   :sep:`|` :aspect:`Type:` string
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   The field's identifier has to be unique within a content block.

type
   :sep:`|` :aspect:`Required:` true
   :sep:`|` :aspect:`Type:` string
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   The field's type. See :ref:`field_types`.

properties
   :sep:`|` :aspect:`Required:` true
   :sep:`|` :aspect:`Type:` array
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Array of properties that are dependent on the :ref:`field_types`.


Field grouping
--------------

Visually grouping of fields by palettes in the editing interface is defined by the key `palettes` Example:

.. code-block:: yaml

   palettes:
      - identifier: palette_1
        label: palette_1
        fields:
        # …

See :ref:`yaml_reference`.

.. attention::
   **Not yet implemented!** See `feature request <https://github.com/TYPO3-Initiatives/content-block-registration-api/issues/22>`__

.. include:: /Includes.rst.txt
.. _field_type_collection:

==========
Collection
==========

The "Collection" type generates a field for Inline-Relational-Record-Editing
(IRRE), which allows nesting of other field types as children.
This field type allows building structures like image sliders, where properties
beyond the image meta fields are required per child item.

It corresponds with the TCA `type='inline'`.


Properties
==========

.. rst-class:: dl-parameters

fields
   :sep:`|` :aspect:`Required:` true
   :sep:`|` :aspect:`Type:` array
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Configures a set of fields as repeatable child objects. All fields defined in
   :ref:`field_types` are possible as children. However, consider not to have
   too many nested Collection fields to avoid performance issues. Content Blocks
   are not intended to represent complex data structures. Consider to create
   custom tables for these cases.

   Example:

   .. code-block:: yaml

      fields:
        - identifier: text
          type: Text
        - identifier: image
          type: Image

useAsLabel
   :sep:`|` :aspect:`Required:` true
   :sep:`|` :aspect:`Type:` array
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Defines which field of the collection item should be used as the title of the
   inline element. The given field has to be a string based field type, or at
   least be convertable to a string.

   Example:

   .. code-block:: yaml

      useAsLabel: text
      fields:
        - identifier: text
          type: Text
        - identifier: image
          type: Image

maxItems
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` integer
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Maximum number of child items. Defaults to a high value. JavaScript record
   validation prevents the record from being saved if the limit is not satisfied.

minItems
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` integer
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Minimum number of child items. Defaults to 0. JavaScript record validation
   prevents the record from being saved if the limit is not satisfied.

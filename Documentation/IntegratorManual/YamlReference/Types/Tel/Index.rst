.. include:: /Includes.rst.txt
.. _field_type_tel:

Tel
===

The "Tel" type generates a simple `<input>` field, which handles phone numbers.

It corresponds with the TCA `type='input'` (default), however special variants are defined as own field types.


Properties
----------

.. rst-class:: dl-parameters

autocomplete
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` 'false'
   :sep:`|`

   If set, the autocomplete feature is enabled for this field.

default
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` integer
   :sep:`|` :aspect:`Default:` 0
   :sep:`|`

   Default value set if a new record is created.

required
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` 'false'
   :sep:`|`

   If set, the field will become mandatory.

size
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` integer
   :sep:`|` :aspect:`Default:` '20'
   :sep:`|`

   Abstract value for the width of the `<input>` field.

trim
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` 'false'
   :sep:`|`

   If set, the PHP trim function is applied on the field's content.

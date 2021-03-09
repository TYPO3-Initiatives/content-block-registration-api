.. include:: /Includes.rst.txt
.. _field_type_number:

Number
======

The "Number" type generates a simple `<input>` field, which allows only 0-9 characters in the field.

It corresponds with the TCA `type=’input’` (default) and `eval=´num´`.


Properties
----------

.. rst-class:: dl-parameters

default
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` double
   :sep:`|` :aspect:`Default:` 0
   :sep:`|`

   Default value set if a new record is created.

size
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` integer
   :sep:`|` :aspect:`Default:` '20'
   :sep:`|`

   Abstract value for the width of the `<input>` field.

required
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` 'false'
   :sep:`|`

   If set, the field will become mandatory.

trim
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` 'false'
   :sep:`|`

   If set, the PHP trim function is applied on the field's content.

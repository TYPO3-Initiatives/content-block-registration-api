.. include:: /Includes.rst.txt
.. _field_type_number:

======
Number
======

The "Number" type generates a simple `<input>` field, which allows only 0-9
characters in the field.

It corresponds with the TCA `type='input'` (default) and `eval='num'`.


Properties
==========

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

range
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` array
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   An array which defines an integer range within which the value must be. Keys:

   lower (integer)
      Defines the lower integer value. Default: 0.

   upper (integer)
      Defines the upper integer value. Default: none.

   It is allowed to specify only one of both of them.

   Example:

   .. code-block:: yaml

      range:
        lower: 10
        upper: 999

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

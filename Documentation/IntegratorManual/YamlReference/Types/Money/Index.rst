
.. include:: /Includes.rst.txt
.. _field_type_money:

Money
=====

The "Money" type generates a simple `<input>` field, which converts the input to a floating point with 2 decimal positions, using the “.” (period) as the decimal delimited (accepts also “,” for the same).

It corresponds with the TCA `type='input'` (default) and `eval='double2'`.


Properties
----------

.. rst-class:: dl-parameters

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

.. include:: /Includes.rst.txt
.. _field_type_url:

===
Url
===

The "Url" type generates a simple `<input>` field, which handles different kinds
of links.

It corresponds with the TCA `type='input'` (inputLink).


Properties
==========

.. rst-class:: dl-parameters

autocomplete
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` 'false'
   :sep:`|`

   If set, the autocomplete feature is enabled for this field.

default
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` string
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Default value set if a new record is created.

linkPopup
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` array
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   The link browser control is typically used with `type='input'` with
   `renderType='inputLink'` adding a button which opens a popup to select an
   internal link to a page, an external link or a mail address.

   allowedExtensions (string, list)
      Comma separated list of allowed file extensions. By default, all extensions
      are allowed.

   blindLinkFields (string, list)
      Comma separated list of link fields that should not be displayed. Possible
      values are `class`, `params`, `target` and `title`. By default, all link
      fields are displayed.

   blindLinkOptions (string, list)
      Comma separated list of link options that should not be displayed. Possible
      values are `file`, `folder`, `mail`, `page`, `spec`, `telephone` and `url`.
      By default, all link options are displayed.

   windowOpenParameters (string)
      Allows to set a different size of the popup, defaults to
      `height=800,width=600,status=0,menubar=0,scrollbars=1`.

   Example:

   .. code-block:: yaml

      linkPopup:
        allowedExtensions: 'pdf'
        blindLinkFields: 'target,title'
        blindLinkOptions: 'folder,spec,telefone,mail'
        windowOpenParameters: 'height=800,width=600'

max
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` integer
   :sep:`|` :aspect:`Default:` '700'
   :sep:`|`

   Value for the “maxlength” attribute of the `<input>` field. Javascript
   prevents adding more than the given number of characters.

placeholder
   :sep:`|` :aspect:`Required:` false
   :sep:`|` :aspect:`Type:` string
   :sep:`|` :aspect:`Default:` ''
   :sep:`|`

   Placeholder text for the field.

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

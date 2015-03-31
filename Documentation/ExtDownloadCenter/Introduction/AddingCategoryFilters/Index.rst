.. include:: Images.txt

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Adding Category filters
^^^^^^^^^^^^^^^^^^^^^^^

The download manager category is not a sys\_category provided by
typo3; this is because an independent category relation is more
preferred in this case. For this purpose typo3 FAL has been extended
with few new fields that will be made available in the download
manager tab in backend when user edits a FAL asset. See screen below;

|img-5| The new category type can be created in web → list view as Download
center categories where a title can be added and parent category can
be selected which will be rendered as dropdown filters in front end.

|img-6| |img-7| 
Adding File type filters
^^^^^^^^^^^^^^^^^^^^^^^^

The file type data can be added similar to categories through Web →
List in backend. Only a title can be provided which will be available
as multiple select item inside the media FAL item.

|img-8| |img-9|


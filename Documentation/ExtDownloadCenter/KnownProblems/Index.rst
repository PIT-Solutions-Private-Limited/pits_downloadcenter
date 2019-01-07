

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


Known problems
--------------
-  Category filter (url copied) not retained all other filters are working fine
-  There seems only one constraint that on initial page load there will
be slight performance hiccup while fetching large number of files >
4000.
-  It is also recommended that the flex form setting "Show Preview Thumb" should be disabled if number of files > 500.
-  From 3.0.0 onwards template extending can be only done inside the Angular Component in `Private/UI/src/app/download-center/download-center.component.html`
-  Once the template is altered in the component template you need to take the production angular build using the command `ng build --prod`
-  Copy the main.xxx js from the dist folder to the Public script folder path and assign that file to the TYPOSCRIPT path

If you find any issues please report in https://forge.typo3.org/projects/extension-pits_downloadcenter.





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


Configuration
-------------

The baseURL has to be set correctly for javascript processing of the
plugin. The frontend view can be adjusted by using the templates
List.html inside Resources/Private/Templates/Download/.

The styles can be customized in Resources/Public/Styles/theme.css
which is loaded through typoscript. Also a font theme css is also
loaded by default when the plugin uses angular as well as jquery to function well in frontend; the main javascript files are present inside
/Resources/Public/Scripts/js/ which is loaded through typoscript.


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   ConfiguringTheme/Index
   Reference/Index



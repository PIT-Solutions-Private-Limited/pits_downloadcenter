

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


Reference
^^^^^^^^^

::


   plugin.tx\_pitsdownloadcenter {

   view {

   templateRootPath =
   {$plugin.tx\_pitsdownloadcenter.view.templateRootPath}

   partialRootPath =
   {$plugin.tx\_pitsdownloadcenter.view.partialRootPath}

   layoutRootPath =
   {$plugin.tx\_pitsdownloadcenter.view.layoutRootPath}

   }

   persistence {

   storagePid =
   {$plugin.tx\_pitsdownloadcenter.persistence.storagePid}

   }
   settings{
   showFileIconPreview = {$plugin.tx\__pitsdownloadcenter.settings.showFileIconPreview}		
   }
   }

   plugin.tx\_pitsdownloadcenter.\_CSS\_DEFAULT\_STYLE (

   textarea.f3-form-error {

   background-color:#FF9F9F;

   border: 1px #FF0000 solid;

   }

   input.f3-form-error {

   background-color:#FF9F9F;

   border: 1px #FF0000 solid;

   }

   .tx-pits-downloadcenter table {

   border-collapse:separate;

   border-spacing:10px;

   }

   .tx-pits-downloadcenter table th {

   font-weight:bold;

   }

   .tx-pits-downloadcenter table td {

   vertical-align:top;

   }

   .typo3-messages .message-error {

   color:red;

   }

   .typo3-messages .message-ok {

   color:green;

   }

   )

   page.includeCSS {

   file70001
   =EXT:pits\_downloadcenter/Resources/Public/Styles/theme.css

   file70001.title = Default CSS

   file70001.media = screen

   file70002=http://maxcdn.bootstrapcdn.com/font-
   awesome/4.3.0/css/font-
   awesome.min.css

   file70002.external=1

   }

   page.includeJS {

   file70001 =
   EXT:pits\_downloadcenter/Resources/Public/Scripts/angular/
   angular.min.js

   file70001.type = text/javascript

   file70002 =
   EXT:pits\_downloadcenter/Resources/Public/Scripts/js/paginator.js

   file70002.type = text/javascript

   file70003 =
   EXT:pits\_downloadcenter/Resources/Public/Scripts/js/jquery.js

   file70003.type = text/javascript

   file70004 =
   EXT:pits\_downloadcenter/Resources/Public/Scripts/js/downl
   oad\_center\_main.js

   file70004.type = text/javascript

   }

   config.tx\_extbase.persistence.classes {

   PITS\PitsDownloadcenter\Domain\Model{

   tableName =
   tx\_pitsdownloadcenter\_domain\_model\_categoryrecordmm

   columns {

   uid\_local.mapOnProperty = uidLocal

   uid\_foreign.mapOnProperty = uidForeign

   }

   }

   }


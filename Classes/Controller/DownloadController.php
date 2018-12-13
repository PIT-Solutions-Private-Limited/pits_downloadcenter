<?php
namespace PITS\PitsDownloadcenter\Controller;

use PITS\PitsDownloadcenter\Handlers\ContentTypeHandler;
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 HOJA <hoja.ma@pitsolutions.com>, PIT Solutions Pvt Ltd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * DownloadController
 */
class DownloadController extends AbstractController {
    
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $config = $this->settings;
        $transilations = $this->getPageTranslations();
        $filetypesObject = $this->filetypeRepository->findAll();
        $fileTypes = $this->getFileTypes( $filetypesObject );
        $categoryTree = $this->doGetSubCategories(0);
        $storageuid = $this->settings['fileStorage'];
        $storageRepository = $this->storageRepository->findByUid($storageuid);
        if( $storageRepository instanceof \TYPO3\CMS\Core\Resource\ResourceStorage )
        {
            $storageConfiguration   = $storageRepository->getConfiguration();
        }
        else{
            $error_code = 503;
            $this->request->forward('error', NULL, NULL, $error_code );
        }
        $basePath = $storageConfiguration['basePath'];
        // Stop Execution if the path selected is fileadmin  
        $isValid = ( $basePath === "fileadmin/" ) ? FALSE : TRUE;
        $showPreview = ( $config['showthumbnail'] == 1 ) ? TRUE : FALSE;
        if( $isValid ){
            $baseUrl = $this->request->getBaseUri();
            //You can also set an array of arguments if you need to:
            $pageUid    =   $GLOBALS['TSFE']->id;
            //Uri for JSON Call
            $urlArguments = array(
                                array(
                                    'tx_pitsdownloadcenter_pitsdownloadcenter' => 
                                    array(
                                        'controller' => 'Download',
                                        'action' => 'show',
                                    )
                                )
                            );
            $actionUrl = $this->uriBuilder->reset()->setTargetPageUid( $pageUid )->setCreateAbsoluteUri( TRUE )->setArguments( $urlArguments )->build();
            $filePreview = ( $config['showFileIconPreview'] == 1 ) ? TRUE : FALSE;             
            $this->view->assign( 'baseURL' , $baseUrl );
            $this->view->assign( 'actionUrl' , $actionUrl );
            $this->view->assign( 'downloadUrl' , $downloadUrl );
            $this->view->assign( 'basePath'  , $basePath );
            $this->view->assign( 'showPreview', $showPreview );
            $this->view->assign( 'showFileIcon',$filePreview );
        }
        else{
            $this->view->assign('showError',TRUE);
        }
    }

    /**
     * action show
     *
     * 
     * @return void
     */ 
    public function showAction()
    {
        ini_set( 'memory_limit', '-1' );
        $config = $this->settings;     
        $translations = $this->getPageTranslations();
        $fileTypesObject = $this->filetypeRepository->findAll();
        $fileTypes = $this->getFileTypes( $fileTypesObject );
        $categoryTree = $this->doGetSubCategories(0);
        $storageUid = $this->settings['fileStorage'];
        $showPreview = ($config['showthumbnail'] == 1) ? TRUE : FALSE;
        $allowDirectLinkDownlod = ($config['allowDirectLinkDownload'] == 1) ? TRUE : FALSE;
        $storageRepository = $this->storageRepository->findByUid( $storageUid );
        $storageConfiguration = $storageRepository->getConfiguration();
        $folder = new \TYPO3\CMS\Core\Resource\Folder($storageRepository, '', '');
        $getFiles = $storageRepository->getFilesInFolder($folder, $start = 0, $maxNumberOfItems = 0, $useFilters = TRUE, $recursive = TRUE);
        $basePath = $storageConfiguration['basePath'];
        $files = $this->generateFiles($getFiles, $showPreview, $allowDirectLinkDownlod, $basePath);
        $baseUrl = $this->request->getBaseUri();
        $response = array(
            'baseURL' => $baseUrl ,
            'files' => $files,
            'categories' => $categoryTree,
            'types' => $fileTypes,
            'config' => $config,
            'translations' => $translations
        );
        echo json_encode( $response );exit;
    }

    /**
     * force download PHP Script
     * @void 
     */
    public function forceDownloadAction()
    {
        $encrypted_fileID = ( $this->request->hasArgument('fileid'))?$this->request->getArgument('fileid'):0;
        $fileID= openssl_decrypt( base64_decode( $encrypted_fileID ) , $this->encryptionMethod, $this->encryptionKey , TRUE , $this->initializationVector );
        if( is_numeric($fileID)) {
            $storageuid = $this->settings['fileStorage'];
            $fileDetails = $this->downloadRepository->getFileDetails( $storageuid , $fileID  );
            $fileIdentifier = ( isset($fileDetails['identifier']) ) ? $fileDetails['identifier'] : FALSE;
            $storageRepository  = $this->storageRepository->findByUid( $storageuid );
            $sConfig = $storageRepository->getConfiguration();
            $fileName = (isset($fileDetails['name']))?$fileDetails['name']:NULL;
            $file = realpath( PATH_site.$sConfig['basePath'].$fileIdentifier ); 
            $fileObject = $storageRepository->getFile( $fileIdentifier );
            $sys_language_uid = $GLOBALS['TSFE']->sys_language_uid;
            $checkTranslations = $this->downloadRepository->checkTranslations( $fileObject , $sys_language_uid );
            if( $checkTranslations ){
                $file_identifier = isset($checkTranslations['translated_file'])?$checkTranslations['translated_file']:NULL;
                if ($file_identifier && !empty( $file_identifier )){
                    $filepath = PATH_site.$file_identifier;
                    if (!empty($filepath) && is_file($filepath)){
                        $file = $filepath;
                        $fileName = basename( $file );
                    }
                }
            }
            if(is_file($file)) {
                $fileLen    = filesize($file);          
                $ext        = strtolower(substr(strrchr($fileName, '.'), 1));
                $cType = ContentTypeHandler::getContentType( $ext );
                $headers = array(
                    'Pragma'                    => 'public', 
                    'Expires'                   => 0, 
                    'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
                    'Cache-Control'             => 'public',
                    'Content-Description'       => 'File Transfer',
                    'Content-Type'              => $cType,
                    'Content-Disposition'       => 'attachment; filename="'. $fileName .'"',
                    'Content-Transfer-Encoding' => 'binary', 
                    'Content-Length'            => $fileLen         
                );
                foreach($headers as $header => $data)
                $this->response->setHeader($header, $data); 
                $this->response->sendHeaders();                 
                @readfile($file);   
            }
        }
        else{
            echo "Invalid Access!";
        }   
        exit;
    }
}

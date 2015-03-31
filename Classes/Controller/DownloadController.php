<?php
namespace PITS\PitsDownloadcenter\Controller;
use TYPO3\CMS\Core\Resource\Collection\FolderBasedFileCollection;
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
class DownloadController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
    /**
     * downloadRepository
     *
     * @var \PITS\PitsDownloadcenter\Domain\Repository\DownloadRepository
     * @inject
     */
    protected $downloadRepository = NULL;

    /**
     * filelistRepository
     *
     * @var \PITS\PitsDownloadcenter\Domain\Repository\FiletypeRepository
     * @inject
     */
    protected $filetypeRepository = NULL;

    /**
     * categoryRepository
     *
     * @var \PITS\PitsDownloadcenter\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoryRepository = NULL;

    /**
     * categorymmRepository
     *
     * @var \PITS\PitsDownloadcenter\Domain\Repository\CategoryrecordmmRepository
     * @inject
     */
    protected $categorymmRepository = NULL;
    
	
    /**
     * action list
     *
     * @return void
     */
    public function listAction() {
	   	$config 		= $this -> settings;
        $transilations 	= $this -> getPageTranslations();
        $filetypesObject= $this -> filetypeRepository -> findAll();
        $fileTypes  	= $this->getFileTypes( $filetypesObject );
        $categoryTree 	= $this->doGetSubCategories(0);
        $storageuid 	= $this->settings['fileStorage'];
        /**
         * @var $storageRepository \TYPO3\CMS\Core\Ressources\StorageRepository 
         **/
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::
        						makeInstance(
        						'TYPO3\\CMS\\Core\\Resource\\StorageRepository' 
        					);
        $storageRepository 		= $storageRepository->findByUid($storageuid);
        $storageConfiguration 	= $storageRepository->getConfiguration();
        $basePath				= $storageConfiguration['basePath'];
        // Stop Execution if the path selected is fileadmin  
        $isValid	= ($basePath === "fileadmin/")?FALSE:TRUE;
        $showPreview= ($config['showthumbnail'] == 1)?TRUE:FALSE;
        if($isValid){
        	$baseUrl    = 	$GLOBALS['TSFE']->baseUrl;
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
        	$actionUrl  = $this	->uriBuilder->reset()
        						->setTargetPageUid($pageUid)
        						->setCreateAbsoluteUri(TRUE)
        						->setArguments($urlArguments)
        						->build();
        	$this->view->assign('baseURL' , $baseUrl );
        	$this->view->assign('actionUrl' , $actionUrl );
        	$this->view->assign('basePath' 	, $basePath);
        	$this->view->assign('showPreview', $showPreview);
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
    public function showAction() {
        ini_set('memory_limit', '-1');
        $config = $this -> settings;
        $transilations = $this -> getPageTranslations();
        $filetypesObject = $this -> filetypeRepository -> findAll();
        $fileTypes  =   $this->getFileTypes( $filetypesObject );
        $categoryTree = $this -> doGetSubCategories(0);
        $storageuid = $this->settings['fileStorage'];
        $showPreview= ($config['showthumbnail'] == 1)?TRUE:FALSE;
        /**
         * @var $storageRepository \TYPO3\CMS\Core\Ressources\StorageRepository 
         **/
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        		'TYPO3\\CMS\\Core\\Resource\\StorageRepository'
        );
        $storageRepository 		= $storageRepository->findByUid($storageuid);
        $storageConfiguration 	= $storageRepository->getConfiguration();
        $folder =  new \TYPO3\CMS\Core\Resource\Folder(
        			$storageRepository,'',''
        		);
        $getfiles = $storageRepository->getFilesInFolder( $folder );
		
        $basePath = $storageConfiguration['basePath'];
        $files = $this -> generateFiles(
        					$getfiles , $basePath ,$showPreview
        		);
        $baseUrl = 	$GLOBALS['TSFE']->baseUrl;
        $response = array(
        				'baseURL' => $baseUrl ,
        				'files' => $files, 
        				'categories' => $categoryTree, 
        				'types' => $fileTypes,
        				'config' => $config, 
        				'transilations' => $transilations
			        );
        echo json_encode( $response );
        exit;
    }

   	
    /**
     * generate subcategories and return category tree
     *
     * @return array
     * @author
     **/
    public function doGetSubCategories($parentID) {
        $categoryTree = array();
        $subCategories = $this 	-> categoryRepository 
        						-> getSubCategories($parentID);
        $i = 0;
        foreach ($subCategories as $key => $value) {
            $catID = $value -> getUid();
            $catName = $value -> getCategoryname();
            $categoryTree[$key]['id'] = $catID;
            $categoryTree[$key]['title'] = $catName;

            $has_sub = NULL;
            $has_sub = $this-> categoryRepository 
            				-> getSubCategoriesCount($catID);
            if ($has_sub) {
                $categoryTree[$key]['input'] = $this->doGetSubCategories($catID);
            }
            $i++;
        }
        return $categoryTree;
    }

     /**
     * Function for structured file result
     *
     * @return structured array
     **/
    public function generateFiles($fileObject , $basePath , $showPreview ) {
    	$response = array();
        $pImgWidth                          = "150m";
        $pImgHeight                         = "150m";
        $processType                        = "Image.CropScaleMask";
        $i=0;
        foreach ($fileObject as $key => $value) {
        	$key = $i++;
            $fileProperty          = $value -> getProperties();
            $response[$key]['id']  = (int)$fileProperty['uid'];
            $response[$key]['url'] =  'fileadmin' . urlencode($fileProperty['identifier']);
            $response[$key]['title'] = (!empty($fileProperty['title'])) 	? $fileProperty['title'] : $value->getNameWithoutExtension();
            $response[$key]['url']   =  $basePath . $fileProperty['identifier'];
            $response[$key]['size']  = $this -> formatBytes($fileProperty['size']);
            $response[$key]['fileType'] = $fileProperty['extension'];
            $response[$key]['dataType'] = ($fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'] !=0 && $fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'] != NULL )?explode(',', $fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype']):array();
            $response[$key]['categories']   = ($fileProperty['tx_pitsdownloadcenter_domain_model_download_category'] !=0 && $fileProperty['tx_pitsdownloadcenter_domain_model_download_category'] != NULL )?explode(',', $fileProperty['tx_pitsdownloadcenter_domain_model_download_category']):array();
            if( $showPreview ){
            	$response[$key]['processed']    = $this->processImage($response[$key]['url'], $response[$key]['title'], $pImgWidth, $pImgHeight);
            	$fileProcessingConf             = $this->downloadRepository->getProcessedFile($value) ;
            	$processedFileConf              = $fileProcessingConf->getProperties() ;
           		$response[$key]['imageUrl'] 	= $processedFileConf['identifier'] == '' ?  'typo3conf/ext/pits_downloadcenter/Resources/Public/Icons/noimage.jpg' : $basePath.$processedFileConf['identifier'];
            }
            $idRel 							= $fileProperty['tx_pitsdownloadcenter_domain_model_download_category'];
        }
        return $response;
    }

    /**
     * Processed Images
     *
     * @return Image
     **/
    public function processImage($file, $title, $size_w, $size_h) {
        $cObj           = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
        $imgTSConfig    = array();
        $imgTSConfig['file'] = $file;
        $imgTSConfig['file.']['width'] = $size_w;
        $imgTSConfig['file.']['height'] = $size_h;
        $imgTSConfig['altText'] = empty($title) ? 'preview' : $title;
        $imgTSConfig['titleText'] = empty($title) ? 'preview' : $title;
        return $cObj->IMG_RESOURCE($imgTSConfig);
    }

       /**
     * Function Returns FileTypes
     *
     * @return array [object]
     **/
    public function getFileTypes( $filetypesObject ){
        $response = array();
        foreach ($filetypesObject as $key => $value) {
            $response[$key]['id']  =   $value->getUid();
            $response[$key]['title']  =   $value->getFiletype();
        }
        return $response;
    }

    /**
     * Function Returns the Page Translations
     *
     * @return array
     **/
    public function getPageTranslations() {
        $translatedValue = array();
        $translatedValue['keywordsearch'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.keywordsearch");
        $translatedValue['searchkey'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.searchkey");
        $translatedValue['filterbyarea'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.filterbyarea");
        $translatedValue['categoryplaceholder'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.categoryplaceholder");
        $translatedValue['searchbytype'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.searchbytype");
        $translatedValue['resultsfound'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.resultsfound");
        $translatedValue['tabletitle'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.tabletitle");
        $translatedValue['tablesize'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.tablesize");
        $translatedValue['tabletype'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.tabletype");
        $translatedValue['tabledownload'] = $this -> localise("tx_pitsdownloadcenter_domain_model_download.tabledownload");
        return $translatedValue;
    }

    /*
     * Localisation Function
     */
    public function localise($id) {
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($id, 'PitsDownloadcenter');
    }

    /*
     * 	Size Convertion Function
     */
    public function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

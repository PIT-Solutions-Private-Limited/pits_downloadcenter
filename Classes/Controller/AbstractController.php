<?php
namespace PITS\PitsDownloadcenter\Controller;

use TYPO3\CMS\Core\Resource\Collection\FolderBasedFileCollection;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceStorage;
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
 * AbstractController
 */
abstract class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
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
     * @var array
     */
    protected $extConf = array();

    /**
     * @var string
     */
    protected $isLogin = NULL;

    /**
     * contains the ts settings for the current action
     *
     * @var array
     */
    protected $actionSettings = array();

    /**
     * contains the specific ts settings for the current controller
     *
     * @var array
     */
    protected $controllerSettings = array();

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager = NULL;

    /**
     * @var int
     */
    protected $currentPageUid;

    /**
     * @var string
     */
    protected $encryptionKey; 

    /**
     * @var string
     */
    protected $encryptionMethod;

    /**
     * @var string
     */
    protected $initializationVector;

    /**
     * @var string
     * extensionName
     */
    protected $extensionName;

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
     * storageRepository
     *
     * @var \TYPO3\CMS\Core\Resource\StorageRepository 
     * @inject
     */
    protected $storageRepository = NULL;

    /**
     * Initializes the controller before invoking an action method.
     *
     * Override this method to solve tasks which all actions have in
     * common.
     *
     * @return void
     */
    protected function initializeAction() {
        parent::initializeAction();
        $this->extensionName = $this->request->getControllerExtensionName();
        $this->dateTime = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName)]);
        $this->initializationVector = $this->strToHex("12345678");

        $this->encryptionKey = isset( $this->extConf['secure_encryption_key'] )?$this->extConf['secure_encryption_key']:NULL;
        $this->encryptionMethod = isset( $this->extConf['secure_encryption_method'] )?$this->extConf['secure_encryption_method']:NULL;
        $this->controllerSettings = $this->settings['controllers'][$this->request->getControllerName()]; 
        $this->actionSettings = $this->controllerSettings['actions'][$this->request->getControllerActionName()];
        $this->currentPageUid = $GLOBALS['TSFE']->id;
        $this->configurationManager = $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
    }

    /**
     * Initializes the view before invoking an action method.
     *
     * Override this method to solve assign variables common for all actions
     * or prepare the view in another way before the action is called.
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     * @return void
     */
    protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
        parent::initializeView($view);
        $this->view->assignMultiple(array(
            'controllerSettings' => $this->controllerSettings,
            'actionSettings' => $this->actionSettings,
            'extConf' => $this->extConf,
            'currentPageUid' => $this->currentPageUid
        ));
    }

    function strToHex($string){
	    $hex = '';
	    for ($i=0; $i<strlen($string); $i++){
	        $ord = ord($string[$i]);
	        $hexCode = dechex($ord);
	        $hex .= substr('0'.$hexCode, -2);
	    }
	    return strToUpper($hex);
	}

    /**
     * generate subcategories and return category tree
     *
     * @return array
     * @author
     **/
    public function doGetSubCategories($parentID) {
        $categoryTree = array();
        $subCategories = $this  -> categoryRepository 
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
        $pImgWidth = "150m";
        $pImgHeight = "150m";
        $processType = "Image.CropScaleMask";
        $i = 0;
        $pageUid = $GLOBALS['TSFE']->id;
        foreach ($fileObject as $key => $value) {
            if ( $value instanceof \TYPO3\CMS\Core\Resource\File) {
                $key = $i++;
                $fileProperty = $value -> getProperties();
                $response[$key]['id']  = (int)$fileProperty['uid'];
                $response[$key]['url'] =  'fileadmin' . urlencode($fileProperty['identifier']);
                $response[$key]['title'] = (!empty($fileProperty['title']))     ? $fileProperty['title'] : $value->getNameWithoutExtension();
                $response[$key]['size']  = $this -> formatBytes($fileProperty['size']);
                $response[$key]['fileType'] = strtoupper($fileProperty['extension']);
                $response[$key]['extension'] = $fileProperty['extension'];
                $response[$key]['dataType'] = ($fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'] !=0 && $fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'] != NULL )?explode(',', $fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype']):array();
                $response[$key]['categories']   = ($fileProperty['tx_pitsdownloadcenter_domain_model_download_category'] !=0 && $fileProperty['tx_pitsdownloadcenter_domain_model_download_category'] != NULL )?explode(',', $fileProperty['tx_pitsdownloadcenter_domain_model_download_category']):array();
                if( $showPreview ){
                    $processed                      = $this->processImage($value,$response[$key]['url'], $response[$key]['title'], $pImgWidth, $pImgHeight);
                    $response[$key]['imageUrl']     = ($processed == '' || !file_exists($processed))?  'typo3conf/ext/pits_downloadcenter/Resources/Public/Icons/noimage.jpg' : $processed;
                }
                $idRel = $fileProperty['tx_pitsdownloadcenter_domain_model_download_category'];
                $file_uid_secure = base64_encode(openssl_encrypt( $fileProperty['uid'] , $this->encryptionMethod, $this->encryptionKey , TRUE , $this->initializationVector ));
                $downloadArguments = array(
                                        array(
                                            'tx_pitsdownloadcenter_pitsdownloadcenter' => array(
                                                'controller' => 'Download',
                                                'action' => 'forceDownload',
                                                'fileid' => $file_uid_secure
                                            ),
                                            'no_cache' => 1
                                        )
                                    );
                $response[$key]['downloadUrl']= $this->uriBuilder->reset()->setTargetPageUid($pageUid)->setCreateAbsoluteUri(TRUE)->setArguments($downloadArguments)->setNoCache (TRUE)->build();
            }
        }
        return $response;
    }

    /**
     * Processed Images
     *
     * @return Image
     **/
    public function processImage($fileObj,$file, $title, $size_w, $size_h) {
        $cObj           = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $file = urldecode( $file );
        $response   =   $cObj->IMG_RESOURCE( array(
                            'file.'=>array('treatAsReference'=>1, 'width'=>$size_w, 'height'=>$size_h, ),
                            'file' => $fileObj->getUid()
                            )
                        );
        return $response;
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
     *  Size Convertion Function
     */
    public function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
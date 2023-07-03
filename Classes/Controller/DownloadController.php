<?php
namespace PITS\PitsDownloadcenter\Controller;

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

use PITS\PitsDownloadcenter\Handlers\ContentTypeHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use Psr\Http\Message\ResponseInterface;

/**
 * DownloadController
 *
 * @todo change to ajax url
 * @todo merge angular 5 fixes
 * @todo unit test the fixes made in angular 5
 * @todo code cleanup and fine tuning
 * @todo fix the trailing slash issues in storage mount url
 */
class DownloadController extends AbstractController
{
    /**
     * typeNumConstant
     *
     * @var integer
     */
    protected $typeNumConstant = null;

    /**
     * Typo3 version
     *
     * @var integer
     */
    protected $typo3Version = null;
    

    /**
     * initialize Action
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function initializeListAction()
    {
        // forward to ajax handler service if typeNum set in url
        $this->checkServiceCalledRoute();
        $typo3VersionObj = GeneralUtility::makeInstance(Typo3Version::class);
        $this->typo3Version = $typo3VersionObj->getVersion();
    }

    /**
     * listAction
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function listAction(): ResponseInterface
    {
        $config = $this->settings;
        $storageUid = $this->settings['fileStorage'];
        $storageRepository = $this->storageRepository->findByUid($storageUid);
        if( $storageRepository instanceof \TYPO3\CMS\Core\Resource\ResourceStorage )
        {
            $storageConfiguration   = $storageRepository->getConfiguration();
        } else{
            $this->redirectTo404();
        }
        $basePath = $storageConfiguration['basePath'];

        // Stop Execution if the path selected is fileadmin
        // this is intentionally done because we dont want to show all the default
        // FAL layer files to frontend
        $isValid = ( $basePath === "fileadmin/" ) ? FALSE : TRUE;
        $showPreview = ( $config['showthumbnail'] == 1 ) ? TRUE : FALSE;

        // Check Starts Here
        if($isValid) {
            $request = $GLOBALS['TYPO3_REQUEST'];
            $normalizedParams = $request->getAttribute('normalizedParams');
            $baseUrl = $normalizedParams->getSiteUrl();
            // uri for JSON service
            //if default language contentIdentifier = uid else _LOCALIZED_UID to get settings of translated plugin in ajax action
            $cObject = $this->configurationManager->getContentObject()->data;
            $contentIdentifier = ($cObject['_LOCALIZED_UID']) ? $cObject['_LOCALIZED_UID'] : $cObject['uid'];
            $urlArguments = [
                'type'  => intval(preg_replace('/[^A-Za-z0-9\-]/', '', $this->settings['typeNum'])),
                'contentIdentifier' => $contentIdentifier
            ];
            $actionUrl = $this->uriBuilder->reset()
                ->setTargetPageUid($this->currentPageUid)
                ->setCreateAbsoluteUri(TRUE)
                ->setArguments($urlArguments)
                ->build();
            $filePreview = ($config['showFileIconPreview'] == 1) ? TRUE : FALSE;
            $this->view->assign('baseURL' , $baseUrl);
            $this->view->assign('actionUrl' , $actionUrl);
            $this->view->assign('downloadUrl' , $downloadUrl);
            $this->view->assign('basePath'  , $basePath);
            $this->view->assign('showPreview', $showPreview);
            $this->view->assign('showFileIcon',$filePreview);
            return $this->responseFactory->createResponse()
                ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->streamFactory->createStream($this->view->render()));
        }
        else {
            $this->view->assign('showError',TRUE);
            // error will shown in frontend
            return $this->responseFactory->createResponse()
                ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->streamFactory->createStream($this->view->render()));
        }
    }

    /**
     * action show
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     */
    public function showAction()
    {
        // to handle large requests
        ini_set( 'memory_limit', '-1' );

        // this function will be used for setting the flex-form settings for AJAX service action
        $this->setExtensionSettingsForService();

        // settings and required arguments for the json object
        $config = $this->settings;
        $translations = $this->getPageTranslations();
        $fileTypesObject = $this->fileTypeRepository->findAll();
        $fileTypes = $this->getFileTypes($fileTypesObject);
        $categoryTree = $this->doGetSubCategories(0);
        $storageUid = $this->settings['fileStorage'];
        $showPreview = ($config['showthumbnail'] == 1) ? TRUE : FALSE;
        $allowDirectLinkDownload = ($config['allowDirectLinkDownload'] == 1) ? TRUE : FALSE;
        $storageRepository = $this->storageRepository->findByUid( $storageUid );
        $storageConfiguration = $storageRepository->getConfiguration();

        // folder object
        $folderObject = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Resource\\Folder',
            $storageRepository,
            null,
            null
        );

        // getting files from the storage folder object
        $getFiles = $storageRepository->getFilesInFolder(
            $folderObject,
            $start = 0,
            $maxNumberOfItems = 0,
            $useFilters = TRUE,
            $recursive = TRUE
        );

        // basePath
        $basePath = $storageConfiguration['basePath'];
        $files = $this->generateFiles(
            $getFiles,
            $showPreview,
            $allowDirectLinkDownload,
            $basePath
        );

        // setting response variables
        $this->defaultViewObjectName = \TYPO3\CMS\Extbase\Mvc\View\JsonView::class;
        $request = $GLOBALS['TYPO3_REQUEST'];
        $normalizedParams = $request->getAttribute('normalizedParams');
        $baseUrl = $normalizedParams->getSiteUrl();
        $response = array(
            'baseURL' => $baseUrl ,
            'files' => $files,
            'categories' => $categoryTree,
            'types' => $fileTypes,
            'config' => $config,
            'translations' => $translations
        );
        echo json_encode( $response );
        die;
    }

    /**
     * force download file
     * by decrypting the file uid
     *
     * @return void 
     */
    public function forceDownloadAction()
    {
        $encrypted_fileID = ($this->request->hasArgument('fileid')) ? $this->request->getArgument('fileid') : 0;
        $fileID= openssl_decrypt(
            base64_decode( $encrypted_fileID ),
            $this->encryptionMethod,
            $this->encryptionKey,
            TRUE,
            $this->initializationVector
        );
        if(is_numeric($fileID)) {
            $storageUid = $this->settings['fileStorage'];
            $fileDetails = $this->downloadRepository->getFileDetails($storageUid , $fileID);
            $fileIdentifier = (isset($fileDetails['identifier'])) ? $fileDetails['identifier'] : FALSE;
            $storageRepository  = $this->storageRepository->findByUid($storageUid);
            $sConfig = $storageRepository->getConfiguration();
            $fileName = (isset($fileDetails['name'])) ? $fileDetails['name'] : NULL;
            if(version_compare($this->typo3Version, '8.7.99', '<=')){
                $file = realpath(PATH_site.$sConfig['basePath'].$fileIdentifier);             
            }
            else{
                $file = realpath(Environment::getPublicPath() . '/'.$sConfig['basePath'].$fileIdentifier);
            }
            $fileObject = $storageRepository->getFile( $fileIdentifier );
            if(version_compare($this->typo3Version, '9.5.99', '<=')){
                $sys_language_uid = $GLOBALS['TSFE']->sys_language_uid;
            }
            else{
                $siteLanguageObj = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
                $sys_language_uid = $siteLanguageObj->getLanguageId();
            }
            $checkTranslations = $this->downloadRepository->checkTranslations($fileObject , $sys_language_uid);
            
            if( $checkTranslations ) {
                $file_uid = isset($checkTranslations['uid_local']) ? $checkTranslations['uid_local'] : NULL;
                if(!is_null($file_uid)) {
                    $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                    $fileObj = $resourceFactory->getFileObject($file_uid);
                    $storConf = $fileObj->getStorage()->getConfiguration();
                    $file_identifier = $fileObj->getIdentifier();
                    if ($file_identifier && !empty( $file_identifier )) {
                        if(version_compare($this->typo3Version, '8.7.99', '<=')){
                            $filePath = PATH_site.$storConf['basePath'].$file_identifier;
                        }
                        else {
                            $filePath = Environment::getPublicPath(). '/'.$storConf['basePath'].$file_identifier;
                        }
                        if (!empty($filePath) && is_file($filePath)){
                            $file = $filePath;
                            $fileName = basename($file);
                        }
                    }
                }
            }
            if(is_file($file)) {
                $fileLen = filesize($file);
                $ext = strtolower(substr(strrchr($fileName, '.'), 1));
                $cType = ContentTypeHandler::getContentType($ext);
                $headers = array(
                    'Pragma'                    => 'public', 
                    'Expires'                   => 0, 
                    // 'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
                    'Cache-Control'             => 'public',
                    'Content-Description'       => 'File Transfer',
                    'Content-Type'              => $cType,
                    'Content-Disposition'       => 'attachment; filename="'. $fileName .'"',
                    'Content-Transfer-Encoding' => 'binary', 
                    'Content-Length'            => $fileLen         
                );
                if(version_compare($this->typo3Version, '10.4.99', '<=')){
                    foreach($headers as $header => $data)
                    $this->response->setHeader($header, $data); 
                    $this->response->sendHeaders();
                }
                else {
                    foreach($headers as $header => $data) {
                        header($header . ': ' . $data);
                    }
                }
                @readfile($file);die;
            }
        }
        else{
            echo "Invalid Access!";die;
        }   
    }

    /**
     * showErrorMessage
     *
     * @return void
     */
    public function showErrorMessage()
    {
        $error_code = 503;
        $this->request->forward('error', NULL, NULL, $error_code);
    }

    /**
     * redirectTo404
     *
     * @return void
     */
    public function redirectTo404()
    {
        $GLOBALS['TSFE']->pageNotFoundAndExit($this->entityNotFoundMessage);
    }

    /**
     * setExtensionSettingsForService
     *
     * @return array | boolean
     */
    public function setExtensionSettingsForService()
    {
        $contentObjectIdentifier = intval(GeneralUtility::_GET('contentIdentifier'));
        $record = BackendUtility::getRecord('tt_content', $contentObjectIdentifier);
        $this->handleRedirectPolicyIfInvalidIdentifier($record);
        $this->configurationManager->getContentObject()->readFlexformIntoConf($record['pi_flexform'], $this->settings);
        foreach ($this->settings as $key => $val) {
            unset($this->settings[$key]);
            $this->settings[str_replace('settings.','', $key)] = $val;
        }
    }

    /**
     * handleRedirectPolicyIfInvalidIdentifier
     * This function will check the ajax service url parameters and redirect to 404 page if not valid
     *
     * @param $record
     * @return void
     */
    public function handleRedirectPolicyIfInvalidIdentifier($record)
    {
        if ($record['list_type'] != "pitsdownloadcenter_pitsdownloadcenter") {
            $this->redirectTo404();
        }
    }

    /**
     * checkServiceCalledRoute
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function checkServiceCalledRoute()
    {
        $this->typeNumConstant = $this->getTypeNumUsedForAjaxService();
        if (intval(GeneralUtility::_GET('type')) === $this->typeNumConstant) {
            // forward to show action
            $this->forward('show');
        }
    }

    /**
     * getTypeNumUsedForAjaxService
     *
     * @return int
     */
    public function getTypeNumUsedForAjaxService()
    {
        $pluginConfigurations = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            $this->extensionName
        );
        return intval($pluginConfigurations['typeNum']);
    }
}

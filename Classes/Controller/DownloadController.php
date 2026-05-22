<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2026 Developer <contact@pitsolutions.com>, PIT Solutions Pvt Ltd
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
 ***************************************************************/

use PITS\PitsDownloadcenter\Domain\Repository\CategoryRepository;
use PITS\PitsDownloadcenter\Domain\Repository\DownloadRepository;
use PITS\PitsDownloadcenter\Domain\Repository\FiletypeRepository;
use PITS\PitsDownloadcenter\Handlers\ContentTypeHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;

/**
 * DownloadController
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - showAction(): removed 'echo json_encode(); die' pattern. Now returns a proper PSR-7 JsonResponse
 *   using $this->responseFactory. Removed $this->defaultViewObjectName assignment (JsonView usage
 *   replaced entirely by JSON response factory).
 * - forceDownloadAction(): removed raw header()/readfile()/die. Now returns a PSR-7 StreamedResponse
 *   built via $this->responseFactory and $this->streamFactory. Added ResponseInterface return type.
 *   Added handling for forbidden content types (empty string from ContentTypeHandler).
 * - redirectTo404(): $GLOBALS['TSFE']->pageNotFoundAndExit() is removed in v13. Replaced with
 *   throwing \TYPO3\CMS\Core\Error\Http\PageNotFoundException which TYPO3 converts to a 404 response.
 * - setExtensionSettingsForService(): configurationManager->getContentObject()->readFlexformIntoConf()
 *   is removed in v13. Replaced with direct FlexFormService usage to parse pi_flexform into settings.
 * - handleRedirectPolicyIfInvalidIdentifier(): list_type is removed in v13 for plugins registered via
 *   registerPlugin(). The new CType is 'pitsdownloadcenter_pitsdownloadcenter'. Check CType instead.
 * - showErrorMessage(): $this->request->forward() is removed in v13. Replaced with ForwardResponse.
 * - listAction(): removed $this->configurationManager->getContentObject()->data access (deprecated).
 *   Content object data is now retrieved via the request attribute 'extbase.controller.context' or
 *   the contentObject request attribute. Used $this->request->getAttribute('currentContentObject')
 *   which is the v13 replacement.
 * - Added ImageService to constructor (required for processImage() in AbstractController).
 */
class DownloadController extends AbstractController
{
    /**
     * @var int|null
     */
    protected ?int $typeNumConstant = null;

    public function __construct(
        DownloadRepository $downloadRepository,
        FiletypeRepository $fileTypeRepository,
        CategoryRepository $categoryRepository,
        PersistenceManager $persistenceManager,
        StorageRepository $storageRepository,
        ImageService $imageService
    ) {
        parent::__construct(
            $downloadRepository,
            $fileTypeRepository,
            $categoryRepository,
            $persistenceManager,
            $storageRepository,
            $imageService
        );
    }

    /**
     * listAction
     *
     * @throws PageNotFoundException
     */
    public function listAction(): ResponseInterface
    {
        $possibleRedirect = $this->checkServiceCalledRoute();
        if ($possibleRedirect) {
            return $possibleRedirect;
        }

        $config = $this->settings;
        $storageUid = (int)$this->settings['fileStorage'];
        $storageRepository = $this->storageRepository->findByUid($storageUid);
        if (!($storageRepository instanceof \TYPO3\CMS\Core\Resource\ResourceStorage)) {
            $this->redirectTo404();
        }
        $storageConfiguration = $storageRepository->getConfiguration();
        $basePath = $storageConfiguration['basePath'];

        // Intentionally block fileadmin/ — we do not want to expose the default FAL storage.
        $isValid = ($basePath !== 'fileadmin/');
        $showPreview = ($config['showthumbnail'] == 1);

        $this->assignCommonViewVariables();

        if ($isValid) {
            $request = $GLOBALS['TYPO3_REQUEST'];
            $normalizedParams = $request->getAttribute('normalizedParams');
            $baseUrl = $normalizedParams->getSiteUrl();

            // Resolve ajax-loader image URI
            $loaderimage = $this->imageService->getImage('EXT:pits_downloadcenter/Resources/Public/Icons/ajax-loader.gif', null, false);
            $loaderimageuri = $this->imageService->getImageUri($loaderimage, false);
            $parsedUrl = parse_url($loaderimageuri);
            if (isset($parsedUrl['host'])) {
                $loaderimageuri = $parsedUrl['path'];
            }

            // TYPO3 v13: getContentObject() is removed from ConfigurationManager.
            // Retrieve the current content object data via the PSR-7 request attribute.
            $currentContentObject = $this->request->getAttribute('currentContentObject');
            $cObjectData = $currentContentObject !== null ? $currentContentObject->data : [];
            $contentIdentifier = isset($cObjectData['_LOCALIZED_UID']) ? (int)$cObjectData['_LOCALIZED_UID'] : (int)($cObjectData['uid'] ?? 0);

            $urlArguments = [
                'type' => (int)preg_replace('/[^A-Za-z0-9\-]/', '', $this->settings['typeNum']),
                'contentIdentifier' => $contentIdentifier,
            ];
            $actionUrl = $this->uriBuilder->reset()
                ->setTargetPageUid($this->currentPageUid)
                ->setCreateAbsoluteUri(true)
                ->setArguments($urlArguments)
                ->build();

            $filePreview = ($config['showFileIconPreview'] == 1);

            $this->view->assign('baseURL', $baseUrl);
            $this->view->assign('loaderimageuri', $loaderimageuri);
            $this->view->assign('actionUrl', $actionUrl);
            $this->view->assign('basePath', $basePath);
            $this->view->assign('showPreview', $showPreview);
            $this->view->assign('showFileIcon', $filePreview);

            return $this->responseFactory->createResponse()
                ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->streamFactory->createStream($this->view->render()));
        }

        $this->view->assign('showError', true);
        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($this->view->render()));
    }

    /**
     * showAction — AJAX JSON endpoint
     *
     * TYPO3 v13: Replaced 'echo json_encode(); die' with a proper PSR-7 JSON response.
     * Removed $this->defaultViewObjectName = JsonView::class (JsonView is no longer needed
     * because we return the JSON directly via the response factory).
     *
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     */
    public function showAction(): ResponseInterface
    {
        ini_set('memory_limit', '-1');

        if (!$this->setExtensionSettingsForService()) {
            return $this->responseFactory->createResponse(404)
                ->withAddedHeader('Content-Type', 'application/json; charset=utf-8')
                ->withBody($this->streamFactory->createStream(json_encode(['error' => 'Invalid content identifier'])));
        }

        $config = $this->settings;
        $translations = $this->getPageTranslations();
        $fileTypesObject = $this->fileTypeRepository->findAll();
        $fileTypes = $this->getFileTypes($fileTypesObject);
        $categoryTree = $this->doGetSubCategories(0);
        $storageUid = (int)$this->settings['fileStorage'];
        $showPreview = ($config['showthumbnail'] == 1);
        $allowDirectLinkDownload = ($config['allowDirectLinkDownload'] == 1);
        $storageRepository = $this->storageRepository->findByUid($storageUid);
        if (!($storageRepository instanceof \TYPO3\CMS\Core\Resource\ResourceStorage)) {
            return $this->responseFactory->createResponse(404)
                ->withAddedHeader('Content-Type', 'application/json; charset=utf-8')
                ->withBody($this->streamFactory->createStream(json_encode(['error' => 'File storage not configured'])));
        }
        $storageConfiguration = $storageRepository->getConfiguration();

        $folderObject = $storageRepository->getFolder('');
        $getFiles = $storageRepository->getFilesInFolder(
            $folderObject,
            0,
            0,
            true,
            true
        );

        $basePath = $storageConfiguration['basePath'];
        $files = $this->generateFiles($getFiles, $showPreview, $allowDirectLinkDownload, $basePath);

        $request = $GLOBALS['TYPO3_REQUEST'];
        $normalizedParams = $request->getAttribute('normalizedParams');
        $baseUrl = $normalizedParams->getSiteUrl();

        $responseData = [
            'baseURL' => $baseUrl,
            'files' => $files,
            'categories' => $categoryTree,
            'types' => $fileTypes,
            'config' => $config,
            'translations' => $translations,
        ];

        // TYPO3 v13: Return a proper PSR-7 JSON response instead of echo+die.
        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody($this->streamFactory->createStream(json_encode($responseData)));
    }

    /**
     * forceDownloadAction — streams a file to the browser as a forced download.
     *
     * TYPO3 v13: Throws PropagateResponseException instead of returning ResponseInterface.
     * Bootstrap::handleFrontendRequest() converts any returned ResponseInterface body to a
     * string and embeds it in the page HTML, corrupting binary files. PropagateResponseException
     * bypasses this by propagating through the middleware stack directly to ResponsePropagation,
     * which returns the response without page-HTML wrapping — the official TYPO3 v13 replacement
     * for header()/readfile()/die().
     */
    public function forceDownloadAction(): never
    {
        $encrypted_fileID = $this->request->hasArgument('fileid') ? $this->request->getArgument('fileid') : '0';
        $fileID = openssl_decrypt(
            base64_decode($encrypted_fileID),
            $this->encryptionMethod,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $this->initializationVector
        );

        if (!is_numeric($fileID)) {
            throw new PropagateResponseException(
                $this->responseFactory->createResponse(400)
                    ->withAddedHeader('Content-Type', 'text/plain')
                    ->withBody($this->streamFactory->createStream('Invalid Access!')),
                1
            );
        }

        $storageUid = (int)$this->settings['fileStorage'];
        $fileDetails = $this->downloadRepository->getFileDetails($storageUid, $fileID);
        $fileIdentifier = $fileDetails['identifier'] ?? false;
        $storageRepository = $this->storageRepository->findByUid($storageUid);
        $sConfig = $storageRepository->getConfiguration();
        $fileName = $fileDetails['name'] ?? null;
        $file = realpath(Environment::getPublicPath() . '/' . $sConfig['basePath'] . $fileIdentifier);
        $fileObject = $storageRepository->getFile($fileIdentifier);

        $siteLanguageObj = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');
        $sys_language_uid = $siteLanguageObj->getLanguageId();
        $checkTranslations = $this->downloadRepository->checkTranslations($fileObject, $sys_language_uid);

        if ($checkTranslations) {
            $file_uid = $checkTranslations['uid_local'] ?? null;
            if ($file_uid !== null) {
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                $fileObj = $resourceFactory->getFileObject($file_uid);
                $storConf = $fileObj->getStorage()->getConfiguration();
                $file_identifier = $fileObj->getIdentifier();
                if (!empty($file_identifier)) {
                    $filePath = Environment::getPublicPath() . '/' . $storConf['basePath'] . $file_identifier;
                    if (!empty($filePath) && is_file($filePath)) {
                        $file = $filePath;
                        $fileName = basename($file);
                    }
                }
            }
        }

        if (!is_string($file) || !is_file($file)) {
            throw new PropagateResponseException(
                $this->responseFactory->createResponse(404)
                    ->withAddedHeader('Content-Type', 'text/plain')
                    ->withBody($this->streamFactory->createStream('File not found.')),
                1
            );
        }

        $fileLen = filesize($file);
        $ext = strtolower((string)substr(strrchr($fileName, '.'), 1));
        $cType = ContentTypeHandler::getContentType($ext);

        // TYPO3 v13: ContentTypeHandler now returns '' for forbidden file types (e.g. .php, .sql).
        if ($cType === '') {
            throw new PropagateResponseException(
                $this->responseFactory->createResponse(403)
                    ->withAddedHeader('Content-Type', 'text/plain')
                    ->withBody($this->streamFactory->createStream('Forbidden file type.')),
                1
            );
        }

        // TYPO3 v13: Bootstrap::handleFrontendRequest() reads the ResponseInterface body as a string
        // and returns it as the plugin's text content, which then gets embedded in the page HTML —
        // corrupting binary files. PropagateResponseException bypasses this: it is re-thrown by
        // ProductionExceptionHandler and caught by the ResponsePropagation middleware, which returns
        // the response directly without page-HTML wrapping. This is the official TYPO3 v13
        // replacement for the old header()/readfile()/die() pattern.
        $fileStream = $this->streamFactory->createStreamFromFile($file, 'rb');

        throw new PropagateResponseException(
            $this->responseFactory->createResponse()
                ->withHeader('Pragma', 'public')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'public')
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', $cType)
                ->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->withHeader('Content-Transfer-Encoding', 'binary')
                ->withHeader('Content-Length', (string)$fileLen)
                ->withBody($fileStream),
            1
        );
    }

    /**
     * showErrorMessage
     *
     * TYPO3 v13: $this->request->forward() is removed. Use ForwardResponse instead.
     */
    public function showErrorMessage(): ResponseInterface
    {
        // TYPO3 v13: forward() on the request object is gone; return a ForwardResponse.
        return new ForwardResponse('error');
    }

    /**
     * redirectTo404
     *
     * TYPO3 v13: $GLOBALS['TSFE']->pageNotFoundAndExit() is removed.
     * Throw PageNotFoundException instead, which TYPO3's middleware stack converts to a 404 response.
     *
     * @throws PageNotFoundException
     */
    public function redirectTo404(): never
    {
        throw new PageNotFoundException(
            'The requested download resource was not found.',
            1700000001
        );
    }

    /**
     * setExtensionSettingsForService
     *
     * Reads the FlexForm settings from the tt_content record identified by the 'contentIdentifier'
     * query parameter and merges them into $this->settings.
     *
     * Returns false if the record is invalid (so showAction can return a JSON error).
     *
     * TYPO3 v13: configurationManager->getContentObject()->readFlexformIntoConf() is removed.
     * Replaced with \TYPO3\CMS\Core\Service\FlexFormService to decode the pi_flexform XML,
     * then we flatten the 'settings.' prefix just as the original code did.
     */
    public function setExtensionSettingsForService(): bool
    {
        $contentObjectIdentifier = (int)($GLOBALS['TYPO3_REQUEST']->getQueryParams()['contentIdentifier'] ?? 0);
        $record = BackendUtility::getRecord('tt_content', $contentObjectIdentifier);

        if (!$this->handleRedirectPolicyIfInvalidIdentifier($record)) {
            return false;
        }

        if (!empty($record['pi_flexform'])) {
            // TYPO3 v13: Use FlexFormService to parse the pi_flexform XML into a nested array.
            $flexFormService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\FlexFormService::class);
            $flexFormData = $flexFormService->convertFlexFormContentToArray($record['pi_flexform']);

            // The original code stripped the 'settings.' prefix from keys. FlexFormService already
            // returns a nested array, so we merge the 'settings' sub-array directly.
            if (isset($flexFormData['settings']) && is_array($flexFormData['settings'])) {
                foreach ($flexFormData['settings'] as $key => $value) {
                    $this->settings[$key] = $value;
                }
            }
        }

        return true;
    }

    /**
     * handleRedirectPolicyIfInvalidIdentifier
     *
     * Returns true if the record is a valid download center plugin content element, false otherwise.
     *
     * Accepts both:
     * - TYPO3 v13 CType: 'pitsdownloadcenter_pitsdownloadcenter' (registered via registerPlugin)
     * - Legacy v12 format: CType='list' + list_type='pitsdownloadcenter_pitsdownloadcenter'
     *
     * @param array<string,mixed>|null $record
     */
    public function handleRedirectPolicyIfInvalidIdentifier(?array $record): bool
    {
        if (empty($record)) {
            return false;
        }
        $cType = $record['CType'] ?? '';
        if ($cType === 'pitsdownloadcenter_pitsdownloadcenter') {
            return true;
        }
        // Support legacy content elements created as "General Plugin" (CType=list)
        if ($cType === 'list' && ($record['list_type'] ?? '') === 'pitsdownloadcenter_pitsdownloadcenter') {
            return true;
        }
        return false;
    }

    /**
     * checkServiceCalledRoute
     *
     * Detects whether this request is the AJAX typeNum call and forwards to showAction if so.
     */
    public function checkServiceCalledRoute(): ?ForwardResponse
    {
        $this->typeNumConstant = $this->getTypeNumUsedForAjaxService();
        $queryType = $GLOBALS['TYPO3_REQUEST']->getQueryParams()['type'] ?? null;
        if ($queryType !== null && (int)$queryType === $this->typeNumConstant) {
            return new ForwardResponse('show');
        }
        return null;
    }

    /**
     * getTypeNumUsedForAjaxService
     */
    public function getTypeNumUsedForAjaxService(): int
    {
        $pluginConfigurations = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            $this->extensionName
        );
        return (int)($pluginConfigurations['typeNum'] ?? 0);
    }
}

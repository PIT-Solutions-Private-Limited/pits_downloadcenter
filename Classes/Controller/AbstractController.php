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
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * AbstractController
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Removed initializeView($view) override: this hook was deprecated in v12 and removed in v13.
 *   The view assignments it performed are now done inside initializeAction() so they are available
 *   before any action method runs (view is assigned via $this->view inside each action, or can be
 *   assigned in initializeAction() via view->assignMultiple if view is available — however since
 *   Extbase initialises the view after initializeAction(), the assignments from initializeView are
 *   best kept in each action method individually. We preserve the common data by setting protected
 *   properties that each action can push to the view explicitly, matching prior behaviour).
 * - Replaced $GLOBALS['TSFE']->id with $this->request->getAttribute('routing')->getPageId().
 *   In TYPO3 v13, $GLOBALS['TSFE']->id is removed; page ID is obtained from the routing attribute.
 * - Replaced configurationManager->getContentObject()->cObjGetSingle('IMG_RESOURCE', ...) in
 *   processImage() with TYPO3\CMS\Extbase\Service\ImageService, which is the correct v13 API.
 * - Removed Typo3Version import (unused).
 * - All injected properties retain their existing constructor-injection pattern (no change needed).
 */
abstract class AbstractController extends ActionController
{
    /**
     * @var DownloadRepository
     */
    protected $downloadRepository;

    /**
     * @var FiletypeRepository
     */
    protected $fileTypeRepository;

    /**
     * @var array<string,mixed>
     */
    protected array $extConf = [];

    /**
     * @var string|null
     */
    protected ?string $isLogin = null;

    /**
     * TypoScript settings for the current action.
     *
     * @var array<string,mixed>
     */
    protected array $actionSettings = [];

    /**
     * TypoScript settings for the current controller.
     *
     * @var array<string,mixed>
     */
    protected array $controllerSettings = [];

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var int
     */
    protected int $currentPageUid = 0;

    /**
     * @var string|null
     */
    protected ?string $encryptionKey = null;

    /**
     * @var string|null
     */
    protected ?string $encryptionMethod = null;

    /**
     * @var string
     */
    protected string $initializationVector = '';

    /**
     * @var string
     */
    protected string $extensionName = '';

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @var \DateTime|null
     */
    protected ?\DateTime $dateTime = null;

    /**
     * @var ImageService
     */
    protected ImageService $imageService;

    public function __construct(
        DownloadRepository $downloadRepository,
        FiletypeRepository $fileTypeRepository,
        CategoryRepository $categoryRepository,
        PersistenceManager $persistenceManager,
        StorageRepository $storageRepository,
        ImageService $imageService
    ) {
        $this->downloadRepository = $downloadRepository;
        $this->fileTypeRepository = $fileTypeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->persistenceManager = $persistenceManager;
        $this->storageRepository = $storageRepository;
        $this->imageService = $imageService;
    }

    /**
     * Initializes common controller state before any action method runs.
     */
    protected function initializeAction(): void
    {
        parent::initializeAction();

        $this->extensionName = $this->request->getControllerExtensionName();
        $this->dateTime = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $this->extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName)] ?? [];

        $this->initializationVector = $this->strToHex('12345678');
        $this->encryptionKey = $this->extConf['secure_encryption_key'] ?? null;
        $this->encryptionMethod = $this->extConf['secure_encryption_method'] ?? null;

        // TYPO3 v13: $GLOBALS['TSFE']->id is removed.
        // Use the routing attribute from the PSR-7 request to get the current page UID.
        $routing = $this->request->getAttribute('routing');
        $this->currentPageUid = $routing !== null ? (int)$routing->getPageId() : 0;
    }

    /**
     * Assigns the common view variables that were previously set in initializeView().
     *
     * TYPO3 v13: initializeView($view) is removed. Call this method at the start of each
     * action that needs these variables, or call it from a custom initializeXxxAction().
     */
    protected function assignCommonViewVariables(): void
    {
        $this->view->assignMultiple([
            'controllerSettings' => $this->controllerSettings,
            'actionSettings' => $this->actionSettings,
            'extConf' => $this->extConf,
            'currentPageUid' => $this->currentPageUid,
        ]);
    }

    /**
     * Converts a string to its hexadecimal representation.
     */
    public function strToHex(string $string): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strtoupper($hex);
    }

    /**
     * Recursively builds a category tree starting from $parentID.
     *
     * @param int|string $parentID
     * @return array<int,array<string,mixed>>
     */
    public function doGetSubCategories($parentID): array
    {
        $categoryTree = [];
        $subCategories = $this->categoryRepository->getSubCategories($parentID);
        $i = 0;
        foreach ($subCategories as $key => $value) {
            if ($value['l10n_parent'] != 0) {
                $categoryTree[$key]['localized_uid'] = $value['uid'];
                $catID = $value['l10n_parent'];
            } else {
                $catID = $value['uid'];
            }
            $catName = $value['categoryname'];
            $categoryTree[$key]['id'] = $catID;
            $categoryTree[$key]['title'] = $catName;

            $has_sub = $this->categoryRepository->getSubCategoriesCount($catID);
            if ($has_sub) {
                $categoryTree[$key]['input'] = $this->doGetSubCategories($catID);
            }
            $i++;
        }
        return $categoryTree;
    }

    /**
     * Builds a structured file-response array from a list of FAL file objects.
     *
     * @param iterable<\TYPO3\CMS\Core\Resource\File> $fileObject
     * @param bool $showPreview
     * @param bool $allowDirectLinkDownlod
     * @param string $basePath
     * @return array<int,array<string,mixed>>
     */
    public function generateFiles(iterable $fileObject, bool $showPreview, bool $allowDirectLinkDownlod, string $basePath): array
    {
        $response = [];
        $pImgWidth = (!empty($this->settings['previewThumbnailWidth'])) ? $this->settings['previewThumbnailWidth'] : '150m';
        $pImgHeight = (!empty($this->settings['previewThumbnailHeight'])) ? $this->settings['previewThumbnailHeight'] : '150m';
        $i = 0;

        // TYPO3 v13: $GLOBALS['TSFE']->id is removed; use routing attribute from PSR-7 request.
        $pageUid = $this->currentPageUid;

        $request = $GLOBALS['TYPO3_REQUEST'];
        $normalizedParams = $request->getAttribute('normalizedParams');
        $baseUri = rtrim($normalizedParams->getSiteUrl(), '/');

        foreach ($fileObject as $key => $value) {
            if ($value instanceof \TYPO3\CMS\Core\Resource\File) {
                $key = $i++;
                $fileProperty = $value->getProperties();
                $response[$key]['id'] = (int)$fileProperty['uid'];
                $response[$key]['title'] = (!empty($fileProperty['title'])) ? $fileProperty['title'] : $value->getNameWithoutExtension();
                $response[$key]['size'] = $this->formatBytes((int)$fileProperty['size']);
                $response[$key]['fileType'] = strtoupper($fileProperty['extension']);
                $response[$key]['extension'] = $fileProperty['extension'];
                $response[$key]['dataType'] = ($fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'] != 0
                    && $fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'] !== null)
                    ? explode(',', $fileProperty['tx_pitsdownloadcenter_domain_model_download_filetype'])
                    : [];
                $response[$key]['categories'] = ($fileProperty['tx_pitsdownloadcenter_domain_model_download_category'] != 0
                    && $fileProperty['tx_pitsdownloadcenter_domain_model_download_category'] !== null)
                    ? explode(',', $fileProperty['tx_pitsdownloadcenter_domain_model_download_category'])
                    : [];

                // Preview image processing
                if ($showPreview) {
                    $processed = $this->processImage($value, $pImgWidth, $pImgHeight);
                    $response[$key]['imageUrl'] = ($processed !== '' && file_exists(realpath(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . $processed)))
                        ? $baseUri . $processed
                        : null;
                    $iconSvg = 'EXT:pits_downloadcenter/Resources/Public/Icons/Mimetypes/' . $this->resolveFileTypeIconName((string)$fileProperty['extension']) . '.svg';
                    $response[$key]['iconUrl'] = $baseUri . PathUtility::getPublicResourceWebPath($iconSvg);
                }

                // Force-download vs direct-link
                if (!$allowDirectLinkDownlod) {
                    $file_uid_secure = base64_encode(
                        openssl_encrypt(
                            (string)$fileProperty['uid'],
                            $this->encryptionMethod,
                            $this->encryptionKey,
                            OPENSSL_RAW_DATA,
                            $this->initializationVector
                        )
                    );
                    $downloadArguments = [
                        'tx_pitsdownloadcenter_pitsdownloadcenter' => [
                            'controller' => 'Download',
                            'action' => 'forceDownload',
                            'fileid' => $file_uid_secure,
                        ],
                    ];
                    $response[$key]['downloadUrl'] = $this->uriBuilder->reset()
                        ->setTargetPageUid($pageUid)
                        ->setCreateAbsoluteUri(true)
                        ->setArguments($downloadArguments)
                        ->build();
                    $response[$key]['url'] = $response[$key]['downloadUrl'];
                } else {
                    $response[$key]['url'] = $baseUri . $value->getPublicUrl();
                    $response[$key]['downloadUrl'] = $baseUri . $value->getPublicUrl();
                }
            }
        }
        return $response;
    }

    /**
     * Processes an image file through FAL and returns the relative URL.
     *
     * TYPO3 v13: configurationManager->getContentObject()->cObjGetSingle('IMG_RESOURCE', ...) is removed.
     * Replaced with ImageService::applyProcessingInstructions() + ImageService::getImageUri(),
     * which is the correct TYPO3 v12/v13 API for image processing.
     *
     * @param \TYPO3\CMS\Core\Resource\File $fileObj
     * @param string $size_w  Width processing instruction (e.g. "150m")
     * @param string $size_h  Height processing instruction (e.g. "150m")
     * @return string Relative URL to the processed image, or empty string on failure
     */
    public function processImage(\TYPO3\CMS\Core\Resource\File $fileObj, string $size_w, string $size_h): string
    {
        if (!$fileObj->isImage()) {
            return '';
        }
        try {
            $processingInstructions = [
                'width' => $size_w,
                'height' => $size_h,
            ];
            $processedImage = $this->imageService->applyProcessingInstructions($fileObj, $processingInstructions);
            // If FAL returned the original file (e.g. PDF without Ghostscript), no real thumbnail was generated.
            if ($processedImage->usesOriginalFile()) {
                return '';
            }
            return $this->imageService->getImageUri($processedImage);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Maps a file extension to the TYPO3 core mimetype SVG icon filename (without .svg suffix).
     * Falls back to mimetypes-other-other for unknown extensions.
     */
    private function resolveFileTypeIconName(string $extension): string
    {
        $map = [
            // Documents
            'doc' => 'mimetypes-word', 'docx' => 'mimetypes-word', 'dot' => 'mimetypes-word',
            'dotx' => 'mimetypes-word', 'rtf' => 'mimetypes-text-text',
            'odt' => 'mimetypes-open-document-text',
            // Spreadsheets
            'xls' => 'mimetypes-excel', 'xlsx' => 'mimetypes-excel', 'xlsm' => 'mimetypes-excel',
            'ods' => 'mimetypes-open-document-spreadsheet',
            'csv' => 'mimetypes-text-csv',
            // Presentations
            'ppt' => 'mimetypes-powerpoint', 'pptx' => 'mimetypes-powerpoint',
            'pps' => 'mimetypes-powerpoint', 'ppsx' => 'mimetypes-powerpoint',
            'odp' => 'mimetypes-open-document-presentation',
            // PDF
            'pdf' => 'mimetypes-pdf',
            // Archives
            'zip' => 'mimetypes-compressed', 'rar' => 'mimetypes-compressed',
            'gz' => 'mimetypes-compressed', 'tar' => 'mimetypes-compressed',
            '7z' => 'mimetypes-compressed', 'bz2' => 'mimetypes-compressed',
            // Audio
            'mp3' => 'mimetypes-media-audio', 'ogg' => 'mimetypes-media-audio',
            'wav' => 'mimetypes-media-audio', 'flac' => 'mimetypes-media-audio',
            'aac' => 'mimetypes-media-audio', 'wma' => 'mimetypes-media-audio',
            // Video
            'mp4' => 'mimetypes-media-video', 'avi' => 'mimetypes-media-video',
            'mov' => 'mimetypes-media-video', 'mkv' => 'mimetypes-media-video',
            'wmv' => 'mimetypes-media-video', 'flv' => 'mimetypes-media-video',
            'webm' => 'mimetypes-media-video',
            // Images (these normally get a real thumbnail, but listed as safety net)
            'png' => 'mimetypes-media-image', 'jpg' => 'mimetypes-media-image',
            'jpeg' => 'mimetypes-media-image', 'gif' => 'mimetypes-media-image',
            'bmp' => 'mimetypes-media-image', 'webp' => 'mimetypes-media-image',
            // Text / code
            'txt' => 'mimetypes-text-text',
            'html' => 'mimetypes-text-html', 'htm' => 'mimetypes-text-html',
            'css' => 'mimetypes-text-css',
            'js' => 'mimetypes-text-js',
            'php' => 'mimetypes-text-php',
        ];

        return $map[strtolower($extension)] ?? 'mimetypes-other-other';
    }

    /**
     * Returns an array of file-type data from a QueryResult.
     *
     * @param iterable<\PITS\PitsDownloadcenter\Domain\Model\Filetype> $fileTypesObject
     * @return array<int,array<string,mixed>>
     */
    public function getFileTypes(iterable $fileTypesObject): array
    {
        $response = [];
        foreach ($fileTypesObject as $key => $value) {
            $response[$key]['id'] = $value->getUid();
            $response[$key]['title'] = $value->getFiletype();
        }
        return $response;
    }

    /**
     * Builds an array of translated UI labels for the frontend.
     *
     * @return array<string,string|null>
     */
    public function getPageTranslations(): array
    {
        return [
            'keywordsearch' => $this->localise('tx_pitsdownloadcenter_domain_model_download.keywordsearch'),
            'searchkey' => $this->localise('tx_pitsdownloadcenter_domain_model_download.searchkey'),
            'filterbyarea' => $this->localise('tx_pitsdownloadcenter_domain_model_download.filterbyarea'),
            'categoryplaceholder' => $this->localise('tx_pitsdownloadcenter_domain_model_download.categoryplaceholder'),
            'searchbytype' => $this->localise('tx_pitsdownloadcenter_domain_model_download.searchbytype'),
            'resultsfound' => $this->localise('tx_pitsdownloadcenter_domain_model_download.resultsfound'),
            'tabletitle' => $this->localise('tx_pitsdownloadcenter_domain_model_download.tabletitle'),
            'tablesize' => $this->localise('tx_pitsdownloadcenter_domain_model_download.tablesize'),
            'tabletype' => $this->localise('tx_pitsdownloadcenter_domain_model_download.tabletype'),
            'tabledownload' => $this->localise('tx_pitsdownloadcenter_domain_model_download.tabledownload'),
        ];
    }

    /**
     * Wraps LocalizationUtility::translate().
     */
    public function localise(string $id): ?string
    {
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($id, 'PitsDownloadcenter');
    }

    /**
     * Converts a byte count to a human-readable size string.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = (int)floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

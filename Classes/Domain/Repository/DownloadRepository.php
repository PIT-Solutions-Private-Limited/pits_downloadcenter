<?php
namespace PITS\PitsDownloadcenter\Domain\Repository;

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
 * @todo cleanup required in this class
 *
 * DownloadRepository
 * The repository for Downloads
 */
class DownloadRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * findAll
     *
     * @return mixed
     */
	public function findAll()
    {
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		    'TYPO3\\CMS\\Core\\Resource\\FileRepository'
        );
		return $fileRepository->findAll();
	}

	/**
	* Disables pid constraint
	*
	* @return void
	*/
	public function initializeObject()
    {
		$querySettings = $this->objectManager->get(
		    'TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings'
        );
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	* Finds all referenced documents returning them as File modules
	*
	* @return void
	*/
	public function findAllReferenced()
    {
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		    'TYPO3\\CMS\\Core\\Resource\\FileRepository'
        );
		return $fileRepository->findAll();
	}

	/**
	 * find Processed File
	 *
     * @param $data
	 * @return array
	 **/
	public function getProcessedFile($data)
    {
		$processedFileRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		    'TYPO3\\CMS\\Core\\Resource\\ProcessedFileRepository'
        );
		$taskType = "Image.Preview";
		$processingConfig = array();
		return $processedFileRep->findOneByOriginalFileAndTaskTypeAndConfiguration($data, $taskType, $processingConfig);
	}

    /**
     * isProcessed
     *
     * @param array $fileDetails
     * @return bool
     */
	public function isProcessed(array $fileDetails)
    {
		$processedFile = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Resource\\ProcessedFile',
            // fileObject , processing type, processing configurations
            [$fileDetails['fileObj'], $fileDetails['processType'], $fileDetails['processConfig']]
        );
		$isProcessed = $processedFile->isProcessed();
		return $isProcessed;
	}

    /**
     * getFileDetails
     *
     * @todo deprecated function TYPO3 _DB needs to change
     * @param $storageUid
     * @param $fileID
     * @return mixed
     */
	public function getFileDetails($storageUid , $fileID)
    {
		$response = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow ( 
            "identifier,name",
            "sys_file",
            " storage = $storageUid AND uid = $fileID ",
            $groupBy= '',
            $orderBy= '',
            $numIndex=FALSE
        );
		return $response;
	}

    /**
     * checkTranslations
     *
     * @todo deprecated function TYPO3 _DB needs to change
     * @param $file
     * @param $sys_language_uid
     * @return array|bool
     */
	public function checkTranslations($file , $sys_language_uid)
    {
		$file_uid = $file->getUid();
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
		$getTranslatedFile = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow ( 
            "uid,tx_pitsdownloadcenter_domain_model_download_translate as translated_file",
            "sys_file_metadata",
            " sys_language_uid = $sys_language_uid AND file = $file_uid ",
            $groupBy= '',
            $orderBy= '',
            $numIndex=FALSE
        );
		
		// Query
		if (is_array( $getTranslatedFile )):
			$response = (!empty(array_filter( $getTranslatedFile )))?$getTranslatedFile:false;
		else:
			return  false;
		endif;
		return $response;
	}
}

<?php
namespace PITS\PitsDownloadcenter\Domain\Repository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * The repository for Downloads
 */
class DownloadRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {
	public function findAll(){
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		return $fileRepository->findAll();
	}
	
	public function findByStorageID( $storageID ){
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$query=$this->createQuery();
		$query->equals('storage', '2');
		return $query->execute();
	}

	/**
	* Disables pid constraint
	*
	* @return void
	*/
	public function initializeObject() {
		$querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	* Finds all referenced documents returning them as File modules
	*
	* @return void
	*/
	public function findAllReferenced() {
		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$query = $this->createQuery();
		$documents = $query->execute();
		$references = array();
		return $fileRepository->findAll();
	}

	/**
	 * find Processed File
	 * 
	 * @return array
	 **/
	public function getProcessedFile( $data ){	
		$processedFileRep = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ProcessedFileRepository');
		$storage = $data->getStorage();
		$identifier = $data->getIdentifier() ;
		$taskType = "Image.Preview";
		//$processingConfig = array("width" => 150, "height" => 150);
		$processingConfig = array();
		$processedFile = new \TYPO3\CMS\Core\Resource\ProcessedFile( $data , $taskType , $processingConfig );
		$retprocessFile = $processedFileRep->findOneByOriginalFileAndTaskTypeAndConfiguration( $data , $taskType , $processingConfig );
		return $retprocessFile;
	}	

	public function isProcessed( array $fileDetails ){
		$processedFile = new \TYPO3\CMS\Core\Resource\ProcessedFile( $fileDetails['fileObj'] , $fileDetails['processType'] , $fileDetails['processConfig'] );
		$isProcessed = $processedFile->isProcessed();
		return $isProcessed;
	}

	public function getFileDetails( $storageuid , $fileID ){
		$response = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow ( 
						"identifier,name", 
						"sys_file", 
						" storage = $storageuid AND uid = $fileID ", 
						$groupBy= '', 
						$orderBy= '', 
						$numIndex=FALSE
					);
		return $response;
	}
	
	public function checkTranslations( $file , $sys_language_uid ){
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

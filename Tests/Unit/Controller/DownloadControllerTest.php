<?php
namespace PITS\PitsDownloadcenter\Tests\Unit\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 HOJA <hoja.ma@pitsolutions.com>, PIT Solutions Pvt Ltd
 *  			
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class PITS\PitsDownloadcenter\Controller\DownloadController.
 *
 * @author HOJA <hoja.ma@pitsolutions.com>
 */
class DownloadControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \PITS\PitsDownloadcenter\Controller\DownloadController
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = $this->getMock('PITS\\PitsDownloadcenter\\Controller\\DownloadController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function listActionFetchesAllDownloadsFromRepositoryAndAssignsThemToView() {

		$allDownloads = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$downloadRepository = $this->getMock('PITS\\PitsDownloadcenter\\Domain\\Repository\\DownloadRepository', array('findAll'), array(), '', FALSE);
		$downloadRepository->expects($this->once())->method('findAll')->will($this->returnValue($allDownloads));
		$this->inject($this->subject, 'downloadRepository', $downloadRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('downloads', $allDownloads);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}

	/**
	 * @test
	 */
	public function showActionAssignsTheGivenDownloadToView() {
		$download = new \PITS\PitsDownloadcenter\Domain\Model\Download();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('download', $download);

		$this->subject->showAction($download);
	}
}

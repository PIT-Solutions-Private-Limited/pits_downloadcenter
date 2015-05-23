<?php
namespace PITS\PitsDownloadcenter\Domain\Model;


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
 * Category
 */
class Category extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	
	/**
	 * categoryname
	 *
	 * @var string
	 */
	protected $categoryname = '';	

	/**
	* Gets the categoryname.
	*
	* @return string
	*/
	public function getCategoryname(){
		return $this->categoryname;
	}

	/**
	* Sets the categoryname.
	*
	* @param string $categoryname the categoryname
	*
	* @return self
	*/
	public function setCategoryname($categoryname){
		$this->categoryname = $categoryname;
		return $this;
	}

	/**
	 * parentcategory
	 *
	 * @var \PITS\PitsDownloadcenter\Domain\Model\Category
	 */
	protected $parentcategory = '';	

    /**
     * Gets the parentcategory.
     *
     * @return @var \PITS\PitsDownloadcenter\Domain\Model\Category $parentcategory
     */
    public function getParentcategory()
    {
    	return $this->parentcategory;
    }

    /**
     * Sets the parentcategory.
     *
     * @param @var \PITS\PitsDownloadcenter\Domain\Model\Category $parentcategory
     *
     * @return self
     */
    public function setParentcategory($parentcategory)
    {
    	$this->parentcategory = $parentcategory;
    	return $this;
    }
}
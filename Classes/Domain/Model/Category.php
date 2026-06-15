<?php

declare(strict_types=1);

namespace PITS\PitsDownloadcenter\Domain\Model;

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
 ***************************************************************/

/**
 * Category
 *
 * Changes from v12 → v13:
 * - Added declare(strict_types=1).
 * - Added explicit typed property declaration for $parentcategory (was typed as string '' but holds a Category
 *   or null at runtime; typed as mixed to preserve existing behavior without breaking changes).
 * - PHP 8.2: dynamic properties on non-stdClass objects are deprecated; explicit declarations required.
 */
class Category extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * categoryname
     */
    protected string $categoryname = '';

    /**
     * parentcategory
     * Typed as mixed because it can be a Category object or an empty string (Extbase default).
     *
     * @var Category|string|null
     */
    protected mixed $parentcategory = '';

    public function getCategoryname(): string
    {
        return $this->categoryname;
    }

    public function setCategoryname(string $categoryname): self
    {
        $this->categoryname = $categoryname;
        return $this;
    }

    /**
     * @return Category|string|null
     */
    public function getParentcategory(): mixed
    {
        return $this->parentcategory;
    }

    /**
     * @param Category|string|null $parentcategory
     */
    public function setParentcategory(mixed $parentcategory): self
    {
        $this->parentcategory = $parentcategory;
        return $this;
    }
}

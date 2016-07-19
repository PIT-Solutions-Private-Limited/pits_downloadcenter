<?php
namespace PITS\PitsDownloadcenter\Handlers;

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
 * ContentTypeHandler
 */
class ContentTypeHandler{
	/**
	 * getContentType
	 * @param string | extension of the file
	 * @return string
	 */
	public static function getContentType( $extension ){
		switch($extension) {
            case 'txt':
                $cType = 'text/plain'; 
            break;              
            case 'pdf':
                $cType = 'application/pdf'; 
            break;
            case 'exe':
                $cType = 'application/octet-stream';
            break;
            case 'zip':
                $cType = 'application/zip';
            break;
            case 'doc':
                $cType = 'application/msword';
            break;
            case 'xls':
                $cType = 'application/vnd.ms-excel';
            break;
            case 'ppt':
                $cType = 'application/vnd.ms-powerpoint';
            break;
            case 'gif':
                $cType = 'image/gif';
            break;
            case 'png':
                $cType = 'image/png';
            break;
            case 'jpeg':
            case 'jpg':
                $cType = 'image/jpg';
            break;
            case 'mp3':
                $cType = 'audio/mpeg';
            break;
            case 'wav':
                $cType = 'audio/x-wav';
            break;
            case 'mpeg':
            case 'mpg':
            case 'mpe':
                $cType = 'video/mpeg';
            break;
            case 'mov':
                $cType = 'video/quicktime';
            break;
            case 'avi':
                $cType = 'video/x-msvideo';
            break;
            //forbidden filetypes
            case 'inc':
            case 'conf':
            case 'sql':                 
            case 'cgi':
            case 'htaccess':
            case 'php':
            case 'php3':
            case 'php4':                        
            case 'php5':
            exit;
            default:
                $cType = 'application/force-download';
            break;
		}
		return $cType;
	}
}
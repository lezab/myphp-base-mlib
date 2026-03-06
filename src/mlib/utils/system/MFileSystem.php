<?php
/** 
 * This file is part of MyLib
 * Copyright (C) 2016-2025 Denis ELBAZ
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace mlib\utils\system;
/**
 * MFileSystem
 * A class which provide easy static methods missing in native php to deal with filesystem
 * @author : Denis ELBAZ
 * @version : 1.0
 * 
 * @category utils
 */
class MFileSystem{
	
	/**
	 * 
	 * @param string $dir
	 * @throws \UnexpectedValueException si $dir n'est pas accessible ou n'est pas un dossier. 
	 */
	public static function rmdirContent($dir) {
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path){
			$path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
		}
	}
	
	/**
	 * 
	 * @param string $dir
	 * @throws \UnexpectedValueException si $dir n'est pas accessible ou n'est pas un dossier. 
	 */
	public static function rmdir($dir) {
		self::rmdirContent($dir);
		rmdir($dir);
	}
	
	/**
	 * 
	 * @param string $dir le dossier à copier
	 * @param string $destination l'endroit où il doit être copié
	 * @throws UnexpectedValueException si $destination n'est pas accessible ou n'est pas un dossier. 
	 */
	public static function copydir($dir, $destination){
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
		if(! file_exists($destination)){
			mkdir($destination, 0755, true);
		}
        else{
            $destination = $destination.DIRECTORY_SEPARATOR.basename($dir);
            if(! file_exists($destination)){
			    mkdir($destination, 0755, true);
		    }
        }
		foreach($iterator as $item){
			if ($item->isDir()){
				mkdir($destination.DIRECTORY_SEPARATOR.$item->getSubPathName());
			}
			else{
				copy($item, $destination.DIRECTORY_SEPARATOR.$item->getSubPathName());
			}
		}
	}
}
?>

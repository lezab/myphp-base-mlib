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

namespace mlib\utils\cache;
/**
 * class MCache
 * @author Denis ELBAZ
 * @version 1.0.0
 * 
 * @category utils
 */
class MCache{
	private $directory = "";
	private static $cache = true;

	/**
	 * Constructor
	 * @param string $cachedir the directory where objects will be cached
	 */
	public function __construct($cachedir) {
		if(self::$cache){
			if(substr($cachedir, strlen($cachedir) - 1) != DIRECTORY_SEPARATOR){
				$cachedir = $cachedir.DIRECTORY_SEPARATOR;
			}
			$this->directory = $cachedir;
			if(! file_exists($this->directory)){
				mkdir($this->directory, 0755, true);
			}
		}
	}

	public static function disable(){
		self::$cache = false;
	}
	
	/**
	 * Sets a variable in the cache
	 * @param string $key an identifier for the $var in cache
	 * @param mixed $var
	 */
	public function set($key, $var){
		if(self::$cache){
			$file = fopen($this->directory.$key.".cache", "w");
			if($file){
				fwrite($file,base64_encode(serialize($var))); // on passe par un encodage base 64 parce que visiblement le passage par le fichier
															  // fait que dans certains cas on a une erreur à la désérialisation
				fclose($file);
			}
		}
	}

	/**
	 * Gets a variable from the cache
	 * @param string $key the identifier of the variable we want to retrieve
	 * @param integer $timeout the timeout in seconds after which we consider the cache is expired
	 * @return mixed|false the variable from the cache or false if not found (not cached before) or expired.
	 */
	public function get($key, $timeout = null){
		if(self::$cache){
			$filename = $this->directory.$key.".cache";
			if(file_exists($filename)){
				if(!isset($timeout)){
					$content = file($filename);
					$var = unserialize(base64_decode($content[0]));
					return $var;
				}
				else{
					$expiration = time() - $timeout;
					if(filemtime($filename) > $expiration){
						$content = file($filename);
						$var = unserialize(base64_decode($content[0]));
						return $var;
					}
					unlink($filename);
				}
			}
		}
		return false;
	}
	
	/**
	 * Deletes a variable from the cache
	 * @param string $key the identifier of the variable we want to delete
	 */
	public function delete($key){
		if(self::$cache){
			$filename = $this->directory.$key.".cache";
			if(file_exists($filename)){
				unlink($filename);
			}
		}
	}
}
?>
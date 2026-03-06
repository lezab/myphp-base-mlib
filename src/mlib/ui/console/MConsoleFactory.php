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

namespace mlib\ui\console;

class MConsoleFactory {

	protected static $implClass;

	/**
	 * 
	 * @param string $class
	 * @throws \InvalidArgumentException
	 */
	public static function setImplementation($class) {
		if(!class_exists($class)){
			throw new \InvalidArgumentException("Class $class does not exist");
		}
		if(!is_subclass_of($class, MConsole::class)){
			throw new \InvalidArgumentException(sprintf(
									'Class %s must extend %s',
									$class,
									MConsole::class
							));
		}
		static::$implClass = $class;
	}

	/**
	 * 
	 * @param string $class
	 * @param array $args
	 * @return \mlib\ui\console\MConsole
	 * @throws \InvalidArgumentException
	 */
	protected static function createInstance($class, ...$args) {
		$reflection = new \ReflectionClass($class);
		return $reflection->newInstanceArgs($args);
	}

	/**
	 * 
	 * @param string $config_filename
	 * @return \mlib\ui\console\MConsole
	 * @throws MConsoleFactoryException
	 */
	public static function createFromFile(string $config_filename) {
		try{
			$config = new \mlib\utils\config\MConfig($config_filename);
			$class = static::$implClass ?: MConsole::class;
			return self::createInstance($class, $config);
		}
		catch(\mlib\utils\config\MConfigException $e){
			throw new MConsoleFactoryException("Problem with console config file", 1, $e);
		}
	}

	/**
	 * 
	 * @param \mlib\utils\config\MConfig $config
	 * @return \mlib\ui\console\MConsole
	 */
	public static function createFromConfig(\mlib\utils\config\MConfig $config) {
		$class = static::$implClass ?: MConsole::class;
		return self::createInstance($class, $config);
	}
	
	/**
	 * 
	 * @param array $array_config
	 * @return \mlib\ui\console\MConsole
	 * @throws MConsoleFactoryException
	 */
	public static function createFromArray(array $array_config) {
		try{
			$config = new \mlib\utils\config\MConfig(null, $array_config);
			$class = static::$implClass ?: MConsole::class;
			return self::createInstance($class, $config);
		}
		catch(\mlib\utils\config\MConfigException $e){
			throw new MConsoleFactoryException("Problem with console config", 2, $e);
		}
	}
	
	/**
	 * 
	 * @param string $config_filename
	 * @param \mlib\utils\config\MConfig $additional_config
	 * @return \mlib\ui\console\MConsole
	 * @throws MConsoleFactoryException
	 */
	public static function createFromFileAndConfig(string $config_filename, \mlib\utils\config\MConfig $additional_config) {
		try{
			$config = new \mlib\utils\config\MConfig($config_filename);
			$entries = $config->getSubEntriesNames();
			foreach($additional_config->getConfigsNames() as $key){
				if(!in_array($key, $entries)){
					$config->setSubEntry($key, $additional_config->getConfig($key));
				}
				else{
					throw new MConsoleFactoryException("Cannot merge config from file $config_filename and additional config object : some key already exist in the file");
				}
			}
			$class = static::$implClass ?: MConsole::class;
			return self::createInstance($class, $config);
		}
		catch(\mlib\utils\config\MConfigException $e){
			throw new MConsoleFactoryException("Problem with console config file", 3, $e);
		}
	}
	
	/**
	 * 
	 * @param string $config_filename
	 * @param array $additional_array_config
	 * @return \mlib\ui\console\MConsole
	 * @throws MConsoleFactoryException
	 */
	public static function createFromFileAndArray(string $config_filename, array $additional_array_config) {
		try{
			$config = new \mlib\utils\config\MConfig($config_filename, $additional_array_config);
			$class = static::$implClass ?: MConsole::class;
			return self::createInstance($class, $config);
		}
		catch(\mlib\utils\config\MConfigException $e){
			throw new MConsoleFactoryException("Problem with console config file", 4, $e);
		}
	}
}
?>
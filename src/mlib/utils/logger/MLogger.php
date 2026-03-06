<?php
/**
 * This file is part of MyLib
 * Copyright (C) 2018-2025 Denis ELBAZ
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

namespace mlib\utils\logger;

abstract class MLogger{
	static private $_configuration_path = null;
	static private $_configuration = null;
    static private $_instances;
 
	const LEVEL_EMERGENCY = 0;
	const LEVEL_ALERT     = 1;
	const LEVEL_CRITICAL  = 2;
	const LEVEL_ERROR     = 3;
	const LEVEL_WARNING   = 4;
	const LEVEL_NOTICE    = 5;
	const LEVEL_INFO      = 6;
	const LEVEL_DEBUG     = 7;
	
	const LABELS = array(0 => 'EMERGENCY', 1 => 'ALERT', 2 => 'CRITICAL', 3 => 'ERROR', 4 => 'WARNING', 5 => 'NOTICE', 6 => 'INFO',7 => 'DEBUG');
	
	const TYPE_FILE     = 'file';
	const TYPE_SYSTEM   = 'errorlog';
	const TYPE_ERRORLOG = 'errorlog';
	const TYPE_SYSLOG   = 'syslog';
	const TYPE_MYSQL    = 'mysql';
	
	// default level
	protected $level = 'warning';
	
    abstract function log($level, $message);
	
	static protected function getLevelLabel($level){
		switch($level){
			case MLogger::LEVEL_EMERGENCY:
				return 'EMERGENCY';
			case MLogger::LEVEL_ALERT :
				return 'ALERT';
			case MLogger::LEVEL_CRITICAL :
				return 'CRITICAL';
			case MLogger::LEVEL_ERROR :
				return 'ERROR';
			case MLogger::LEVEL_WARNING :
				return 'WARNING';
			case MLogger::LEVEL_NOTICE :
				return 'NOTICE';
			case MLogger::LEVEL_INFO :
				return 'INFO';
			case MLogger::LEVEL_DEBUG :
				return 'DEBUG';
			default:
				return 'UNKNOWN';
		}
	}
	
	static protected function stringify($var){
		if(is_array($var)){
			return print_r($var, true);
		}
		if(is_object($var)){
			$class = new \ReflectionClass(get_class($var));
			if($class->hasMethod('toString')){
				return $var->toString();
			}
			elseif($class->hasMethod('to_string')){
				return $var->to_string();
			}
			elseif(method_exists($var, '__toString')){
				return $var;
			}
			return print_r($var, true);
		}
		return $var;
	}
	
	static public function setConfigurationFile($path){
		self::$_configuration_path = $path;
	}
	static private function loadConfiguration(){
		if(self::$_configuration_path == null){
			self::$_configuration_path = __DIR__.'/resources/mlogger/mlogger.conf.php';
		}
		include_once(self::$_configuration_path);
		//TODO : check configuration
		/*if(! isset($options['file'])){
			throw new MLoggerException("'file' option must be set to instantiate a file logger");
		}*/
		self::$_configuration = $mloggers;
	}
 
    /**
     *
     * @param string $name
     * @param string $type
     * @return MLogger
     */
    static public function getInstance($name){
		if(! isset(self::$_configuration)){
			//throw new MLoggerException("No logger configuration loaded", 1, $e);
			self::loadConfiguration();
		}
        if(! isset(self::$_instances[$name])){
			if(! isset(self::$_configuration[$name])){
				throw new MLoggerException("Unknown logger $name. See mlogger configuration file.", 0);
			}
			
			try{
				
				switch(self::$_configuration[$name]['type']){
					case MLogger::TYPE_SYSTEM :
					case MLogger::TYPE_ERRORLOG :
						self::$_instances[$name] = new MErrorLogLogger(self::$_configuration[$name]['level']);
						break;
					case MLogger::TYPE_FILE :
						self::$_instances[$name] = new MFileLogger(self::$_configuration[$name]['level'], self::$_configuration[$name]['options']);
						break;
					case MLogger::TYPE_SYSLOG :
						self::$_instances[$name] = new MSyslogLogger(self::$_configuration[$name]['level'], self::$_configuration[$name]['options']);
						break;
					case MLogger::TYPE_MYSQL :
						self::$_instances[$name] = new MMysqlLogger(self::$_configuration[$name]['level'], self::$_configuration[$name]['options']);
						break;
					default:
						throw new MLoggerException("Unknown logger type : ".self::$_configuration[$name]['type']);
						break;
				}
			}
			catch(\Exception $e){
				throw new MLoggerException("Problem encountered while instantiating logger", 1, $e);
			}
        }
        return self::$_instances[$name];
    }
	
	
	public function emergency($message){
		$this->log(MLogger::LEVEL_EMERGENCY, $message);
	}
	
	public function alert($message){
		$this->log(MLogger::LEVEL_ALERT, $message);
	}
	
	public function critical($message){
		$this->log(MLogger::LEVEL_CRITICAL, $message);
	}
	
	public function error($message){
		$this->log(MLogger::LEVEL_ERROR, $message);
	}
	
	public function warning($message){
		$this->log(MLogger::LEVEL_WARNING, $message);
	}
	
	public function notice($message){
		$this->log(MLogger::LEVEL_NOTICE, $message);
	}
	
	public function info($message){
		$this->log(MLogger::LEVEL_INFO, $message);
	}
	
	public function debug($message){
		$this->log(MLogger::LEVEL_DEBUG, $message);
	}
}
?>
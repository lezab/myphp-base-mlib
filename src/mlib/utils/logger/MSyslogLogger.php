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

class MSyslogLogger extends MLogger{
    
    public function __construct($level){
		$this->level = $level;
    }
 
    public function log($level, $message) {
		if($level <= $this->level){
			$priority = $this->levelToSyslogPriority($level);
			syslog($priority, self::stringify($message));
		}
    }
    
    private function levelToSyslogPriority($level) {
		switch($level) {
			case MLogger::LEVEL_EMERGENCY: return LOG_EMERG;
			case MLogger::LEVEL_ALERT:     return LOG_ALERT;
			case MLogger::LEVEL_CRITICAL:  return LOG_CRIT;
			case MLogger::LEVEL_ERROR:     return LOG_ERR;
			case MLogger::LEVEL_WARNING:   return LOG_WARNING;
			case MLogger::LEVEL_NOTICE:    return LOG_NOTICE;
			case MLogger::LEVEL_INFO:      return LOG_INFO;
			case MLogger::LEVEL_DEBUG:     return LOG_DEBUG;
			default:                       return LOG_INFO;
		}
	}
}
?>
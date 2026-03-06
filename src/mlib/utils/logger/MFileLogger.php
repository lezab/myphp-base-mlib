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

class MFileLogger extends MLogger{
	private $file;
    private $filename;
	private $rotateSize = 10485760; //10 Mo
    private $rotateFilesNumber = 10;
	
    public function __construct($level, $options){
		$this->level = $level;
		$this->filename = $options['filename'];
		if(isset($options['rotate_size'])){
			$this->rotateSize = $options['rotate_size'];
		}
		if(isset($options['rotate_files'])){
			$this->rotateFilesNumber = $options['rotate_files'];
		}		
		$this->rotateIfNeeded();
		
    }
 
    public function __destruct(){
        fclose($this->file);
    }
	
	public function log($level, $message) {
		if($level <= $this->level){
			flock($this->file, LOCK_EX);
			$this->file = fopen($this->filename, 'a');
			fwrite($this->file, date('Y-m-d H:i:s:u')." :\t[".self::getLevelLabel($level)."]".self::stringify($message).PHP_EOL);
			fflush($this->file);
			flock($this->file, LOCK_UN);
		}
    }

    private function rotateIfNeeded(){
        clearstatcache(true, $this->filename);
        if(file_exists($this->filename) && (filesize($this->filename) >= $this->rotateSize)){
			// lock rotation with a lockfile to avoid races
			$lockFile = $this->filename . '.lock';
			$lf = fopen($lockFile, 'c');
			if($lf === false) return;
			if(! flock($lf, LOCK_EX)){
				fclose($lf); return;
			}
			// recheck after acquiring lock
			clearstatcache(true, $this->filename);
			if (file_exists($this->filename) && filesize($this->filename) >= $this->rotateSize) {
				// rotate existing files: log.4 -> log.5, log.3 -> log.4, ..., log -> log.1
				for ($i = $this->rotateFilesNumber - 1; $i >= 0; $i--) {
					$src = $i === 0 ? $this->filename : $this->filename . '.' . $i;
					$dst = $this->filename . '.' . ($i + 1);
					if (file_exists($src)) {
						@rename($src, $dst);
					}
				}
			}
			flock($lf, LOCK_UN);
			fclose($lf);
			@unlink($lockFile);
		}
    }
}
?>
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

/**
 * MConsolePrinterFactoryException
 * Exception class relative to MConsolePrinterFactory
 */
class MConsolePrinterFactoryException extends \Exception {
	
	/**
	 * Constructor
	 * @param string $msg
	 * @param integer $code
	 * @param Exception $e
	 */
	public function __construct($msg, $code=0, $e=null) {
		parent::__construct($msg, $code, $e);
	}
}
?>

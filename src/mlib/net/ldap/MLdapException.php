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

namespace mlib\net\ldap;
/**
 * Class MLdapException
 * Exception relative to MLdap
 * Most of the time the code of the exception is the the code error of the ldap underlying function.
 * 
 * @category net
 */
class MLdapException extends \Exception {
	public function __construct($msg, $code=0) {
		parent :: __construct($msg, $code);
	}
}
?>
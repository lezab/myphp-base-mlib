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

namespace mlib\utils\string;
/**
 * MString
 * A class with only static method for helpfull operations on strings
 * @author : Denis ELBAZ
 * @version : 1.0.1
 * 
 * @category utils
 */
class MString{
	
	/**
	 * Replaces common letters with accents (or other diacritics) by their equivalent without.
	 * Also separates ligatures
	 * Finally replaces spaces and quotes by glue
	 * This function is particularly usefull to obtain a file name based on a personal name.
	 * @param String $string
	 * @param String $glue
	 * @return String
	 */
	static public function cleanAndGlue($string, $glue = '-'){
		$string = htmlentities($string, ENT_NOQUOTES, 'utf-8');
		$string = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|dot|grave|ring|slash|tilde|uml);#', '\1', $string);
		// acute : with acute        : accent aigue
		// cedil : with cedilla      : cédille
		// caron :                   : accent circonflexe inversé
		// circ  : with circumflex   : accent circonflexe
		// dot   : with dot          : point
		// grave : with grave        : accent grave
		// ring  : with a ring above : petit rond au dessus
		// slash : with stroke       : lettre barrée
		// tilde : with tilde        : tilde
		// uml   : with diaeresis    : trema
		$string = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $string );
		// lig   : ligature          : ligature (ae ou oe)
		$string = join($glue, explode(' ',$string));
		$string = join($glue, explode("'",$string));
		return $string;
	}
	
	/**
	 * Try to "htmlize" text ie. replace LF with <br> for example
	 * @param String $string
	 * @return String
	 */
	static public function toHtml($string){
		$html = htmlentities($string, ENT_QUOTES, 'utf-8');
		$html = str_replace("\n\r","<br>",$html);
		$html = str_replace("\r\n","<br>",$html);
		$html = str_replace("\n","<br>",$html);
		$html = str_replace("\r","<br>",$html);	
		$html = str_replace("  ","&nbsp;&nbsp;",$html);
		$html = str_replace("--&gt;","&rarr;",$html);
		$html = str_replace("&lt;--","&larr;",$html);
		$html = str_replace("&lt;==&gt;","&hArr;",$html);
		$html = str_replace("==&gt;","&rArr;",$html);
		$html = str_replace("&lt;==","&lArr;",$html);
		$html = str_replace("!=","&ne;",$html);
		return $html;
	}
	
	/**
	 * @param $haystack String
	 * @param $needle String
	 * @param $ignoreCase boolean optional default false
	 * @return boolean if $haystack starts with $needle or not
	 */
	static public function startsWith($haystack, $needle, $ignoreCase = false){
		if($ignoreCase){
			return substr_compare($haystack, $needle, 0, strlen($needle), true) === 0;
		}
		else{
			return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
		}
	}
	
	/**
	 * @param $haystack String
	 * @param $needle String
	 * @param $ignoreCase boolean optional default false
	 * @return boolean if $haystack ends with $needle or not
	 */
	static public function endsWith($haystack, $needle, $ignoreCase = false){
		if($ignoreCase){
			$length = strlen($needle);
			return substr_compare($haystack, $needle, -$length, $length, true) === 0;
		}
		else{
			return substr_compare($haystack, $needle, -strlen($needle)) === 0;
		}
	}
	
	/**
	 * @param $haystack String
	 * @param $needle String
	 * @param $ignoreCase boolean optional default false
	 * @return boolean if $haystack contains $needle or not
	 */
	static public function contains($haystack, $needle, $ignoreCase = false){
		if($ignoreCase){
			return (stripos($haystack, $needle) !== false);
		}
		else{
			return (strpos($haystack, $needle) !== false);
		}
	}
	
	/**
	 * Fill a string with indicated character before, to obtain a string of indicated length
	 * @param $input the string to standardize
	 * @param $nbChars characters length of the result expected
	 * @param $char the character we want to fill the string with
	 * @return string filled string or input string unchanged if string length is already equals or more than the requested fnal length
	 */
	static public function standardizeByLeading($input, $nbChars, $char = " ") {
		$value = $input;
		while(strlen($value) < $nbChars){
			$value = $char.$value;
		}
		return $value;
	}
	
	/**
	 * Fill a string with indicated character after, to obtain a string of indicated length
	 * @param $input the string to standardize
	 * @param $nbChars characters length of the result expected
	 * @param $char the character we want to fill the string with
	 * @return string filled string or input string unchanged if string length is already equals or more than the requested fnal length
	 */
	static public function standardizeByTrailing($input, $nbChars, $char = " ") {
		$value = $input;
		while(strlen($value) < $nbChars){
			$value = $value.$char;
		}
		return $value;
	}
}
?>
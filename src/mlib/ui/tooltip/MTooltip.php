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

namespace mlib\ui\tooltip;
/**
 * MTooltip
 * A class to display "tooltiped" content easily
 * @author : Denis ELBAZ
 * @version : 1.0.0
 * 
 * @category view
 */
class MTooltip{
	
	static private $next_tooltip_index = 0;
	static private $ASSETS_PATH = null;
    
	
	/**
	 * 
	 * @param string $path
	 */
	static public function registerAssetsPath($path){
		self::$ASSETS_PATH = $path;
	}
	
	
	/**
	 * 
	 * @param string $content
	 * @param string $tooltip_content
	 */
	static public function printTooltipedContent($content, $tooltip_content){
		if(self::$next_tooltip_index == 0){
			echo "<link type=\"text/css\" href=\"".self::$ASSETS_PATH."/MTooltip.css\" rel=\"StyleSheet\" />\n";
			echo "<script src=\"".self::$ASSETS_PATH."/MTooltip.js\" type=\"text/javascript\"></script>\n";
		}
		echo self::getTooltipedContent($content, $tooltip_content);
	}
	
	/**
	 * 
	 * @param type $content
	 * @param type $tooltip_content
	 * @return string
	 */
	static public function getTooltipedContent($content, $tooltip_content){
		$tooltip_id = 'mt'.self::$next_tooltip_index++;
			
		$return  = "<span class=\"mtooltiped\" onmouseover=\"MTooltip.showtooltip('$tooltip_id');\" onmouseout=\"MTooltip.hidetooltip('$tooltip_id');\">";
		$return .= $content;
		$return .= "</span>";
		$return .= "<div class=\"mtooltip\" id='$tooltip_id'>";
		$return .= self::get_html_from_text($tooltip_content);
		$return .= "</div>";

		return $return;
	}
	
	/**
	 * 
	 */
	static public function printPrerequisite(){
		echo "<link type=\"text/css\" href=\"".self::$ASSETS_PATH."/MTooltip.css\" rel=\"StyleSheet\" />\n";
		echo "<script src=\"".self::$ASSETS_PATH."/MTooltip.js\" type=\"text/javascript\"></script>\n";
		self::$next_tooltip_index = 1;
	}
	
	/**
	 * 
	 * @return string
	 */
	static public function getPrerequisite(){
		$return  = "<link type=\"text/css\" href=\"".self::$ASSETS_PATH."/MTooltip.css\" rel=\"StyleSheet\" />\n";
		$return .= "<script src=\"".self::$ASSETS_PATH."/MTooltip.js\" type=\"text/javascript\"></script>\n";
		return $return;
	}
	
	static private function get_html_from_text($text){
		$html = htmlentities($text, ENT_QUOTES, 'UTF-8');
		$html = str_replace("\n\r","<br>",$html);
		$html = str_replace("\r\n","<br>",$html);
		$html = str_replace("\n","<br>",$html);
		$html = str_replace("\r","<br>",$html);
		return $html;
	}
}
?>
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

namespace mlib\ui\autocomplete;
/**
 * MAutoComplete
 * @author Denis ELBAZ
 * @version 1.0.0
 * 
 * @category view
 */
class MAutoComplete {

	static private $ASSETS_PATH = null;

	private static $must_send_assets_references = true;	

	/**
	 * 
	 * @param string $path the relative path to the directory containing js, css and images
	 */
	public static function registerAssetsPath($path){
		self::$ASSETS_PATH = preg_replace('/\/$/', '', $path);
	}
	
	/**
	 * Forces the inclusion of CSS and JavaScript assets in case of usage in a layout or template.
	 * In such a case, the display method could be called after it has been called in the rendered view, then 
	 * the assets would not be included.
	 */
	public static function forceAssetsInclusion(){
		if(self::$ASSETS_PATH !== null){
			echo "<link rel=\"StyleSheet\" href=\"".self::$ASSETS_PATH."/MAutoComplete.css\" type=\"text/css\">\n";
			echo "<script type=\"module\">import MAutoComplete from \"".self::$ASSETS_PATH."/MAutoComplete.js\"; window.MAutoComplete = MAutoComplete;</script>\n";
			self::$must_send_assets_references = false;
		}
	}
	
	
	/**
	 * @param type $id the id of the input
	 * @param string $search_url the url for search : the processor at this url should return a json encoded array
	 * @param array $functions the names of javascript functions linked to the autocomplete field.
	 *		$functions['onSelect'] : mandatory : what happens when a suggestion is selected
	 *		$functions['renderResult'] : a javascript function which is called to render suggestions
	 *		$functions['onValidate'] : if autocomplete field is told to display a button (see options), a javascript function which is called when button clicked on
	 * @param array $options an assotiative array. Possible keys :
	 *		button : boolean : if a button should be displayed next to the autocomplete field (default : false)
	 *		button_label : if 'button option is true, ignore otherwise. Default : 'Go'
	 *		button_icon : if 'button option is true, the icon url (button_label' is then img title attribute).
	 *		button_color : a color in format #hhhhhh
	 *		button_text_color : a color in format #hhhhhh
	 *		placeholder : what is displayed when the field is empty. Default : 'Search ...'
	 *		min_chars : the minimum of typed chars for $search_url is called. Default : 2
	 *		empty_message : the message displayed when no suggestions are return. Default : 'No result'
	 *		width : the total width (css style with unit) with button included. Default : 350px
	 */
	public static function display($id, $search_url, $functions, $options = null) {		
		if(self::$ASSETS_PATH === null){
			throw new \Exception('Assets path not registered. Call MAutoComplete::registerAssetsPath() first.');
		}
		
		if(self::$must_send_assets_references){
			echo "<link rel=\"StyleSheet\" href=\"".self::$ASSETS_PATH."/MAutoComplete.css\" type=\"text/css\">\n";
			echo "<script type=\"module\">import MAutoComplete from \"".self::$ASSETS_PATH."/MAutoComplete.js\"; window.MAutoComplete = MAutoComplete;</script>\n";
			self::$must_send_assets_references = false;
		}
			
		$placeholder = isset($options['placeholder']) ? $options['placeholder'] : 'Search ...';
		$width = isset($options['width']) ? $options['width'] : '350px';
		$min_chars = isset($options['min_chars']) ? ($options['min_chars'] > 0 ? $options['min_chars'] : 2) : 2;
		$empty_message = isset($options['empty_message']) ? $options['empty_message'] : 'No result';
		$button = isset($options['button']) ? $options['button'] : false;
		if($button){
			$button_label = isset($options['button_label']) ? $options['button_label'] : 'Go';
			$button_icon = isset($options['button_icon']) ? $options['button_icon'] : null;
			$button_color = isset($options['button_color']) ? $options['button_color'] : null;
			$button_text_color = isset($options['button_text_color']) ? $options['button_text_color'] : null;
		}
		
		echo "<div class='mautocomplete_wrapper' style=\"width:$width\">";
		echo "<div class='mautocomplete_search' role='search'>";

		$button_style = "";
		if(isset($button_color) || isset($button_text_color)){
			$button_style = " style=\"";
			if(isset($button_color)){
				$button_style .= "background-color: $button_color;";
			}
			if(isset($button_text_color)){
				$button_style .= "color: $button_text_color;";
			}
			$button_style .= "\"";
		}

		if($button){
			echo "<input type='text' name='$id' id='$id' placeholder=\"$placeholder\" autocomplete=\"off\" />";
			if(isset($button_icon)){
				echo "<button class=mautocomplete_button$button_style title=$button_label onClick=\"".$functions['onValidate']."();\"><img src='$button_icon'></img></button>";
			}
			else{
				echo "<button class=mautocomplete_button$button_style onClick=\"".$functions['onValidate']."();\">$button_label</button>";
			}
		}
		else{
			echo "<input type='text' class=no_button name='$id' id='$id' placeholder=\"$placeholder\" autocomplete=\"off\" />";
		}
		echo "</div>";
		echo "</div>";
		$renderResultCallback = isset($functions['renderResult']) ? $functions['renderResult'] : 'null';
		echo "<script>document.addEventListener('DOMContentLoaded', () => {
            MAutoComplete.bind('$id','$search_url', $min_chars, ".$functions['onSelect'].", $renderResultCallback, '$empty_message');
        });</script>";
	}
}
?>
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

namespace mlib\ui\ajaxzone;
/**
 * MAjaxZone
 * A class to easily display a button which load ajax content when clicked
 * Several options could be added to reload or hide the loaded content
 * @author Denis ELBAZ
 * @version 1.0.0
 * 
 * @category view
 */
class MAjaxZone {

	static private $next_zone_index = 0;
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
	 * Displays the button and the zone.
	 * The zone is hidden first.
	 *
	 * @param string $button_label
	 * @param string $url the url to display in the ajax zone
	 * @param array $options an assotiative array. Possible keys :
	 *		hideonclick	(true / false) : if the zone should be hidden when clicking on the button while the zone is displayed
	 *									 the default behaviour is to refresh the zone
	 *		visible_button_label : if hide on click, the button label can can change when the zone is displayed
	 *		button_align (left/center/right) : the button alignment over the ajax zone (default left)
	 *		align (left/center/right) : the alignment of the ajax zone (default left)
	 *		button_color : a color in format #hhhhhh
	 *		button_text_color : a color in format #hhhhhh
	 */
	public static function display($button_label, $url, $options = null) {
		if(self::$must_send_assets_references){
			echo "<link rel=\"StyleSheet\" href=\"".self::$ASSETS_PATH."/MAjaxZone.css\" type=\"text/css\">\n";
			echo "<script type=\"text/javascript\" src=\"".self::$ASSETS_PATH."/MAjaxZone.js\"></script>\n";		
			
			self::$must_send_assets_references = false;
		}
		
		$zone_id = 'maz'.self::$next_zone_index++;
		
		if(isset($options) && is_array($options)){
			$hideonclick = isset($options['hideonclick']) ? $options['hideonclick'] : false;
			$visible_button_label = isset($options['visible_button_label']) ? $options['visible_button_label'] : $button_label;
			$button_align = isset($options['button_align']) ? $options['button_align'] : 'left';
			$align = isset($options['align']) ? $options['align'] : 'left';
			$button_color = isset($options['button_color']) ? $options['button_color'] : null;
			$button_text_color = isset($options['button_text_color']) ? $options['button_text_color'] : null;
			
			echo "<div align='$button_align'>";
			
			$button_style = "";
			if(isset($button_color) || isset($button_text_color)){
				$button_style = " style=\"";
				if(isset($button_color)){
					$button_style .= "background-color: $button_color;";
				}
				if(isset($button_text_color)){
					$button_style .= "background-color: $button_text_color;";
				}
				$button_style .= "\"";
			}
			if($hideonclick){
				echo "<button class=majax_zone_button$button_style onclick=\"MAjaxZone.showhide('".$zone_id."', '$url', this);\" label1='$button_label' label2='$visible_button_label'>$button_label</button>";
			}
			else{
				if($visible_button_label != $button_label){
					echo "<button class=majax_zone_button$button_style onclick=\"MAjaxZone.refresh('".$zone_id."', '$url', this);\" label2='$visible_button_label'>$button_label</button>";
				}
				else{
					echo "<button class=majax_zone_button$button_style onclick=\"MAjaxZone.refresh('".$zone_id."', '$url');\">$button_label</button>";
				}
			}
			echo "<div class=majax_zone id='$zone_id' align='$align'></div>";
			echo "</div>";
		}
		else{
			echo "<button class=majax_zone_button onclick=\"MAjaxZone.refresh('".$zone_id."', '$url');\">$button_label</button>";
			echo "<div class=majax_zone id='$zone_id'></div>";
		}
	}
}
?>
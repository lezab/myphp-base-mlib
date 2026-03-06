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
 * MConsole
 * A class to display console tables easily.
 * @author Denis ELBAZ
 * @version 1.4.4
 * 
 * @category view
 */
class MConsole {
	
	protected $name = null;
	
	protected $title = null;
	protected $title_print = true;
	
	protected $width = null;
	protected $min_width = null;
	
	protected $datas = null;
	protected $columns = array();
	
	protected $ASSETS_PATH = null;
	
	protected $delete = null;
	protected $delete_url = null;
	
	protected $modify = null;
	protected $modify_url = null;
	
	protected $details = null;
	protected $details_window_url = null;
	protected $details_window_title = null;
	protected $details_window_width = null;
	protected $details_window_height = null;
	
	protected $custom = null;
	
	protected $expandable = false;
	protected $expand_url = null;
	
	protected $printable = false;
	protected $printable_cols = null;
	
	protected $exportable = false;
	protected $exportable_cols = null;
	protected $exportable_filename = null;
	
	protected $toolbar = null;
	
	protected $footer_groups = null;
	protected $footer_datas = null;
	
	
	protected $deleteIcon = null;
	protected $modifyIcon = null;
	protected $detailsIcon = null;
	
	
	protected static $must_send_assets_references = true;
	
	/**
	 * Constructor
	 * @param \mlib\utils\config\MConfig $config
	 */
	public function __construct(\mlib\utils\config\MConfig $config) {
		
		$name = $config->getParameter('name');
		if(isset($name) && ($name != "")){
			$this->name = $name;
		}
		
		$width = $config->getParameter('width');
		if(isset($width) && ($width != "")){
			$this->width = $width;
		}
		else{
			$min_width = $config->getParameter('min_width');
			if(isset($min_width) && ($min_width != "")){
				$this->min_width = $min_width;
			}
		}
		
		$tmpconfig = $config->getConfig("columns");
		$console_cols = $tmpconfig->getConfigsNames();
		$colindex = 0;
		foreach ($console_cols as $column) {
			$this->columns[$colindex] = array();
			$cconf = $tmpconfig->getConfig($column);
			$this->columns[$colindex]['id'] = $column;
			$type = $cconf->getParameter('type');
			$this->columns[$colindex]['type'] = $type;
			$this->columns[$colindex]['label'] = $cconf->getParameter('label');
			
			$default_width = (($type == 'details') || ($type == 'delete') || ($type == 'status') || ($type == 'modify')) ? '40px' : false;
			$width = $cconf->getParameter('width');
			$this->columns[$colindex]['width'] = isset($width) ? $width.'px' : $default_width;
			$maxwidth = $cconf->getParameter('max_width');
			$this->columns[$colindex]['max_width'] = (isset($maxwidth) && (! $default_width)) ? $maxwidth.'px' : false;
			
			
			$responsive = $cconf->getParameter('responsive');
			$this->columns[$colindex]['responsive'] = isset($responsive);
			if(isset($responsive)){
				$this->columns[$colindex]['responsive_level'] = $responsive;
			}
			
			// For retro-compatibility only. You shoudl not use max_characters parameter anymore
			if(($type == 'data') || ($type == 'link') || ($type == 'mailto')){
				$max_char = $cconf->getParameter('max_characters');
				if(isset($max_char) && ($this->columns[$colindex]['max_width'] === false)){
					// 6.6 is a mean value. In real it could vary, depending on charachters and number of uppercase characters used
					$this->columns[$colindex]['max_width'] = floor($max_char * 6.6).'px';
				}
			}
			
			$align = $cconf->getParameter('align');
			if(isset($align)){
				$this->columns[$colindex]['align'] = $align;
			}
			else{
				$center = $cconf->getParameter('center');
				$default_align = (($type == 'details') || ($type == 'delete') || ($type == 'status') || ($type == 'modify')) ? "center" : "left";
				$this->columns[$colindex]['align'] = isset($center) ? "center" : $default_align;
			}
			
			$sortable = $cconf->getParameter('sortable');
			$this->columns[$colindex]['sortable'] = isset($sortable) ? $sortable : false;
			if($this->columns[$colindex]['sortable']){
				$this->columns[$colindex]['sort_method'] = $cconf->getParameter('sort_method');
				if($this->columns[$colindex]['sort_method'] == 'server'){
					$this->columns[$colindex]['sort_url'] = $cconf->getParameter('sort_url');
				}
			}
			
			if($this->columns[$colindex]['type'] == 'delete'){
				$this->delete = true;
				$this->delete_url = $cconf->getParameter('url');
			}
			elseif($this->columns[$colindex]['type'] == 'modify'){
				$this->modify = true;
				$this->modify_url = $cconf->getParameter('url');
				$this->modify_url = strpos($this->modify_url, '?') === false ? $this->modify_url.'?' : $this->modify_url.'&';
			}
			elseif($this->columns[$colindex]['type'] == 'details'){
				$this->details = true;
				$this->details_window_url = $cconf->getParameter('url');
				$this->details_window_title = $cconf->getParameter('title');
				$this->details_window_width = $cconf->getParameter('window_width');
				$this->details_window_height = $cconf->getParameter('window_height');
			}
			elseif($this->columns[$colindex]['type'] == 'mailto'){
				$encoded = $cconf->getParameter('encoded');
				$this->columns[$colindex]['encoded'] = isset($encoded) ? $encoded : false;
			}
			elseif($this->columns[$colindex]['type'] == 'custom'){
				$this->custom = true;
			}
			$colindex++;
		}
		
		$expandable = $config->getParameter('expandable');
		if(isset($expandable) && ($expandable == true)){
			$this->expandable = true;
			$this->expand_url = $config->getParameter('expand_url');
		}
		
		
		$tmpconfig = $config->getConfig("toolbar");
		if(isset($tmpconfig)){
			$this->toolbar = array();
			$tools_names = $tmpconfig->getConfigsNames();
			foreach ($tools_names as $tool_name) {
				$tool_config = $tmpconfig->getConfig($tool_name);
				$this->toolbar[$tool_name] = $tool_config->toArray();
				
				if($tool_name == 'native_printable'){
					$this->printable = true;
					$this->printable_cols = explode(',', $this->toolbar[$tool_name]['columns']);
				}
				
				if($tool_name == 'native_export'){
					$this->exportable = true;
					$this->exportable_cols = explode(',', $this->toolbar[$tool_name]['columns']);
					$this->exportable_filename = $this->toolbar[$tool_name]['filename'];
				}
			}
		}
	
		$tmpconfig = $config->getConfig("footer");
		if(isset($tmpconfig)){
			$this->footer_groups = array();
			$groups = explode(',', $tmpconfig->getParameter('colgroups'));
			$aligns = explode(',', $tmpconfig->getParameter('align'));
			for($i = 0; $i < count($groups); $i++) {
				$this->footer_groups[$i] = array('nb_cols' => $groups[$i], 'align' => $aligns[$i]);
			}
		}
	}
	
	
	/**
	 * 
	 * @param string $path the relative path to the directory containing js, css and images for MConsole
	 */
	public function registerAssetsPath($path){
		$this->ASSETS_PATH = preg_replace('/\/$/', '', $path);
	}
	
	/**
	 * 
	 * @param string $title a title for the console. Used only for a display goal.
	 * @param boolean $printable_only says if the title should be displayed only in printable version window
	 */
	public function setTitle($title, $printable_only = false) {
		$this->title = $title;
		$this->title_print = !$printable_only;
	}
	
	/**
	 * 
	 * @param string $name sets a name the the MConsole object. Usefull only if multiple consoles have
	 *	to be displayed in the same page
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setDeleteUrl($value){
		if($this->delete){
			$this->delete_url = $value;
		}
	}
	
	public function setModifyUrl($value){
		if($this->modify){
			$this->modify_url = $value;
		}
	}
	
	public function setDetailsUrl($value){
		if($this->details){
			$this->details_window_url = $value;
		}
	}
	
	public function setToolbarAddUrl($value){
		if(isset($this->toolbar['add'])){
			$this->toolbar['add']['url'] = $value;
		}
	}
	
	public function setToolbarPdfUrl($value){
		if(isset($this->toolbar['pdf'])){
			$this->toolbar['pdf']['url'] = $value;
		}
	}
	
	public function setToolbarExportUrl($value){
		if(isset($this->toolbar['export'])){
			$this->toolbar['export']['url'] = $value;
		}
	}
	
	public function setToolbarSpeadsheetUrl($value){
		if(isset($this->toolbar['speadsheet'])){
			$this->toolbar['speadsheet']['url'] = $value;
		}
	}
	
	public function setToolbarUrlFor($toolname, $value){
		if(isset($this->toolbar[$toolname])){
			$this->toolbar[$toolname]['url'] = $value;
		}
	}
	
	/**
	 * 
	 * @param array $datas the datas for the console
	 */
	public function setData($datas) {
		$this->datas = $datas;
	}
	
	/**
	 * 
	 * @param array $datas the datas for the console
	 */
	public function setDatas($datas) {
		$this->datas = $datas;
	}
	
	public function setFooterDatas($datas) {
		$this->footer_datas = $datas;
	}
	
	protected function printJavascript() {
		$datas = $this->datas;
		
		echo "<script language=\"javascript\">\n";
		echo "var mconsole = new MConsole();\n";
		//echo "mconsole.setStylePath('".$this->ASSETS_PATH."');\n";
		if(isset($this->title)){
			echo "mconsole.setTitle(\"".$this->title."\");\n";
		}
		if(isset($this->name)){
			echo "mconsole.setName('".$this->name."');\n";
		}
		if($this->details){
			if(isset($this->details_window_url)){
				echo "mconsole.setDetailsWindowUrl('".$this->details_window_url."');\n";
			}
			if(isset($this->details_window_title)){
				echo "mconsole.setDetailsWindowTitle(\"".$this->details_window_title."\");\n";
			}
			if(isset($this->details_window_width) && isset($this->details_window_height)){
				echo "mconsole.setDetailsWindowDimensions(".$this->details_window_width.", ".$this->details_window_height.");\n";
			}
		}
		if($this->delete){
			echo "mconsole.setDeleteUrl('".$this->delete_url."');\n";
		}
		if($this->expandable){
			echo "mconsole.setExpandUrl('".$this->expand_url."');\n";
		}
		
		if(($this->details) || ($this->delete) || ($this->modify) || ($this->custom)){
			echo "var datas = new Array();\n";
			
			for($i=0; $i<count($datas); $i++){
				echo "var data = new Array();\n";
				
				if($this->details && (!isset($this->details_window_url))){
					
					echo "data['console_details'] = new Array();\n";
					for($j=0; $j<count($datas[$i]['console_details']); $j++){
						echo "var group_of_values = new Array();\n";
						$labels = array_keys($datas[$i]['console_details'][$j]);

						foreach($labels as $label){
							if(is_array($datas[$i]['console_details'][$j][$label])){
								echo "group_of_values[\"$label\"] = new Array();\n";
								$k = 0;
								foreach($datas[$i]['console_details'][$j][$label] as $value){
									echo "group_of_values[\"$label\"][".$k++."] = \"$value\";\n";
								}
							}
							else{
								echo "group_of_values[\"$label\"] = \"".$datas[$i]['console_details'][$j][$label]."\";\n";
							}
						}
						echo "data['console_details'][$j] = group_of_values;\n";
					}
				}
				
				echo "data['console_display'] = \"".str_replace('"', '\"',$datas[$i]['console_display'])."\";\n";
				echo "datas['".$datas[$i]['console_id']."'] = data;\n";
			}
			
			echo "mconsole.setDatas(datas);\n";
		}
			
		
		if($this->printable){
			$params_str = "new Array(";
			$params_str .= "'".$this->printable_cols[0]."'";
			for($i=1; $i<count($this->printable_cols); $i++){
				$params_str .= ",'".$this->printable_cols[$i]."'";
			}
			$params_str .= ")";
			echo "mconsole.setPrintableColumns($params_str);\n";
		}
		
		if($this->exportable){
			$params_str = "new Array(";
			$params_str .= "'".$this->exportable_cols[0]."'";
			for($i=1; $i<count($this->exportable_cols); $i++){
				$params_str .= ",'".$this->exportable_cols[$i]."'";
			}
			$params_str .= ")";
			echo "mconsole.setExportableColumns($params_str);\n";
		}
		
		if(isset($this->name)){
			echo "var ".$this->name."_jsobject = mconsole;\n";
		}
		else{
			echo "var mconsole_jsobject = mconsole;\n";
		}
		echo "</script>\n";
	}
	
	protected function printConsoleHeader(){
		$columns = $this->columns;
		$nb_cols = count($columns);
		if(isset($this->name)){
			$console_name = $this->name;
		}
		else{
			$console_name = "mconsole";
		}
		echo "<thead>\n";
		echo "<tr class=mconsole_header_row>\n";
		if($this->expandable){
			echo "    <th width=20></th>\n";
		}
		for($i=0; $i<$nb_cols;$i++) {
			$width = $columns[$i]['width'] ? ' width='.$columns[$i]['width'] : '';
			$maxwidth = $columns[$i]['max_width'] ? ' style="max-width:'.$columns[$i]['max_width'].';"' : '';
			$sort = '';
			$sortable = $columns[$i]['sortable'];
			if($sortable){
				$sort = "<span class=mconsole_sort_button><a href=";
				$method = $columns[$i]['sort_method'];
				if($method == 'server'){
					$sort .= '"'.$columns[$i]['sort_url'].'">';
				}
				else{
					$colindex = $this->expandable ? $i+1 : $i;
					$sort .= "\"javascript:".$console_name."_jsobject.sort('$colindex');\">";
				}
				$sort .= "<img src=\"".$this->ASSETS_PATH."/sort.png\" alt=\"Sort\"/></a></span>";
			}
			
			$class = $columns[$i]['responsive'] ? ' class=mconsole_responsive_'.$columns[$i]['responsive_level'] : '';
			
			echo "    <th id='".$columns[$i]['id']."'".$width.$maxwidth.$class.">".$columns[$i]['label']."$sort</th>\n";
		}
		echo "</tr>\n";
		echo "</thead>\n";
	}
	
	private function printConsoleFooter(){
		if(isset($this->footer_datas)){
			echo "<tfoot>\n";
			echo "  <tr class=mconsole_footer_row>";
			if($this->expandable){
				echo "    <td width=20 class=nodata></td>\n";
			}
			$gr_index = 0;
			foreach($this->footer_groups as $group){
				if(! isset($this->footer_datas[$gr_index]) || $this->footer_datas[$gr_index] === ""){
					echo "    <td class=nodata colspan=".$group['nb_cols']."></td>\n";
				}
				else{
					$align = $group['align'] == 'left' ? "" : " align=".$group['align'];
					echo "    <td".$align." colspan=".$group['nb_cols'].">".$this->footer_datas[$gr_index]."</td>\n";
				}
				$gr_index++;
			}
			echo "  </tr>\n";
			echo "</tfoot>\n";
		}
	}
	
	protected function printConsoleRows(){
		if(isset($this->name)){
			$console_name = $this->name;
		}
		else{
			$console_name = "mconsole";
		}
		
		$datas = $this->datas;
		$columns = $this->columns;
		
		// Pour gagner un peu de temps
		$maxwidth = array();
		$align = array();
		$responsive = array();
		for($j=0; $j<count($columns); $j++){
			$maxwidth[$j] = $columns[$j]['max_width'] ? ' style="max-width:'.$columns[$j]['max_width'].';"' : '';
			$align[$j] = $columns[$j]['align'] == 'left' ? "" : " align=".$columns[$j]['align'];
			$responsive[$j] = $columns[$j]['responsive'] ? ' class=mconsole_responsive_'.$columns[$j]['responsive_level'] : '';
		}
		
		$console_style_path = $this->ASSETS_PATH;
		
		echo "<tbody>\n";
		for($i=0; $i<count($datas); $i++){
	
			echo "  <tr id=\"".$console_name."_".$datas[$i]['console_id']."\" class=mconsole_row>\n";
			
			if($this->expandable){
				echo "    <td>";
				echo "<a href=\"javascript:".$console_name."_jsobject.expandCollapse('".$console_name."_".$datas[$i]['console_id']."', ".$datas[$i]['console_id'].");\">";
				echo "<img id=\"".$console_name."_".$datas[$i]['console_id']."_img_more\" src=\"$console_style_path/more.png\" height=\"16\" width=\"16\"/>";
				echo "<img id=\"".$console_name."_".$datas[$i]['console_id']."_img_less\" src=\"$console_style_path/less.png\" height=\"16\" width=\"16\" style=\"display:none;\"/>";
				echo "</a>";
				echo "</td>\n";
			}
			
			for($j=0; $j<count($columns); $j++){
				
				echo "    <td".$align[$j].$maxwidth[$j].$responsive[$j].">";
				if($maxwidth[$j] !== ''){
					echo "<span class=mconsole_possible_overflow>";
				}
				
				switch($columns[$j]['type']) {
					case 'data' :
						if(is_array($datas[$i][$columns[$j]['id']])){
							echo "<span mconsole_sort_value=\"".$datas[$i][$columns[$j]['id']][1]."\">".$datas[$i][$columns[$j]['id']][0]."</span>";
						}
						else{
							echo $datas[$i][$columns[$j]['id']];
						}
						break;

					case 'link' :
						$data_id = $columns[$j]['id'];
						$external = false;
						if(isset($datas[$i][$data_id][2])){
							$external = $datas[$i][$data_id][2];
						}
						if($external){
							echo "<a class=external href=\"#\" onClick=\"javascript:window.open('".$datas[$i][$data_id][0]."');\">";
						}
						else{
							echo "<a href=\"".$datas[$i][$data_id][0]."\">";
						}
						if(isset($datas[$i][$data_id][1])){
							$display = $datas[$i][$data_id][1];
						}
						else{
							$display = $datas[$i][$data_id][0];
						}
						echo $display;
						echo "</a>";
						break;
						
					case 'mailto' :
						$data_id = $columns[$j]['id'];
						$link_name = $datas[$i][$data_id][0];
						if(isset($datas[$i][$data_id][1])){
							$link_name = $datas[$i][$data_id][1];
						}
						if($columns[$j]['encoded']){
							echo self::getEncodedMailto($datas[$i][$data_id][0], $link_name);
						}
						else{
							echo "<a href=\"mailto:".$datas[$i][$data_id][0]."\">$link_name</a>";
						}
						break;
						
					case 'custom' :
						echo $datas[$i][$columns[$j]['id']];
						break;
						
					case 'delete' :
						if($datas[$i][$columns[$j]['id']]){
							echo "<a href=\"javascript:".$console_name."_jsobject.confirmDelete('".$datas[$i]['console_id']."');\">";
							if(isset($this->deleteIcon)){
								echo "<img src=\"$this->deleteIcon\" title=\"".$columns[$j]['label']."\" alt=\"".$columns[$j]['label']."\" height=\"16\" width=\"16\"/>";
							}
							else{
								echo "<img src=\"$console_style_path/delete_icon.png\" title=\"".$columns[$j]['label']."\" alt=\"".$columns[$j]['label']."\" height=\"16\" width=\"16\"/>";
							}
							echo "</a>";
						}
						break;
						
					case 'modify' :
						if($datas[$i][$columns[$j]['id']]){
							echo "<a href=\"".$this->modify_url."id=".$datas[$i]['console_id']."\">";
							if(isset($this->modifyIcon)){
								echo "<img src=\"$this->modifyIcon\" title=\"".$columns[$j]['label']."\" alt=\"".$columns[$j]['label']."\" height=\"16\" width=\"16\"/>";
							}
							else{
								echo "<img src=\"$console_style_path/modify_icon.png\" title=\"".$columns[$j]['label']."\" alt=\"".$columns[$j]['label']."\" height=\"16\" width=\"16\"/>";
							}
							echo "</a>";
						}
						break;
						
					case 'status' :
						echo "<img src=\"$console_style_path/status".$datas[$i][$columns[$j]['id']].".png\" title=\"".$columns[$j]['label']."\" alt=\"status".$datas[$i][$columns[$j]['id']]."\" height=\"16\" width=\"16\"/>";
						break;
						
					case 'details' :
						echo "<a href=\"javascript:".$console_name."_jsobject.details('".$datas[$i]['console_id']."');\">";
						if(isset($this->detailsIcon)){
							echo "<img src=\"$this->detailsIcon\" title=\"".$columns[$j]['label']."\" alt=\"".$columns[$j]['label']."\" height=\"16\" width=\"16\"/>";
						}
						else{
							echo "<img src=\"$console_style_path/details_icon.png\" title=\"".$columns[$j]['label']."\" alt=\"".$columns[$j]['label']."\" height=\"16\" width=\"16\"/>";
						}
						echo "</a>";
						break;
						
					default :
						break;
				}
				if($maxwidth[$j]){
					echo "</span>";
				}
				echo "</td>\n";
			}
			echo "  </tr>\n";
		}
		echo "</tbody>\n";
	}
	
	protected static function getEncodedMailto($mail, $link = null) {
		
		$clear_link = "<a href=\"mailto:$mail\">";
		if(isset($link)){
			$clear_link .= $link;
		}
		else{
			$clear_link .= $mail;
		}
		$clear_link .= "</a>";

		$encoded_link = '';
		//TODO : sortir ce tableau en variable statique privée de la classe
		$hextab = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');

		for($i=0; $i<strlen($clear_link); $i++){

			$char = substr($clear_link,$i,1);
			$ascii = ord($char);

			$div = floor($ascii/16);
			$rest = $ascii%16;

			$hexa = $hextab[$div];
			$hexa .= $hextab[$rest];

			$encoded_link .= $hexa;
		}

		// Ne fonctionne pas dans un appel ajax
		//$encoded_link = "<script language=javascript>document.write(MConsole.mailto_compute('$encoded_link'));</script>";
		$id = '';
		for($i=0;$i<10;$i++){
			$id .= $hextab[rand(0,15)];
		}
		$encoded_link = "<span id='$id'></span>\n"
			. "<script language=javascript>\n"
			. "  document.getElementById('$id').innerHTML = MConsole.mailto_compute('$encoded_link');\n"
			. "</script>\n";

		return $encoded_link;
	}
	
	/**
	 * Displays the console on standard output
	 */
	public function display() {
		if(isset($this->name)){
			$console_name = $this->name;
		}
		else{
			$console_name = "mconsole";
		}
		
		if(self::$must_send_assets_references){
			echo "<link rel=\"StyleSheet\" href=\"".$this->ASSETS_PATH."/MConsole.css\" TYPE=\"text/css\">\n";
			echo "<script type=\"text/javascript\" src=\"".$this->ASSETS_PATH."/MConsole.js\"></script>\n";
			self::$must_send_assets_references = false;
		}
		
		$this->printJavascript();
		
		/*--------------------------------------------------*/
		/* Wrapper                                          */
		/*--------------------------------------------------*/
		if(isset($this->width)){
			if(strpos($this->width, '%')){
				echo "<div style=\"width:".$this->width.";\">";
			}
			else{
			echo "<div style=\"width:".$this->width."px;\">";
		}
		}
		elseif(isset($this->min_width)){
			if(strpos($this->min_width, '%')){
				echo "<div style=\"display: inline-block; min-width:".$this->min_width.";\">";
			}
			else{
				echo "<div style=\"display: inline-block; min-width:".$this->min_width."px;\">";
			}
		}
		else{
			echo "<div style=\"display: inline-block;\">";
		}
		
		/*--------------------------------------------------*/
		/* Title                                            */
		/*--------------------------------------------------*/
		if(isset($this->title) && $this->title_print){
			echo "<div class='mconsole_title'>".$this->title."</div>";
		}
		
		/*--------------------------------------------------*/
		/* Toolbar                                          */
		/*--------------------------------------------------*/
		if(isset($this->toolbar)){
			echo "<div class='mconsole_toolbar'>";
			
			foreach($this->toolbar as $tool_name => $tool_conf){
				$label = $tool_conf['label'];
				switch($tool_name){
					case "native_printable" :
						echo "<a class=".$tool_name."_btn href=\"javascript:$console_name"."_jsobject.displayPrintable();\">$label</a>";
						break;
					case "native_export" :
						$filename = isset($this->exportable_filename) ? $this->exportable_filename : (isset($this->title) ? self::cleanAndGlue($this->title).'.csv' : $this->name.'.csv');
						echo "<a class=".$tool_name."_btn href=\"javascript:$console_name"."_jsobject.exportToCSV('$filename');\">$label</a>";
						break;
					case "add" :
					case "pdf" :
					case "export" :
					case "spreadsheet" :
						$url = $tool_conf['url'];
						echo "<a class=".$tool_name."_btn href=\"$url\">$label</a>";
						break;
					default :
						$url = $tool_conf['url'];
						echo "<a class=toolbar_btn href=\"$url\">$label</a>";
						break;
				}
			}
			echo "</div>";
		}
		
		
		/*--------------------------------------------------*/
		/* Table                                            */
		/*--------------------------------------------------*/
		if(isset($this->width) || isset($this->min_width)){
			echo "<table id=$console_name class=mconsole_table style=\"width:100%;\">\n";
		}
		else{
			echo "<table id=$console_name class=mconsole_table>\n";
		}
		
		$this->printConsoleHeader();
		if(isset($this->datas) && (count($this->datas) > 0)){
			$this->printConsoleFooter();
			$this->printConsoleRows();
		}
		else{
			$cspan = $this->expandable ? count($this->columns) + 1 : count($this->columns);
			echo "<tr><td colspan=".$cspan."><span class=mconsole_warning>Aucune donnée à afficher</span></td>";
		}
		
		echo "</table>\n";
		echo "</div>";
	}
	
	/**
	 * For retro-compatibility
	 */
	public function run() {
		$this->display();
	}
	
	static protected function cleanAndGlue($string, $glue = '-'){
		$string = htmlentities($string, ENT_NOQUOTES, 'utf-8');
		$string = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|dot|grave|ring|slash|tilde|uml);#', '\1', $string);
		$string = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $string );
		$string = preg_replace('/[^a-zA-Z0-9]/', $glue, $string);
		return $string;
	}
}
?>
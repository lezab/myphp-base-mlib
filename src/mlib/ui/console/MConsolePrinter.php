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
 * MConsolePrinter
 * A class to display console tables easily.
 * This is a basic version of MConsole with no sort options, no expandable lines, no modify, delete or details columns, no javascript ...
 * @author Denis ELBAZ
 * @version 1.0.0
 * 
 * @category view
 */
class MConsolePrinter {
	
	/**
	 * @var \mlib\utils\config\MConfig
	 */
	protected $config = null;
	
	protected $name = null;
	
	protected $title = null;
	
	protected $width = null;
	protected $min_width = null;
	
	protected $datas = null;
	protected $columns = array();
	
	protected $STYLE_PATH = null;
	protected $SCRIPT_PATH = null;
	
	protected $custom = null;
	
	protected $footer_groups = null;
	protected $footer_datas = null;
	
	protected static $must_send_js_and_css_references = true;
	
	/**
	 * Constructor
	 * @param \mlib\utils\config\MConfig $config the configuration object
	 */
	public function __construct(\mlib\utils\config\MConfig $config) {
		$this->config = $config;
		
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
		
		
		$columns_config = $config->getConfig("columns");
		$console_cols = $columns_config->getConfigsNames();
		$colindex = 0;
		foreach ($console_cols as $column) {
			$this->columns[$colindex] = array();
			$cconf = $columns_config->getConfig($column);
			$this->columns[$colindex]['id'] = $column;
			$type = $cconf->getParameter('type');
			$this->columns[$colindex]['type'] = $type;
			$this->columns[$colindex]['label'] = $cconf->getParameter('label');
			
			$width = $cconf->getParameter('width');
			$this->columns[$colindex]['width'] = isset($width) ? $width.'px' : "";
			
			$responsive = $cconf->getParameter('responsive');
			$this->columns[$colindex]['responsive'] = isset($responsive);
			if(isset($responsive)){
				$this->columns[$colindex]['responsive_level'] = $responsive;
			}
			
			if(($type == 'data') || ($type == 'link') || ($type == 'mailto')){
				$max_char = $cconf->getParameter('max_characters');
				$this->columns[$colindex]['max_char'] = isset($max_char) ? $max_char : false;
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
			$colindex++;
		}
		
		$footer_config = $config->getConfig("footer");
		if(isset($footer_config)){
			$this->footer_groups = array();
			$groups = explode(',', $footer_config->getParameter('colgroups'));
			$aligns = explode(',', $footer_config->getParameter('align'));
			for($i = 0; $i < count($groups); $i++) {
				$this->footer_groups[$i] = array('nb_cols' => $groups[$i], 'align' => $aligns[$i]);
			}
		}
	}
	
		
	/**
	 * 
	 * @param string $title a title for the console. Used only for a display goal.
	 * @param boolean $printable_only says if the title should be displayed only in printable version window
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * 
	 * @param string $name sets a name the the MConsole object. Usefull only if multiple consoles have
	 *	to be displayed in the same page
	 */
	public function setName($name) {
		$this->name = $name;
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
	
	
	private function printConsoleHeader(){
		$columns = $this->columns;
		$nb_cols = count($columns);
		if(isset($this->name)){
			$console_name = $this->name;
		}
		else{
			$console_name = "mconsole";
		}
		echo "<thead>\n";
		echo "<tr class=mconsole_header_row>";
		for($i=0; $i<$nb_cols;$i++) {
			$width = $columns[$i]['width'] ? 'width='.$columns[$i]['width'] : '';
			$class = $columns[$i]['responsive'] ? 'class=mconsole_responsive_'.$columns[$i]['responsive_level'] : '';
			echo "<th id='".$columns[$i]['id']."' $width $class>".$columns[$i]['label']."</th>";
		}
		echo "</tr>\n";
		echo "</thead>\n";
	}
	
	private function printConsoleFooter(){
		if(isset($this->footer_datas)){
			echo "<tfoot>\n";
			echo "<tr class=mconsole_footer_row>";
			$gr_index = 0;
			foreach($this->footer_groups as $group){
				if(! isset($this->footer_datas[$gr_index]) || $this->footer_datas[$gr_index] == ""){
					echo "<td class=nodata colspan=".$group['nb_cols']."></td>";
				}
				else{
					$align = $group['align'] == 'left' ? "" : " align=".$group['align'];
					echo "<td".$align." colspan=".$group['nb_cols'].">".$this->footer_datas[$gr_index]."</td>";
				}
				$gr_index++;
			}
			echo "</tr>\n";
			echo "</tfoot>\n";
		}
	}
	
	private function printConsoleRows(){
		
		if(isset($this->name)){
			$console_name = $this->name;
		}
		else{
			$console_name = "mconsole";
		}
		
		$datas = $this->datas;
		$columns = $this->columns;
		
		// Pour gagner un peu de temps
		$align = array();
		$responsive = array();
		for($j=0; $j<count($columns); $j++){
			$align[$j] = $columns[$j]['align'] == 'left' ? "" : " align=".$columns[$j]['align'];
			$responsive[$j] = $columns[$j]['responsive'] ? ' class=mconsole_responsive_'.$columns[$j]['responsive_level'] : "";
		}
		
		echo "  <tbody>\n";
		for($i=0; $i<count($datas); $i++){
	
			echo "  <tr id=\"".$console_name."_".$datas[$i]['console_id']."\">\n";
			
			for($j=0; $j<count($columns); $j++){
				
				if($columns[$j]['type'] != 'data'){
					echo "    <td".$align[$j].$responsive[$j].">";
				}
				
				switch($columns[$j]['type']) {
					case 'data' :
						$display = $datas[$i][$columns[$j]['id']];
						if($columns[$j]['max_char']){
							echo "    <td".$align[$j]." title=\"$display\"".$responsive[$j].">";
							$display = strlen($display) <= $columns[$j]['max_char'] ? $display : mb_substr($display, 0, $columns[$j]['max_char']-3, 'UTF-8')."...";
						}
						else{
							echo "    <td".$align[$j].$responsive[$j].">";
						}
						echo $display;
						break;

					case 'link' :
						$data_id = $columns[$j]['id'];
						$external = false;
						if(isset($datas[$i][$data_id][2])){
							$external = $datas[$i][$data_id][2];
						}
						if($external){
							echo "       <a class=external href=\"#\" onClick=\"javascript:window.open('".$datas[$i][$data_id][0]."');\">";
						}
						else{
							echo "       <a href=\"".$datas[$i][$data_id][0]."\">";
						}
						if(isset($datas[$i][$data_id][1])){
							$display = $datas[$i][$data_id][1];
						}
						else{
							$display = $datas[$i][$data_id][0];
						}
						if($columns[$j]['max_char']){
							if($external){
								$display = strlen($display) <= $columns[$j]['max_char'] ? $display : mb_substr($display, 0, $columns[$j]['max_char']-6, 'UTF-8')."...";
							}
							else{
								$display = strlen($display) <= $columns[$j]['max_char'] ? $display : mb_substr($display, 0, $columns[$j]['max_char']-3, 'UTF-8')."...";
							}
						}
						echo $display;
						echo "       </a>";
						break;
						
					case 'mailto' :
						$data_id = $columns[$j]['id'];
						$link_name = $datas[$i][$data_id][0];
						if(isset($datas[$i][$data_id][1])){
							$link_name = $datas[$i][$data_id][1];
						}
						if($columns[$j]['max_char']){
							$link_name = strlen($link_name) <= $columns[$j]['max_char'] ? $link_name : substr($link_name, 0, $columns[$j]['max_char']-3)."...";
						}
						echo "       <a href=\"mailto:".$datas[$i][$data_id][0]."\">$link_name</a>";
						break;
												
					default :
						break;
				}
				echo "    </td>\n";
			}
			echo "  </tr>\n";
		}
		echo "  </tbody>\n";
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
		
		if(self::$must_send_js_and_css_references){
			echo "<style>\n";
			echo file_get_contents(__DIR__."/_resources/console_printer.css");
			echo "</style>\n";
			self::$must_send_js_and_css_references = false;
		}
		
		
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
		if(isset($this->title)){
			echo "<div class='mconsole_title'>".$this->title."</div>";
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
			$cspan = count($this->columns);
			echo "<tr><td colspan=".$cspan."><span class=mconsole_warning>Aucune donnée à afficher</span></td>";
		}
		echo "</table>\n";
		echo "</div>";
	}
	
	
	/**
	 *  for retro compatibility : idem display()
	 */
	public function run(){
		$this->display();
	}
}
?>
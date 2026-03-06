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

namespace mlib\ui\form;
/**
 * MFormPrinter
 * A class to display form datas (not the form) based on a config file more simple to write than html.
 * The main interest is the automatic mapping between the values and what is displayed. 
 * @author Denis ELBAZ
 * @version 1.0.0
 * 
 * @category view
 */
class MFormPrinter {

	/**
	 * @var \mlib\utils\config\MConfig
	 */
	protected $config = null;
	
	protected $name = 'mformprinter';
	protected $custom_options = null;
	
	protected static $must_send_css_references = true;
	
	/**
	 * Constructor
	 * @param \mlib\utils\config\MConfig $config @see MConfig
	 * @param array $custom_options
	 */
	public function __construct(\mlib\utils\config\MConfig $config, array $custom_options = null) {
		$this->config = $config;
		if(isset($custom_options) && is_array($custom_options)){
			$this->custom_options = $custom_options;
		}
	}
	
	/**
	 * Sets the form printer name
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}
	
	/**
	 * Sets options for fields of type radio, checkbox or select with options defined as custom
	 * @param array $options
	 *        Ex : array('myselectfieldname' => array('values' => array(1,2,3), 'displays' => array('Label 1', 'Label 2', 'Other')))
	 */
	public function setCustomOptions(array $options){
		$this->custom_options = $options;
	}
	
	
	/**
	 *  For retro compatibility : idem display() function
	 */
	public function run($vars){
		$this->display($vars);
	}
	
	/**
	 * Displays the datas
	 * @param array $vars an array with values to display
	 */
	public function display($vars) {
		if(self::$must_send_css_references){
			echo "<style>\n";
			echo file_get_contents(__DIR__."/_resources/form_printer.css");
			echo "</style>\n";
			self::$must_send_css_references = false;
		}
		
		$config_form = $this->config;
		
		$this->displayCustomTop($vars);
		
		echo "	<div style='display:flex;'>";
		
		echo "		<div>";
		$this->displayCustomLeft($vars);
		echo "		</div>";
		
		echo "		<div style='flex: 1;'>";
		echo "	<table class=\"mdisplayer\">\n\n";
		
		$display_elements = $config_form->getConfigsNames();
		foreach ($display_elements as $display_element) {
			
			if(substr($display_element, 0, 8) == 'fieldset'){
				$fieldset_config = $config_form->getConfig($display_element);
				$fieldset_name = $fieldset_config->getParameter("display");
				$fields = $fieldset_config->getConfigsNames();

				echo "		<tr><td colspan=2 class=\"mdisplayer_fieldset_header\"><span class=\"mdisplayer_fieldset_label\">$fieldset_name</span></td></tr>";
				foreach ($fields as $field) {
					if($field != "display"){
						$config_field = $fieldset_config->getConfig($field);
						$this->printField($field, $config_field, $vars, $display_element."_".$field, true);
					}
				}
				echo "		<tr><td colspan=2 class=\"mdisplayer_fieldset_footer\"></td></tr>";
			}
			else{
				$config_field = $config_form->getConfig($display_element);
				$this->printField($display_element, $config_field, $vars, "field_".$display_element);
			}
		}
		echo "	</table>\n";
		echo "		</div>";
		
		echo "		<div>";
		$this->displayCustomRight($vars);
		echo "		</div>";
		
		echo "	</div>";
		
		$this->displayCustomBottom($vars);
	}
	
	protected function displayCustomTop($vars = ""){}
	
	protected function displayCustomLeft($vars = ""){}
	
	protected function displayCustomRight($vars = ""){}
	
	protected function displayCustomBottom($vars = ""){}
	
	private function printField($field, $config_field, $vars, $id, $fieldset_element = false){
		
		if(! isset($vars["$field"])){
			return false;
		}
		
		$fieldName = $config_field->getParameter("display");
		$fieldType = $config_field->getParameter("type");
		$fieldMulti = $config_field->getParameter("multi");
		
		if($fieldMulti){
			$values = $vars["$field"];
			if(count($values) > 0){
				while(($values[count($values)-1] == "") && (count($values) > 0)){
					array_pop($values);
				}
			}
			if(! (count($values) > 0)){
				//return false;
				$values[] = "-";
			}
		}
		elseif($fieldType != "custom"){
			$values = $vars["$field"];
			if($values == ""){
				$values = "-";
			}
		}
		else{
			$values = $vars;
		}

		
		$method = "print".ucfirst($fieldType);

		
		$class = $fieldset_element ? " class=\"mdisplayer_fieldset_row\"" : "";
		
		echo "		<tr$class id=$id>\n";
		
		echo "			<td class=\"mdisplayer_left\"><span class=\"mdisplayer_label\">$fieldName</span></td>\n";
		
		echo "			<td class=\"mdisplayer_right\">";
		$this->$method($field, $config_field, $values);
		echo "			</td>";
		
		echo "		</tr>";
	}
	
	private function printValue($field, $config_field, $values){
		$fieldMulti = $config_field->getParameter("multi");
		if($fieldMulti){
			for($i=0; $i<count($values);$i++){
				echo $values[$i];
				echo $i < (count($values) - 1) ? "<br>" : "";
			}
		}
		else{
			echo $values;
		}
	}
	
	private function printEnum($field, $config_field, $values){
		$fieldMulti = $config_field->getParameter("multi");
		$config_field_options = $config_field->getConfig("options");
		$optionsType = $config_field_options->getParameter("type");
		if ($optionsType == "constant") {
			// on split sur les "/" sauf ceux précédé d'un "\" (comme ça on peut mettre des / dans les valeurs)
			$enum_values = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("values"));
			$displays = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("displays"));
			// on remplace les "\/" (échappés) par des "/"
			$enum_values = str_replace("\\/", "/", $enum_values);
			$displays = str_replace("\\/", "/", $displays);
		}
		elseif ($optionsType == "custom") {
			if(isset($this->custom_options[$field])){
				$enum_values = $this->custom_options[$field]['values'];
				$displays = $this->custom_options[$field]['displays'];
			}
			else{
				throw new MFormPrinterException("Options for field $field defined as custom but no corresponding parameters found in custom_options", 200);
			}
			if(! (is_array($enum_values) && is_array($displays))){
				throw new MFormPrinterException("Custom options passed for field $field are not defined correctly", 201);
			}
		}
		if ($fieldMulti) {
			for ($i = 0; $i < count($values); $i++) {
				echo $displays[array_search($values[$i],$enum_values)];
				echo $i < (count($values) - 1) ? "<br>" : "";
			}
		}
		else {
			echo $displays[array_search($values,$enum_values)];
		}
	}
	
	private function printComplex($field, $config_field, $values){
		$fieldMulti = $config_field->getParameter("multi");
		$subfields = $config_field->getConfigsNames();
		if ($fieldMulti) {
			for ($i = 0; $i < count($values); $i++) {
				$first = true;
				echo "				";
				foreach ($subfields as $subfield) {
					if(! $first){
						echo " - ";
					}
					$first = false;
					$config_subfield = $config_field->getConfig($subfield);
					$method = "print".ucfirst($config_subfield->getParameter('type'));
					$v = empty($values) ? "" : $values[$i][$subfield];
					$this->$method($field."[$i][$subfield]", $config_subfield, $v);
				}
				echo $i < (count($values) - 1) ? "<br>" : "";
			}
		}
		else {
			$first = true;
			foreach ($subfields as $subfield) {
				if(! $first){
					echo " - ";
				}
				$first = false;
				$config_subfield = $config_field->getConfig($subfield);
				$method = "print".ucfirst($config_subfield->getParameter('type'));
				$v = is_array($values) ? $values[$subfield] : "";
				$this->$method($field."[$subfield]", $config_subfield, $v);
			}
		}
	}
	
	private function printCustom($field, $config_field, $values){
		$displayMethod = $config_field->getParameter("display_method");
		if(! isset($displayMethod)){
			throw new MFormException("No display_method defined for field $field defined as custom");
		}
		$this->$displayMethod($values);
	}
}
?>
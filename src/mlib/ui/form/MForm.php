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
 * MForm
 * A class to display forms based on a config file more simple to write than html with a lot of functionnalities embedded.
 * @author Denis ELBAZ
 * @version 1.0.4
 * 
 * @category view
 */
class MForm {

	/**
	 * @var \mlib\utils\config\MConfig
	 */
	protected $config = null;
	
	protected $name = 'mform';
	protected $custom_options = null;
	
	protected $ASSETS_PATH = null;

	protected $use_browser_validation = true;

	protected $info = null;
	protected $warning = null;
	protected $error = null;
	protected $error_field = null;
	
	protected $regular = null;
	
	private static $must_send_assets_references = true;
	
	/**
	 * Constructor
	 * @param mlib\utils\MConfig $config @see MConfig
	 * @param array $custom_options
	 */
	public function __construct(\mlib\utils\config\MConfig $config, array $custom_options = null) {
		$this->config = $config;
		if(isset($custom_options) && is_array($custom_options)){
			$this->custom_options = $custom_options;
		}
	}
	
	/**
	 * Sets the form name
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}
	
	/**
	 * Disables browser validation
	 * Use this in case of conditional fields with "required only if" conditions
	 */
	public function disableBrowserValidation() {
		$this->use_browser_validation = false;
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
	 * 
	 * @param string $path the relative path to the directory containing js, css and images for MForm
	 */
	public function registerAssetsPath($path){
		$this->ASSETS_PATH = preg_replace('/\/$/', '', $path);
	}
	
	
	/**
	 * Sets an info message to display after form submission
	 * @param string $message the message
	 */
	public function setInfo($message) {
		$this->info = $message;
	}
	
	/**
	 * Sets a warning message to display after form submission
	 * @param string $message the message
	 */
	public function setWarning($message) {
		$this->warning = $message;
	}
	
	/**
	 * Sets an error message to display after form submission
	 * @param string $message the message
	 */
	public function setError($message) {
		$this->error = $message;
	}
	
	/**
	 * Sets the field on which error occured when form was submitted.
	 * Calling this method before displaying the form allow to highlight the field.
	 * @param string $field the fieldname
	 */
	public function setErrorOn($field) {
		$this->error_field = $field;
	}
	
	
	protected function printInfo() {
		echo "<span class=mform_info>$this->info</span><br>";
	}
	
	protected function printWarning() {
		echo "<span class=mform_warning>$this->warning</span><br>";
	}
	
	protected function printError() {
		echo "<span class=mform_error>$this->error</span><br>";
	}

	
	/**
	 *  For retro compatibility : idem display() function
	 */
	public function run($vars = ""){
		$this->display($vars);
	}
	
	/**
	 * Displays the form
	 * @param array $vars if edit mode, an array with actual values
	 */
	public function display($vars = "") {

		// if passed a partially filled form, proceed in "edit mode"
		$edit = is_array($vars);
		$config_form = $this->config;
		$formname = $this->name;
		$this->regular = $config_form->getParameter("regularized");
		
		if(self::$must_send_assets_references){
			echo "<link rel=\"StyleSheet\" href=\"".$this->ASSETS_PATH."/MForm.css\" type=\"text/css\">\n";
			echo "<script type=\"text/javascript\" src=\"".$this->ASSETS_PATH."/MForm.js\"></script>\n";		
			// Pour afficher qqchose quand on soumet le formulaire
			echo "<div id=\"mform_wait\"></div>\n";
			self::$must_send_assets_references = false;
		}
			
		// Une zone pour les messages avec un petit effet pour voir qu'il y a qqchose qui se passe
		echo "<div id=".$formname."_messages_zone class=mform_messages_zone>\n";
		if(isset($this->info)){$this->printInfo();}
		if(isset($this->warning)){$this->printWarning();}
		if(isset($this->error)){$this->printError();}
		echo "</div>\n";
		echo "<script type=\"text/javascript\">\n";
		echo " var s = document.getElementById('".$formname."_messages_zone').style;";
		echo " s.opacity = 1; (function fade(){if((s.opacity-=.01) > 0.6 ) setTimeout(fade,40);})();";
		echo "</script>\n";
		
		$submit_display = "Valider";
		
		$action = $config_form->getParameter("action");
		echo "<form name=\"$formname\" action=\"$action\" method=\"post\" enctype=\"multipart/form-data\" onSubmit=\"MForm.submit('$formname');\">\n";
	
		$this->displayCustomTop($vars);
		
		echo "	<div style='display:flex;'>";
		
		echo "		<div>";
		$this->displayCustomLeft($vars);
		echo "		</div>";
		
		echo "		<div style='flex: 1;'>";
		echo "	<table class=mform>\n";
		
		$form_elements = $config_form->getSubEntriesNames();
		foreach ($form_elements as $form_element) {
			
			if(substr($form_element,0,8) == 'fieldset'){
				$fieldset_config = $config_form->getConfig($form_element);
				$fieldset_name = $fieldset_config->getParameter("display");
				$fieldset_hiddeable = $fieldset_config->getParameter("hiddeable");
				$fieldset_help = $fieldset_config->getParameter("help");
				$fieldset_help_width = $fieldset_config->getParameter("help_width");
				$fieldset_help_height = $fieldset_config->getParameter("help_height");
				$fields = $fieldset_config->getConfigsNames();

				$help_span = "";
				if($fieldset_help){
					$help_span = "<span style=\"display:block;text-align:right;position:absolute;right:0px;bottom:1px;\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"".$this->ASSETS_PATH."/help.png\" align=absbottom onclick=\"MForm.help('$fieldset_name', '$fieldset_help', '$fieldset_help_width', '$fieldset_help_height');\" /></span>";
				}
				if($fieldset_hiddeable){
					echo "<tr><td colspan=2 class=mform_fieldset_header><span class=mform_fieldset_label>$fieldset_name <img src=\"".$this->ASSETS_PATH."/showhide.png\" title=\"Déplier/Replier\" align=absbottom onclick=\"MForm.showhide('$form_element');\" /> $help_span</span></td></tr>\n";
				}
				else{
					echo "<tr><td colspan=2 class=mform_fieldset_header><span class=mform_fieldset_label>$fieldset_name $help_span</span></td></tr>\n";
				}
				
				foreach ($fields as $field) {
					if(($field != "display")&&($field != "hiddeable")&&($field != "help")&&($field != "help_width")&&($field != "help_height")){
						$config_field = $fieldset_config->getConfig($field);
						$this->printField($field, $config_field, $edit, $vars, $form_element."_".$field, true);
					}
				}
				
				echo "<tr><td colspan=2 class=mform_fieldset_footer></td></tr>\n";
				
				if($fieldset_hiddeable){
					echo "<script type=\"text/javascript\">MForm.showhide('$form_element');</script>\n";
				}
			}
			elseif($form_element == "submit"){
				$config_field = $config_form->getConfig($form_element);
				$submit_display = $config_field->getParameter("display");
				$submit_captcha = $config_field->getParameter("captcha");
			}
			else{
				if($form_element != "action" && $form_element != "regularized"){
					$config_field = $config_form->getConfig($form_element);
					$this->printField($form_element, $config_field, $edit, $vars, "field_".$form_element);
				}
			}
		}
		
		if(isset($submit_captcha)){
			$this->printCaptcha($submit_captcha);
		}
		echo "	</table>\n";
		echo "		</div>";
		
		echo "		<div>";
		$this->displayCustomRight($vars);
		echo "		</div>";
		
		echo "	</div>";
		
		$this->displayCustomBottom($vars);
		
		echo "	<div align=center>";
		// Workaround pour IE qui n'envoie pas l'elément de type submit en dessous dans les valeurs du POST
		// quand on veut valider le formulaire en appuyant sur ENTREE et qu'il n'y a qu'un seul input dans le formulaire
		// On considère qu'il y a en au moins un au dessus, avec cet input "factice", ça fait deux.
		// TODO : voir si c'est toujours necessaire dans la mesure où on a mis un hook pour safari plus bas
		//echo "				<!--[if IE]><input type=\"text\" style=\"display: none;\" disabled=\"disabled\" size=\"1\" /><!-->\n";		
		// Safari n'envoie pas la variable de type submit dans le POST. On est obligé de mette un champs caché de même nom pour le récupérer.
		// On se basera sur ce champs pour laisser la possibilité d'avoir plusieurs formulaires dans la même page.
		echo "				<input type=\"hidden\"  name=\"".$formname."_validated\" value=\"true\" />\n";
		echo "				<input class=mform_submit type=\"submit\"  name=\"".$formname."_validated\" value=\"$submit_display\" />\n";
		echo "	</div>";
		
		echo "</form>\n";
	}
	
	protected function displayCustomTop($vars = ""){}
	
	protected function displayCustomLeft($vars = ""){}
	
	protected function displayCustomRight($vars = ""){}
	
	protected function displayCustomBottom($vars = ""){}
	
	
	
	private function printField($field, $config_field, $edit, $vars, $id, $fieldset_element = false){
		
		$fieldName = $config_field->getParameter("display");
		$fieldType = $config_field->getParameter("type");
		if(!in_array($fieldType, array('hidden', 'input', 'tel', 'number', 'email', 'password', 'text', 'checkbox', 'radio', 'select', 'file', 'date', 'datetime', 'time', 'complex', 'custom'))){
			throw new MFormException("Unknown type $fieldType for field $field", 100);
		}
		$fieldRequired = $config_field->getParameter("required");
		$fieldDisabled = $config_field->getParameter("disabled");
		$fieldHelp = $config_field->getParameter("help");
		$fieldHelpWidth = $config_field->getParameter("help_width");
		$fieldHelpHeight = $config_field->getParameter("help_height");
		
		$disabled = $fieldDisabled && $edit ? "disabled" : "";
		//$error = ($edit && $field === $this->error_field) ? " error" : "";
		$error = "";
		if($edit){
			if($field === $this->error_field){
				$error = " error";
			}
			elseif($fieldType == 'complex'){
				foreach($config_field->getConfigsNames() as $subfield){
					if($subfield === $this->error_field){
						$error = " error";
						break;
					}
				}
			}
		}
		$class = $fieldset_element ? " class=\"mform_fieldset_row$error\"" : " class=\"$error\"";
		if($fieldType != "hidden"){
			echo "		<tr$class id=$id>\n";
			$reqClass = "";
			if ($fieldRequired) {
				$reqClass = " mform_required";
			}
			$regularized = $this->regular ? " mform_regularized" : "";
			echo "			<td class=\"mform_left$regularized\"><span class=\"mform_label$reqClass\">$fieldName</span>";
			if($fieldHelp){
				$esc_fieldName = addslashes($fieldName);
				echo "&nbsp;&nbsp;<img src=\"".$this->ASSETS_PATH."/help.png\" align=absbottom onclick=\"MForm.help('$esc_fieldName', '$fieldHelp', '$fieldHelpWidth', '$fieldHelpHeight');\" />\n";
			}
			echo "</td>\n";
			echo "			<td class=\"mform_right$regularized\" id=".$field."_container>\n";
		}

		
		$method = "print".ucfirst($fieldType);
		
		$fieldDefaultValue = $config_field->getParameter("default");
		if (!isset ($fieldDefaultValue)) {
			$fieldDefaultValue = "";
		}
		$fieldMulti = $config_field->getParameter("multi");
		
		if($fieldMulti || ($fieldType == "checkbox")){
			$values = $edit ? (isset($vars["$field"]) ? $vars["$field"] : array()) : array();
			// TODO : check if necessary
			/*if(count($values) > 0){
				while(($values[count($values)-1] == "") && (count($values) > 0)){
					array_pop($values);
				}
			}*/
			if($fieldType != 'complex' && count($values) == 0){
				$values[] = $fieldDefaultValue;
			}
		}
		elseif($fieldType != "custom"){
			$values = $edit ? (isset($vars["$field"]) ? $vars["$field"] : $fieldDefaultValue) : $fieldDefaultValue;
		}
		else{
			$values = $vars;
		}
		
		$this->$method($field, $config_field, $values, $fieldRequired, $disabled);
		
		if($fieldType != "hidden"){
			echo "			</td>\n";
			echo "		</tr>\n";
		}
	}
	
	private function printHidden($field, $config_field, $value, $required, $disabled){
		echo "		<input type=hidden name=\"$field\" value=\"".$value."\"/>\n";
	}
	
	private function printInput($field, $config_field, $values, $required, $disabled){
		$fieldMulti = $config_field->getParameter("multi");
		$fieldWidth = $config_field->getParameter("width");
		$fieldPlaceholder = $config_field->getParameter("placeholder");
		$placeholder_str = isset($fieldPlaceholder) ? " placeholder=\"$fieldPlaceholder\"" : "";
		$maxlength = $config_field->getParameter("maxlength");
		$maxlength_str = isset($maxlength) ? " maxlength=\"$maxlength\"" : "";
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if (!isset ($fieldWidth)) {
			$fieldWidth = 120;
		}
		if($fieldMulti){
			for($i=0; $i<count($values);$i++){
				echo "				<input type=text$maxlength_str$placeholder_str$required_str id=\"".$field."_firstfield\" name=\"".$field."[]\" style=\"width:".$fieldWidth."px\" value=\"".$values[$i]."\" $disabled/>";
				echo "<img src=\"".$this->ASSETS_PATH."/minus.png\" style=\"vertical-align:middle\" onclick=\"MForm.deleteInput('$field', $i);\"/>";
				if($i < count($values) - 1){
					echo "<br>\n";
				}
			}
			echo "<img src=\"".$this->ASSETS_PATH."/more.png\" style=\"vertical-align:middle\" onclick=\"MForm.addInput('$field');\"/>\n";
		}
		else{
			echo "				<input type=text$maxlength_str$placeholder_str$required_str name=\"$field\" style=\"width:" . $fieldWidth . "px\" value=\"" . $values . "\" $disabled/>\n";
		}
	}
	
	private function printEmail($field, $config_field, $values, $required, $disabled){
		$fieldMulti = $config_field->getParameter("multi");
		$fieldWidth = $config_field->getParameter("width");
		$fieldPlaceholder = $config_field->getParameter("placeholder");
		$placeholder_str = isset($fieldPlaceholder) ? " placeholder=\"$fieldPlaceholder\"" : "";
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if (!isset ($fieldWidth)) {
			$fieldWidth = 120;
		}
		if($fieldMulti){
			for($i=0; $i<count($values);$i++){
				echo "				<input type=email$placeholder_str$required_str id=\"".$field."_firstfield\" name=\"".$field."[]\" style=\"width:" . $fieldWidth . "px\" value=\"" . $values[$i] . "\" $disabled/>";
				if($i < count($values) - 1){
					echo "<br>\n";
				}
			}
			echo "<img src=\"".$this->ASSETS_PATH."/more.png\" style=\"vertical-align:middle\" onclick=\"MForm.addInput('$field');\"/>\n";
		}
		else{
			echo "				<input type=email$placeholder_str$required_str name=\"$field\" style=\"width:" . $fieldWidth . "px\" value=\"" . $values . "\" $disabled/>\n";
		}
	}
	
	private function printNumber($field, $config_field, $values, $required, $disabled){
		$fieldMulti = $config_field->getParameter("multi");
		$fieldWidth = $config_field->getParameter("width");
		$fieldPlaceholder = $config_field->getParameter("placeholder");
		$placeholder_str = isset($fieldPlaceholder) ? " placeholder=\"$fieldPlaceholder\"" : "";
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		$step = $config_field->getParameter("step");
		$step_str = isset($step) ? " step=\"$step\"" : "";
		$min = $config_field->getParameter("min");
		$min_str = isset($min) ? " min=\"$min\"" : "";
		$max = $config_field->getParameter("max");
		$max_str = isset($max) ? " max=\"$max\"" : "";
		if (!isset ($fieldWidth)) {
			$fieldWidth = 120;
		}
		if($fieldMulti){
			for($i=0; $i<count($values);$i++){
				echo "				<input type=number$step_str$min_str$max_str$placeholder_str$required_str id=\"".$field."_firstfield\" name=\"".$field."[]\" style=\"width:" . $fieldWidth . "px\" value=\"" . $values[$i] . "\" $disabled/>";
				if($i < count($values) - 1){
					echo "<br>\n";
				}
			}
			echo "<img src=\"".$this->ASSETS_PATH."/more.png\" style=\"vertical-align:middle\" onclick=\"MForm.addTextField('$field');\"/>\n";
		}
		else{
			echo "				<input type=number$step_str$min_str$max_str$placeholder_str$required_str name=\"$field\" style=\"width:" . $fieldWidth . "px\" value=\"" . $values . "\" $disabled/>\n";
		}
	}
	
	private function printSelect($field, $config_field, $values, $required, $disabled){
		$fieldWidth = $config_field->getParameter("width");
		$fieldMulti = $config_field->getParameter("multi");
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if ($fieldMulti) {
			$fieldHeight = $config_field->getParameter("height");
			if($fieldHeight == null){
				$fieldHeight = 100;
			}
		}
		$config_field_options = $config_field->getConfig("options");
		$optionsType = $config_field_options->getParameter("type");
		if ($optionsType == "constant") {
			// on split sur les "/" sauf ceux précédé d'un "\" (comme ça on peut mettre des / dans les valeurs)
			$select_values = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("values"));
			$displays = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("displays"));
			// on remplace les "\/" (échappés) par des "/"
			$select_values = str_replace("\\/", "/", $select_values);
			$displays = str_replace("\\/", "/", $displays);
		}
		elseif ($optionsType == "custom") {
			if(isset($this->custom_options[$field])){
				$select_values = $this->custom_options[$field]['values'];
				$displays = $this->custom_options[$field]['displays'];
			}
			elseif(preg_match('/\[[0-9]+\]/', $field)){
				$originalField = preg_replace('/\[[0-9]+\]/', '', $field);
				$fieldPathArray = array_filter(explode('[', $originalField));
				foreach($fieldPathArray as &$fieldPathElmt){
					$fieldPathElmt = substr($fieldPathElmt, -1) === ']' ? substr($fieldPathElmt,0, -1) : $fieldPathElmt;
				}
				if(isset($this->custom_options)){
					$temp = &$this->custom_options;
					foreach($fieldPathArray as $key) {
						if(isset($temp[$key])){
							$temp = &$temp[$key];
						}
						else{
							throw new MFormException("Options for field ".join('/',$fieldPathArray)." defined as custom but was not set (see constructor or setCustomOptions method)", 202);
						}
					}
					$select_values = $temp['values'];
					$displays = $temp['displays'];
				}
				else{
					throw new MFormException("Options for field $field defined as custom but was not set (see constructor or setCustomOptions method)", 201);
				}
			}
			elseif(preg_match('/\[(.*)+\]/', $field)){
				$fieldPathArray = array_filter(explode('[', $field));
				foreach($fieldPathArray as &$fieldPathElmt){
					$fieldPathElmt = substr($fieldPathElmt, -1) === ']' ? substr($fieldPathElmt,0, -1) : $fieldPathElmt;
				}
				if(isset($this->custom_options)){
					$temp = &$this->custom_options;
					foreach($fieldPathArray as $key) {
						if(isset($temp[$key])){
							$temp = &$temp[$key];
						}
						else{
							throw new MFormException("Options for field ".join('/',$fieldPathArray)." defined as custom but was not set (see constructor or setCustomOptions method)", 204);
						}
					}
					$select_values = $temp['values'];
					$displays = $temp['displays'];
				}
				else{
					throw new MFormException("Options for field $field defined as custom but was not set (see constructor or setCustomOptions method)", 203);
				}
			}
			else{
				throw new MFormException("Options for field $field defined as custom but was not set (see constructor or setCustomOptions method)", 200);
			}
			if(! (is_array($select_values) && is_array($displays))){
				throw new MFormException("Custom options passed for field $field are not defined correctly", 300);
			}
		}
		if ($fieldMulti) {
			//echo "				<select multiple name=\"".$field."[]\" size=$fieldHeight style=\"width:" . $fieldWidth . "px\" $disabled>\n";
			//for ($i = 0; $i < count($select_values); $i++) {
			//	echo "					<option value=\"" . $select_values[$i] . "\"" . (in_array($select_values[$i], $values) ? " selected" : "") . ">" . $displays[$i] . "</option>\n";
			//}
			//echo "				</select>\n";
			echo "				<div class=\"mform_multiselect_replacement\" style=\"height:".$fieldHeight."px; width:".$fieldWidth."px;\">\n";
			for ($i = 0; $i < count($select_values); $i++) {
				echo "					<label><input type=checkbox name=\"".$field."[]\" value=\"" . $select_values[$i] . "\"" . (in_array($select_values[$i], $values)  ? " checked" : "") . " $disabled> <span>" . $displays[$i] . "</span></label>\n";
			}
			echo "				</div>";
		}
		else {
			echo "				<select name=\"$field\"$required_str style=\"width:" . $fieldWidth . "px\" $disabled>\n";
			echo "					<option value=\"\"></option>\n";
			for ($i = 0; $i < count($select_values); $i++) {
				echo "					<option value=\"" . $select_values[$i] . "\"" . ($values == $select_values[$i] ? " selected" : "") . ">" . $displays[$i] . "</option>\n";
			}
			echo "				</select>\n";
		}
	}
	
	private function printCheckbox($field, $config_field, $values, $required, $disabled){
		$config_field_options = $config_field->getConfig("options");
		$optionsType = $config_field_options->getParameter("type");
		if ($optionsType == "constant") {
			$check_values = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("values"));
			$displays = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("displays"));
			$check_values = str_replace("\\/", "/", $check_values);
			$displays = str_replace("\\/", "/", $displays);
		}
		elseif ($optionsType == "custom") {
			if(isset($this->custom_options[$field])){
				$check_values = $this->custom_options[$field]['values'];
				$displays = $this->custom_options[$field]['displays'];
			}
			elseif(preg_match('/\[[0-9]+\]/', $field)){
				$originalField = preg_replace('/\[[0-9]+\]/', '', $field);
				$fieldPathArray = array_filter(explode('[', $originalField));
				foreach($fieldPathArray as &$fieldPathElmt){
					$fieldPathElmt = substr($fieldPathElmt, -1) === ']' ? substr($fieldPathElmt,0, -1) : $fieldPathElmt;
				}
				if(isset($this->custom_options)){
					$temp = &$this->custom_options;
					foreach($fieldPathArray as $key) {
						if(isset($temp[$key])){
							$temp = &$temp[$key];
						}
						else{
							throw new MFormException("Options for field ".join('/',$fieldPathArray)." defined as custom but was not set (see constructor or setCustomOptions method)", 202);
						}
					}
					$check_values = $temp['values'];
					$displays = $temp['displays'];
				}
				else{
					throw new MFormException("Options for field $field defined as custom but  was not set (see constructor or setCustomOptions method)", 201);
				}
			}
			else{
				throw new MFormException("Options for field $field defined as custom but was not set (see constructor or setCustomOptions method)", 200);
			}
			if(! (is_array($check_values) && is_array($displays))){
				throw new MFormException("Custom options passed for field $field are not defined correctly", 300);
			}
		}
		for ($i = 0; $i < count($check_values); $i++) {
			echo "				<input type=checkbox name=\"".$field."[]\" id=\"$field" . $check_values[$i] . "\" value=\"" . $check_values[$i] . "\"" . (in_array($check_values[$i], $values)  ? " checked" : "") . " $disabled>\n";
			echo "				<label for=\"$field" . $check_values[$i] . "\">".$displays[$i]."</label>\n";
		}
	}
	
	private function printRadio($field, $config_field, $values, $required, $disabled){
		$config_field_options = $config_field->getConfig("options");
		$optionsType = $config_field_options->getParameter("type");
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if ($optionsType == "constant") {
			$box_values = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("values"));
			$displays = preg_split("#(?<!\\\)/#", $config_field_options->getParameter("displays"));
			$box_values = str_replace("\\/", "/", $box_values);
			$displays = str_replace("\\/", "/", $displays);
		}
		elseif ($optionsType == "custom") {
			if(isset($this->custom_options[$field])){
				$box_values = $this->custom_options[$field]['values'];
				$displays = $this->custom_options[$field]['displays'];
			}
			elseif(preg_match('/\[[0-9]+\]/', $field)){
				$originalField = preg_replace('/\[[0-9]+\]/', '', $field);
				$fieldPathArray = array_filter(explode('[', $originalField));
				foreach($fieldPathArray as &$fieldPathElmt){
					$fieldPathElmt = substr($fieldPathElmt, -1) === ']' ? substr($fieldPathElmt,0, -1) : $fieldPathElmt;
				}
				if(isset($this->custom_options)){
					$temp = &$this->custom_options;
					foreach($fieldPathArray as $key) {
						if(isset($temp[$key])){
							$temp = &$temp[$key];
						}
						else{
							throw new MFormException("Options for field ".join('/',$fieldPathArray)." defined as custom but was not set (see constructor or setCustomOptions method)", 202);
						}
					}
					$box_values = $temp['values'];
					$displays = $temp['displays'];
				}
				else{
					throw new MFormException("Options for field ".join('/',$fieldPathArray)." defined as custom but was not set (see constructor or setCustomOptions method)", 201);
				}
			}
			else{
				throw new MFormException("Options for field $field defined as custom but was not set (see constructor or setCustomOptions method)", 200);
			}
			if(! (is_array($box_values) && is_array($displays))){
				throw new MFormException("Custom options passed for field $field are not defined correctly", 300);
			}
		}

		for ($i = 0; $i < count($box_values); $i++) {
			echo "				<input type=radio name=\"$field\"$required_str id=\"$field" . $box_values[$i] . "\" value=\"" . $box_values[$i] . "\"" . ($values == $box_values[$i] ? " checked" : "") . " $disabled>\n";
			echo "				<label for=\"$field" . $box_values[$i] . "\">".$displays[$i]."</label>\n";
		}
	}
	
	
	private function printFile($field, $config_field, $values, $required, $disabled){
		$fieldWidth = $config_field->getParameter("width");
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		
		$fieldMulti = $config_field->getParameter("multi");
		$multiple = $fieldMulti ? " multiple" : "";
		
		// Un div en position "relative" qui contiendra tout le reste (qui pourra etre positionné en absolu dans le div
		echo "				<div class=mform_custom_inputfile style=\"width:" . $fieldWidth . "px;\">";
		// Un faux input, mais c'est celui qu'on voit
		$placeholder = $values != "" ? "$values (cliquez pour modifier)" : "";
		echo "					<input type=text id=\"mform_custom_inputfile_fakefield_$field\" class=\"mform_custom_inputfile_fakefield\" placeholder=\"$placeholder\" readonly=\"readonly\" $disabled />";
		// Une image qui fait office de bouton parcourir
		echo "					<img class=\"mform_custom_inputfile_button\" src=\"".$this->ASSETS_PATH."/computer.png\" title=\"Parcourir ...\" />";
		// Le vrai input file qu'on ne voit pas et qui masque le tout pour que ce soit cliquable et ouvre la boite de sélection
		echo "					<input type=file id=\"$field\"$required_str name=\"$field\" onmousedown=\"return false;\" onkeydown=\"return false;\" class=\"mform_custom_inputfile_file\" onchange=\"document.getElementById('mform_custom_inputfile_fakefield_$field').value = this.value.replace('C:\\\\fakepath\\\\','');\" $disabled $multiple />";		
		echo "				</div>";
	}
	
	
	private function printDate($field, $config_field, $values, $required, $disabled){
		
		$format = $config_field->getParameter("format");
		if(! isset($format)){
			$format = 'Y-m-d';
		}
		
		if($disabled == ""){
			$date_begin = $config_field->getParameter("min");
			$date_end = $config_field->getParameter("max");
			
			if(!isset($date_begin)){
				$date_begin = 'D';
			}
			if(!isset($date_end)){
				$date_end = 'Y+1';
			}
			
			if(substr_compare($date_begin, 'Y', 0, 1) === 0){
				if($p = strpos($date_begin, '+')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, 1, 1, date("Y")+$nb));
				}
				elseif($p = strpos($date_begin, '-')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, 1, 1, date("Y")-$nb));
				}
				else{
					$date_begin = date($format, mktime(0, 0, 0, 1, 1, date("Y")));
				}
			}
			elseif(substr_compare($date_begin, 'M', 0, 1) === 0){
				if($p = strpos($date_begin, '+')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n")+$nb, 1, date("Y")));
				}
				elseif($p = strpos($date_begin, '-')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n")-$nb, 1, date("Y")));
				}
				else{
					$date_begin = date($format, mktime(0, 0, 0, date("n"), 1, date("Y")));
				}
			}
			elseif(substr_compare($date_begin, 'D', 0, 1) === 0){
				if($p = strpos($date_begin, '+')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n"), date("j")+$nb, date("Y")));
				}
				elseif($p = strpos($date_begin, '-')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n"), date("j")-$nb, date("Y")));
				}
				else{
					$date_begin = date($format);
				}
			}


			if(substr_compare($date_end, 'Y', 0, 1) === 0){
				if($p = strpos($date_end, '+')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(0, 0, 0, 12, 31, date("Y")+$nb));
				}
				elseif($p = strpos($date_end, '-')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(0, 0, 0, 12, 31, date("Y")-$nb));
				}
				else{
					$date_end = date($format, mktime(0, 0, 0, 12, 31, date("Y")));
				}
			}
			elseif(substr_compare($date_end, 'M', 0, 1) === 0){
				if($p = strpos($date_end, '+')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(0, 0, 0, date("n")+$nb+1, 0, date("Y")));
				}
				elseif($p = strpos($date_end, '-')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(0, 0, 0, date("n")-$nb+1, 0, date("Y")));
				}
				else{
					$date_end = date($format, mktime(0, 0, 0, date("n")+1, 0, date("Y")));
				}
			}
			elseif(substr_compare($date_end, 'D', 0, 1) === 0){
				if($p = strpos($date_end, '+')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(0, 0, 0, date("n"), date("j")+$nb, date("Y")));
				}
				elseif($p = strpos($date_end, '-')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(0, 0, 0, date("n"), date("j")-$nb, date("Y")));
				}
				else{
					$date_end = date($format);
				}
			}
		}
		
		switch($format){
			case 'Y-m-d' :
				$placeholder = 'aaaa-mm-jj';
				break;
			case 'd-m-Y' :
				$placeholder = 'jj-mm-aaaa';
				break;
			case 'Y/m/d' :
				$placeholder = 'aaaa/mm/jj';
				break;
			case 'd/m/Y' :
				$placeholder = 'jj/mm/aaaa';
				break;
			default :
				$placeholder = $format;
				break;
		}
		
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if($disabled != ""){
			echo "				<input type=\"text\" id=\"$field\"$required_str name=\"$field\" style=\"width:120px\" placeholder=\"$placeholder\" maxlength=10 value=\"$values\"$disabled/>";
		}
		else{
			echo "				<input type=\"text\" id=\"$field\"$required_str name=\"$field\" style=\"width:120px\" placeholder=\"$placeholder\" maxlength=10 value=\"$values\" onclick=\"MDatePicker.showHide('$field', '$date_begin', '$date_end', '$format', false);\"/>";
		}
	}
	
	private function printDatetime($field, $config_field, $values, $required, $disabled){
		
		$format = $config_field->getParameter("format");
		if(! isset($format)){
			$format = 'Y-m-d H:i';
		}
		
		if($disabled == ""){
			$date_begin = $config_field->getParameter("min");
			$date_end = $config_field->getParameter("max");

			if(!isset($date_begin)){
				$date_begin = 'D';
			}
			if(!isset($date_end)){
				$date_end = 'Y+1';
			}
			
			if(substr_compare($date_begin, 'Y', 0, 1) === 0){
				if($p = strpos($date_begin, '+')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, 1, 1, date("Y")+$nb));
				}
				elseif($p = strpos($date_begin, '-')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, 1, 1, date("Y")-$nb));
				}
				else{
					$date_begin = date($format, mktime(0, 0, 0, 1, 1, date("Y")));
				}
			}
			elseif(substr_compare($date_begin, 'M', 0, 1) === 0){
				if($p = strpos($date_begin, '+')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n")+$nb, 1, date("Y")));
				}
				elseif($p = strpos($date_begin, '-')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n")-$nb, 1, date("Y")));
				}
				else{
					$date_begin = date($format, mktime(0, 0, 0, date("n"), 1, date("Y")));
				}
			}
			elseif(substr_compare($date_begin, 'D', 0, 1) === 0){
				if($p = strpos($date_begin, '+')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n"), date("j")+$nb, date("Y")));
				}
				elseif($p = strpos($date_begin, '-')){
					$nb = trim(substr($date_begin, $p+1));
					$date_begin = date($format, mktime(0, 0, 0, date("n"), date("j")-$nb, date("Y")));
				}
				else{
					$date_begin = date($format, mktime(0, 0, 0, date("n"), date("j"), date("Y")));
				}
			}


			if(substr_compare($date_end, 'Y', 0, 1) === 0){
				if($p = strpos($date_end, '+')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(23, 59, 59, 12, 31, date("Y")+$nb));
				}
				elseif($p = strpos($date_end, '-')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(23, 59, 59, 12, 31, date("Y")-$nb));
				}
				else{
					$date_end = date($format, mktime(23, 59, 59, 12, 31, date("Y")));
				}
			}
			elseif(substr_compare($date_end, 'M', 0, 1) === 0){
				if($p = strpos($date_end, '+')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(23, 59, 59, date("n")+$nb+1, 0, date("Y")));
				}
				elseif($p = strpos($date_end, '-')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(23, 59, 59, date("n")-$nb+1, 0, date("Y")));
				}
				else{
					$date_end = date($format, mktime(23, 59, 59, date("n")+1, 0, date("Y")));
				}
			}
			elseif(substr_compare($date_end, 'D', 0, 1) === 0){
				if($p = strpos($date_end, '+')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(23, 59, 59, date("n"), date("j")+$nb, date("Y")));
				}
				elseif($p = strpos($date_end, '-')){
					$nb = trim(substr($date_end, $p+1));
					$date_end = date($format, mktime(23, 59, 59, date("n"), date("j")-$nb, date("Y")));
				}
				else{
					$date_end = date($format, mktime(23, 59, 59, date("n"), date("j"), date("Y")));
				}
			}
		}
		
		switch($format){
			case 'Y-m-d H:i' :
				$placeholder = 'aaaa-mm-jj hh:mm';
				break;
			case 'd-m-Y H:i' :
				$placeholder = 'jj-mm-aaaa hh:mm';
				break;
			case 'Y/m/d H:i' :
				$placeholder = 'aaaa/mm/jj hh:mm';
				break;
			case 'd/m/Y H:i' :
				$placeholder = 'jj/mm/aaaa hh:mm';
				break;
			default :
				$placeholder = $format;
				break;
		}
		
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if($disabled != ""){
			echo "				<input type=\"text\" id=\"$field\"$required_str name=\"$field\" style=\"width:140px\" placeholder=\"$placeholder\" maxlength=16 value=\"$values\"$disabled/>";
		}
		else{
			echo "				<input type=\"text\" id=\"$field\"$required_str name=\"$field\" style=\"width:140px\" placeholder=\"$placeholder\" maxlength=16 value=\"$values\" onclick=\"MDatePicker.showHide('$field', '$date_begin', '$date_end', '$format', true);\"/>";
		}
	}
	
	private function printTime($field, $config_field, $values, $required, $disabled){
		$placeholder = 'hh:mm';
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if($disabled != ""){
			echo "				<input type=\"text\" id=\"$field\"$required_str name=\"$field\" style=\"width:80px;\" placeholder=\"$placeholder\" maxlength=5 value=\"$values\"$disabled/>";
		}
		else{
			echo "				<input type=\"text\" id=\"$field\"$required_str name=\"$field\" style=\"width:80px;\" placeholder=\"$placeholder\" maxlength=5 value=\"$values\" onclick=\"MTimePicker.showHide('$field');\"/>";
		}
	}
	
	private function printText($field, $config_field, $values, $required, $disabled){
		$fieldWidth = $config_field->getParameter("width");
		if(! isset($fieldWidth)){
			$fieldWidth = 300;
		}
		$fieldHeight = $config_field->getParameter("height");
		$height_str = isset($fieldHeight) ? "height:".$fieldHeight."px;" : "";
		$rows = $config_field->getParameter("rows");
		if(! isset($rows)){
			$rows = 4;
		}
		$fieldPlaceholder = $config_field->getParameter("placeholder");
		$placeholder_str = isset($fieldPlaceholder) ? " placeholder=\"$fieldPlaceholder\"" : "";
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		echo "				<textarea$placeholder_str$required_str name=\"$field\" rows=\"$rows\" style=\"width:".$fieldWidth."px;".$height_str."\" $disabled>$values</textarea>\n";
	}
	
	private function printPassword($field, $config_field, $values, $required, $disabled){
		$fieldWidth = $config_field->getParameter("width");
		$fieldPlaceholder = $config_field->getParameter("placeholder");
		$placeholder_str = isset($fieldPlaceholder) ? " placeholder=\"$fieldPlaceholder\"" : "";
		$required_str = $required ? ($this->use_browser_validation ? " required" : "") : "";
		if (!isset ($fieldWidth)) {
			$fieldWidth = 120;
		}
		echo "				<input type=password$placeholder_str$required_str name=\"$field\" style=\"width:" . $fieldWidth . "px\" value=\"" . $values . "\" $disabled/>\n";
	}
	
	private function printComplex($field, $config_field, $values, $required, $disabled){
		$fieldMulti = $config_field->getParameter("multi");
		
		$subfields = $config_field->getConfigsNames();
		
		echo "<table class=mform_complex_table><tr id=\"".$field."_header\" class=mform_complex_table_header>";
		foreach ($subfields as $subfield) {
			echo "<td>";
			$config_subfield = $config_field->getConfig($subfield);
			echo $config_subfield->getParameter('display');
			if($config_subfield->getParameter('required')){
				echo "*";
			}
			echo "</td>";
		}
		if($fieldMulti){
			echo "<td></td>";
		}
		echo "</tr>";
		
		if($fieldMulti){
			$nb = empty($values) ? 1  : count($values);
			for($i=0; $i<$nb;$i++){
				echo "<tr id=\"".$field."_$i\">";
				foreach ($subfields as $subfield) {
					echo "<td>";
					$config_subfield = $config_field->getConfig($subfield);
					$method = "print".ucfirst($config_subfield->getParameter('type'));
					$required = $config_subfield->getParameter("required");
					$fieldDefaultValue = $config_subfield->getParameter("default");
					if (!isset ($fieldDefaultValue)) {
						$fieldDefaultValue = "";
					}
					$v = empty($values) ? $fieldDefaultValue : $values[$i][$subfield];
					$this->$method($field."[$i][$subfield]", $config_subfield, $v, $required, $disabled);
					echo "</td>";
				}
				echo "<td>";
				echo "<img src=\"".$this->ASSETS_PATH."/minus.png\" style=\"vertical-align:middle\" onclick=\"MForm.deleteComplex('$field', $i);\"/>";
				if($i < count($values) - 1){
					echo "</td>";
				}
			}
			echo "<img src=\"".$this->ASSETS_PATH."/more.png\" style=\"vertical-align:middle\" onclick=\"MForm.addComplex('$field');\"/>\n";
			echo "</td>";
		}
		else{
			echo "<tr>";
			foreach ($subfields as $subfield) {	
				echo "<td>";
				$config_subfield = $config_field->getConfig($subfield);
				$method = "print".ucfirst($config_subfield->getParameter('type'));
				$required = $config_subfield->getParameter("required");
				$fieldDefaultValue = $config_subfield->getParameter("default");
				if (!isset ($fieldDefaultValue)) {
					$fieldDefaultValue = "";
				}
				$v = is_array($values) ? $values[$subfield] : $fieldDefaultValue;
				$this->$method($field."[$subfield]", $config_subfield, $v, $required, $disabled);
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	
	private function printCustom($field, $config_field, $values, $required, $disabled){
		$displayMethod = $config_field->getParameter("display_method");
		if(! isset($displayMethod)){
			throw new MFormException("No display_method defined for field $field defined as custom");
		}
		$this->$displayMethod($values);
	}
	
	
	private function printCaptcha($captcha_url){
		echo "<tr><td colspan=2 align=center><div class=mform_captcha_box><img id=".$this->name."_captchaimg style=\"margin-bottom:10px;\" src=\"$captcha_url\" /><br><input type=text name=".$this->name."_captcha style=\"width:120px; height:25px;\" /><img src=\"".$this->ASSETS_PATH."/renew.png\" style=\"float:right;margin:5px;\" onclick=\"MForm.renewCaptcha('".$this->name."_captchaimg', '$captcha_url');\" /><br>Veuillez saisir les caractères ci-dessus</div></td></tr>\n";
	}
}
?>
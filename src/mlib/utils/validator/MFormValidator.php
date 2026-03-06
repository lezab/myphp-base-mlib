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

namespace mlib\utils\validator;

/**
 * MFormValidator
 * @author Denis ELBAZ
 * @version 1.0.3
 * 
 * @category controller
 */
class MFormValidator {

	/**
	 * @var \mlib\utils\config\MConfig
	 */
	protected $config = null;
	protected $custom_options = null;
	
	protected $error = null;
	protected $errorField = null;
	protected $errorMessage = null;
	
	const ERROR_SYSTEM = 0;
	const ERROR_REQUIRED = 1;
	const ERROR_CHECK = 2;
	const ERROR_GLOBAL = 3;
	
	/**
	 * Constructor
	 * @param type $config_filename
	 * @param type $params
	 * @throws MFormValidatorException
	 */
	public function __construct(\mlib\utils\config\MConfig $config, array $custom_options = null) {
		$this->config = $config;
		if(isset($custom_options) && is_array($custom_options)){
			$this->custom_options = $custom_options;
		}
	}
	
	/**
	 * Sets options for fields of type enum with values defined as custom
	 * @param array $options
	 *        Ex : array('myenumfieldname' => array(1,2,3))
	 */
	public function setCustomOptions(array $options){
		$this->custom_options = $options;
	}
	
	
	/**
	 * Runs the validator
	 * @return boolean true if submitted datas are validated, false otherwise
	 */
	public function run(){	
		// When the post size exceeds post_max_size, the super global arrays of $_POST and $_FILES will become empty.
		// So, by testing for these and by confirming that there is some content being sent using the POST method,
		// it can be deduced that such an error has occurred
		if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0){
			$displayMaxSize = ini_get('post_max_size');
			$this->error = self::ERROR_SYSTEM;
			$this->errorMessage = "Too much datas received (Max : $displayMaxSize). Check your configuration file (post_max_size)";
			return false;
		}
		
		$this->clean($_POST);
		if(!$this->check(array_merge($_POST, $_FILES))){
			return false;
		}
		return true;
	}

	
	private function clean(&$vars){
		$keys = array_keys($vars);
		foreach($keys as $key){
			if(is_array($vars[$key])){
				$vars[$key] = $this->clean($vars[$key]);
				if(empty($vars[$key])){
					unset($vars[$key]);
				}
			}
			else{
				if($vars[$key] == ""){
					unset($vars[$key]);
				}
			}
		}
		return $vars;
	}
	
	private function check($vars){
	
		$config_form = $this->config;
		$form_elements = $config_form->getConfigsNames();
		foreach ($form_elements as $form_element) {
			$config_field = $config_form->getConfig($form_element);
			if(! $this->checkField($form_element, $config_field, $vars)){
				return false;
			}
		}
		if(! $this->globalCheck($vars)){
			$this->error = self::ERROR_GLOBAL;
			return false;
		}
		return true;
	}
	
	
	private function checkField($field, $config_field, $vars){
	
		$fieldRequired = $config_field->getParameter("required");
		$fieldType = $config_field->getParameter("type");
		$fieldMulti = $config_field->getParameter("multi");
		$fieldErrorMsg = $config_field->getParameter("error");
		$fieldErrorRqMsg = $config_field->getParameter("error_required");
	
		if($fieldRequired){
			if($fieldType == "file"){
				if(! (isset($vars[$field]) && isset($vars[$field]['name']) && ($vars[$field]['name'] != ''))){
					$this->error = self::ERROR_REQUIRED;
					$this->errorField = $field;
					if(isset($fieldErrorRqMsg)){
						$this->errorMessage = $fieldErrorRqMsg;
					}
					elseif(isset($fieldErrorMsg)){
						$this->errorMessage = $fieldErrorMsg;
					}
					return false;
				}
			}
			else{
				if(! isset($vars[$field])){
					$this->error = self::ERROR_REQUIRED;
					$this->errorField = $field;
					if(isset($fieldErrorRqMsg)){
						$this->errorMessage = $fieldErrorRqMsg;
					}
					elseif(isset($fieldErrorMsg)){
						$this->errorMessage = $fieldErrorMsg;
					}
					return false;
				}
			}
		}

		if(isset($vars[$field])){
			if($fieldType == "custom"){
				$checkMethod = $config_field->getParameter("check_method");
				if(! $checkMethod){
					throw new MFormValidatorException("No check_method defined for field $field defined as custom");
				}
				if(! $this->$checkMethod($vars[$field])){
					$this->error = self::ERROR_CHECK;
					$this->errorField = $field;
					if((!isset($this->errorMessage)) && isset($fieldErrorMsg)){
						$this->errorMessage = $fieldErrorMsg;
					}
					return false;
				}
			}
			else{
				$checkMethod = "check".ucfirst($fieldType);
				$additionalCheckMethod = $config_field->getParameter("check_method");

				if($fieldType == "file"){
					if (isset($vars[$field]['name'])) {
						// Si c'est un champ multiple (name est un tableau)
						if (is_array($vars[$field]['name'])) {
							$fileCount = count($vars[$field]['name']);
							if($fileCount > 1 && (! $fieldMulti)){
								$this->error = self::ERROR_CHECK;
								$this->errorField = $field;
								$this->errorMessage = "Too much files uploaded";
								return false;
							}
							for ($i = 0; $i < $fileCount; $i++) {
								// On reconstruit un tableau de fichier standard pour chaque fichier
								$file = [
									'name' => $vars[$field]['name'][$i],
									'type' => $vars[$field]['type'][$i],
									'tmp_name' => $vars[$field]['tmp_name'][$i],
									'error' => $vars[$field]['error'][$i],
									'size' => $vars[$field]['size'][$i]
								];
								
								if ($file['name'] != '') {  // Ne traiter que les fichiers effectivement uploadés
									if (!$this->checkFile($file, $config_field)) {
										$this->errorField = $field;
										if ((!isset($this->errorMessage)) && isset($fieldErrorMsg)) {
											$this->errorMessage = $fieldErrorMsg;
										}
										return false;
									}
									
									if ($additionalCheckMethod) {
										if (!$this->$additionalCheckMethod($file)) {
											$this->error = self::ERROR_CHECK;
											$this->errorField = $field;
											if ((!isset($this->errorMessage)) && isset($fieldErrorMsg)) {
												$this->errorMessage = $fieldErrorMsg;
											}
											return false;
										}
									}
								}
							}
						} 
						// Si c'est un fichier unique
						else if ($vars[$field]['name'] != '') {
							if (!$this->checkFile($vars[$field], $config_field)) {
								$this->errorField = $field;
								if ((!isset($this->errorMessage)) && isset($fieldErrorMsg)) {
									$this->errorMessage = $fieldErrorMsg;
								}
								return false;
							}
							
							if ($additionalCheckMethod) {
								if (!$this->$additionalCheckMethod($vars[$field])) {
									$this->error = self::ERROR_CHECK;
									$this->errorField = $field;
									if ((!isset($this->errorMessage)) && isset($fieldErrorMsg)) {
										$this->errorMessage = $fieldErrorMsg;
									}
									return false;
								}
							}
						}
					}
				}
				else{
					if($fieldMulti){
						foreach($vars[$field] as $value){
							if(! $this->$checkMethod($value, $config_field)){
								$this->error = self::ERROR_CHECK;
								$this->errorField = $field;
								if((!isset($this->errorMessage)) && isset($fieldErrorMsg)){
									$this->errorMessage = $fieldErrorMsg;
								}
								return false;
							}
							if($additionalCheckMethod){
								if(! $this->$additionalCheckMethod($value)){
									$this->error = self::ERROR_CHECK;
									$this->errorField = $field;
									if((!isset($this->errorMessage)) && isset($fieldErrorMsg)){
										$this->errorMessage = $fieldErrorMsg;
									}
									return false;
								}
							}
						}
					}
					else{						
						if(! $this->$checkMethod($vars[$field], $config_field)){
							$this->error = self::ERROR_CHECK;
							$this->errorField = $field;
							if((!isset($this->errorMessage)) && isset($fieldErrorMsg)){
								$this->errorMessage = $fieldErrorMsg;
							}
							return false;
						}
						if($additionalCheckMethod){
							if(! $this->$additionalCheckMethod($vars[$field])){
								$this->error = self::ERROR_CHECK;
								$this->errorField = $field;
								if((!isset($this->errorMessage)) && isset($fieldErrorMsg)){
									$this->errorMessage = $fieldErrorMsg;
								}
								return false;
							}
						}
					}
				}
			}
		}
		return true;
	}
	
	
	protected function checkString($value, $config){
		$min = $config->getParameter('minlength');
		if(isset($min) && (! (strlen($value) >=  $min))){
			$errorMsg = $config->getParameter("error_minlength");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$max = $config->getParameter('maxlength');
		if(isset($max) && (! (strlen($value) <=  $max))){
			$errorMsg = $config->getParameter("error_maxlength");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$regex = $config->getParameter('regex');
		//error_log($regex);
		if(isset($regex) && (! preg_match("$regex", $value))){
			$errorMsg = $config->getParameter("error_regex");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		return true;
	}
	
	protected function checkFloat($value, $config){
		if(filter_var($value, FILTER_VALIDATE_FLOAT) === false){
			$errorMsg = $config->getParameter("error_type");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$min = $config->getParameter('min');
		if(isset($min) && (! ($value >=  $min))){
			$errorMsg = $config->getParameter("error_min");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$max = $config->getParameter('max');
		if(isset($max) && (! ($value <=  $max))){
			$errorMsg = $config->getParameter("error_max");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		return true;
	}
	
	protected function checkInteger($value, $config){
		if(filter_var($value, FILTER_VALIDATE_INT) === false){
			$errorMsg = $config->getParameter("error_type");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$min = $config->getParameter('min');
		if(isset($min) && (! ($value >=  $min))){
			$errorMsg = $config->getParameter("error_min");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$max = $config->getParameter('max');
		if(isset($max) && (! ($value <=  $max))){
			$errorMsg = $config->getParameter("error_max");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		return true;
	}
	
	protected function checkEmail($value, $config){
		if(! filter_var($value, FILTER_VALIDATE_EMAIL)){
			$errorMsg = $config->getParameter("error_type");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		$regex = $config->getParameter('regex');
		//error_log($regex);
		if(isset($regex) && (! preg_match("$regex", $value))){
			$errorMsg = $config->getParameter("error_regex");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		return true;
	}
	
	protected function checkPhone($value, $config){
		// On est assez permissif parce que c'est compliqué de vérifier tous les formats de numéros de tel,
		// mais on verifie quand même :
		// - le début (commence par 0 ou 00 ou +[1-9])
		// - ne contient que de chiffre et des . ou - ou espaces
		if(! (preg_match('#^0[1-9]([-. ]?\d+)+$#', $value) ||
				preg_match('#^00[1-9]([-. ]?\d+)+$#', $value) ||
				preg_match('#^\+[1-9]([-. ]?\d+)+$#', $value))){
			$errorMsg = $config->getParameter("error_type");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		return true;
	}
	
	protected function checkDatetime($value, $config){
		$format = $config->getParameter('format');
		if(! $date = \DateTime::createFromFormat($format, $value)){
			$errorMsg = $config->getParameter("error_format");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		if($date->format($format) != $value){
			$errorMsg = $config->getParameter("error_type");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		
		$min = $config->getParameter('min');
		if(isset($min)){
			if(substr_compare($min, 'Y', 0, 1) === 0){
				if($p = strpos($min, '+')){
					$nb = trim(substr($min, $p+1));
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, 1, 1, date("Y")+$nb));
				}
				elseif($p = strpos($min, '-')){
					$nb = trim(substr($min, $p+1));
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, 1, 1, date("Y")-$nb));
				}
				else{
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, 1, 1, date("Y")));
				}
			}
			elseif(substr_compare($min, 'M', 0, 1) === 0){
				if($p = strpos($min, '+')){
					$nb = trim(substr($min, $p+1));
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date("n")+$nb, 1, date("Y")));
				}
				elseif($p = strpos($min, '-')){
					$nb = trim(substr($min, $p+1));
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date("n")-$nb, 1, date("Y")));
				}
				else{
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date("n"), 1, date("Y")));
				}
			}
			elseif(substr_compare($min, 'D', 0, 1) === 0){
				if($p = strpos($min, '+')){
					$nb = trim(substr($min, $p+1));
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date("n"), date("j")+$nb, date("Y")));
				}
				elseif($p = strpos($min, '-')){
					$nb = trim(substr($min, $p+1));
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date("n"), date("j")-$nb, date("Y")));
				}
				else{
					$date_min = (new \DateTime())->setTimestamp(mktime(0, 0, 0, date("n"), date("j"), date("Y")));
				}
			}
			else{
				$date_min = \DateTime::createFromFormat($format, $min);
			}
			if(! ($date_min <= $date)){
				$errorMsg = $config->getParameter("error_min");
				if(isset($errorMsg)){
					$this->errorMessage = $errorMsg;
				}
				return false;
			}
		}

		$max = $config->getParameter('max');
		if(isset($max)){
			if(substr_compare($max, 'Y', 0, 1) === 0){
				if($p = strpos($max, '+')){
					$nb = trim(substr($max, $p+1));
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, 12, 31, date("Y")+$nb));
				}
				elseif($p = strpos($max, '-')){
					$nb = trim(substr($max, $p+1));
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, 12, 31, date("Y")-$nb));
				}
				else{
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, 12, 31, date("Y")));
				}
			}
			elseif(substr_compare($max, 'M', 0, 1) === 0){
				if($p = strpos($max, '+')){
					$nb = trim(substr($max, $p+1));
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, date("n")+$nb+1, 0, date("Y")));
				}
				elseif($p = strpos($max, '-')){
					$nb = trim(substr($max, $p+1));
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, date("n")-$nb+1, 0, date("Y")));
				}
				else{
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, date("n")+1, 0, date("Y")));
				}
			}
			elseif(substr_compare($max, 'D', 0, 1) === 0){
				if($p = strpos($max, '+')){
					$nb = trim(substr($max, $p+1));
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, date("n"), date("j")+$nb, date("Y")));
				}
				elseif($p = strpos($max, '-')){
					$nb = trim(substr($max, $p+1));
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, date("n"), date("j")-$nb, date("Y")));
				}
				else{
					$date_max = (new \DateTime())->setTimestamp(mktime(23, 59, 59, date("n"), date("j"), date("Y")));
				}
			}
			else{
				$date_max = \DateTime::createFromFormat($format, $max);
			}
			if(! ($date <= $date_max)){
				$errorMsg = $config->getParameter("error_max");
				if(isset($errorMsg)){
					$this->errorMessage = $errorMsg;
				}
				return false;
			}
		}
		return true;
	}
	
	protected function checkDate($value, $config){
		return $this->checkDatetime($value, $config);
	}
	
	protected function checkTime($value, $config){
		
		$format = $config->getParameter('format');
		if(!isset($format)){
			//HH:MM 24-hour with leading 0
			$format = "H:i";
		}
	
		if(! $date = \DateTime::createFromFormat($format, $value)){
			$errorMsg = $config->getParameter("error_format");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		if($date->format($format) != $value){
			$errorMsg = $config->getParameter("error_type");
			if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
			}
			return false;
		}
		
		$min = $config->getParameter('min');
		if(isset($min)){
			$date_min = \DateTime::createFromFormat($format, $min);
			if(! ($date_min <= $date)){
				$errorMsg = $config->getParameter("error_min");
				if(isset($errorMsg)){
					$this->errorMessage = $errorMsg;
				}
				return false;
			}
		}

		$max = $config->getParameter('max');
		if(isset($max)){
			$date_max = \DateTime::createFromFormat($format, $max);
			if(! ($date <= $date_max)){
				$errorMsg = $config->getParameter("error_max");
				if(isset($errorMsg)){
					$this->errorMessage = $errorMsg;
				}
				return false;
			}
		}
		return true;
	}
	
	protected function checkEnum($value, $config){
		$options = $config->getConfig('options');
		$o_type = $options->getParameter('type');
		if($o_type == 'constant'){
			$values = preg_split("#(?<!\\\)/#", $options->getParameter("values"));
			$values = str_replace("\\/", "/", $values);
			if(in_array($value, $values)){
				return true;
			}
		}
		elseif($o_type == "custom"){
			$fieldPathArray = array_filter(explode('/', $config->getPath()));
			if(isset($this->custom_options)){
				$temp = &$this->custom_options;
				foreach($fieldPathArray as $key) {
					if(isset($temp[$key])){
						$temp = &$temp[$key];
					}
					else{
						throw new MFormValidatorException("Options for field ".join('/',$fieldPathArray)." defined as custom but no corresponding parameters found in params passed to constructor", 202);
					}
				}
				$values = $temp;
			}
			else{
				throw new MFormValidatorException("Options for field ".join('/',$fieldPathArray)." defined as custom but no corresponding parameters found in params passed to constructor", 200);
			}
			if(! is_array($values)){
				throw new MFormValidatorException("Custom options passed for field ".join('/',$fieldPathArray)." are not defined correctly", 201);
			}
			if(in_array($value, $values)){
				return true;
			}
		}
		$errorMsg = $config->getParameter("error_type");
		if(isset($errorMsg)){
				$this->errorMessage = $errorMsg;
		}
		return false;
	}
	
	protected function checkFile($value, $config){
		
		if(! $value['error'] == UPLOAD_ERR_OK) {
			$this->error = self::ERROR_SYSTEM;

			$maxUploadSize = ini_get('upload_max_filesize');
			$errorMessages = [
				UPLOAD_ERR_INI_SIZE => "File size too big (Max: $maxUploadSize)",
				UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form",
				UPLOAD_ERR_PARTIAL => "The file was only partially uploaded",
				UPLOAD_ERR_NO_FILE => "No file was uploaded",
				UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
				UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
				UPLOAD_ERR_EXTENSION => "File upload stopped by extension",
			];
			$this->errorMessage = $errorMessages[$value['error']] ?? "Unknown upload error (Code: ".$value['error'].")";
			return false;
		}

		if(! is_uploaded_file($value['tmp_name'])) {
			$this->error = self::ERROR_SYSTEM;
			$this->errorMessage = "Possible file upload attack";
			return false;
		}

		$allowedMimeTypes = $config->getParameter("file_mime_types");
		if(isset($allowedMimeTypes)) {
			$allowedMimeTypes = array_map('trim', explode(',', strtolower($allowedMimeTypes)));
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $value['tmp_name']);
			
			if(! in_array($mime, $allowedMimeTypes)){
				$this->error = self::ERROR_CHECK;
				$this->errorMessage = $config->getParameter("error_file_mime_type") ?: "File type not allowed";
				return false;
			}
		}
		$allowedExtensions = $config->getParameter("file_types");
		if(isset($allowedExtensions)){
			$allowedExtensions = array_map('trim', explode(',', strtolower($allowedExtensions)));
			$fileExtension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
			if(! in_array($fileExtension, $allowedExtensions)){
				$this->error = self::ERROR_CHECK;
				$this->errorMessage = $config->getParameter("error_file_types") ?: "File extension not allowed";
				return false;
			}
		}
		
		$maxSize = $config->getParameter("max_size");
		if(isset($maxSize) && $value['size'] > $maxSize) {
			$this->error = self::ERROR_CHECK;
			$this->errorMessage = $config->getParameter("error_max_size") ?: sprintf("File size exceeds maximum allowed size of %s", $maxSize);
			return false;
		}

		return true;
	}
	
	protected function checkComplex($value, $config){
		
		$subfields = $config->getConfigsNames();
		$subfieldsErrorRqMsg = $config->getParameter('error_incomplete');
		$subfieldsErrorMsg = $config->getParameter('error_subfield_error');
		
		foreach($subfields as $subfield){
			
			$fConf = $config->getConfig($subfield);
			$fType = $fConf->getParameter('type');
			
			
			$fRequired = $fConf->getParameter("required");
			$fErrorRqMsg = $fConf->getParameter("error_required");
			$fErrorMsg = $fConf->getParameter("error");
			
			if($fRequired){
				if(! isset($value[$subfield])){
					if(isset($fErrorRqMsg)){
						$this->errorMessage = "";
						if(isset($subfieldsErrorRqMsg)){
							$this->errorMessage .= $subfieldsErrorRqMsg;
							$this->errorMessage .= " : ";
						}
						$this->errorMessage .= $fErrorRqMsg;
					}
					elseif(isset($subfieldsErrorRqMsg)){
						$this->errorMessage = $subfieldsErrorRqMsg;
					}
					return false;
				}
			}
			
			if(isset($value[$subfield])){
				$checkMethod = "check".ucfirst($fType);
				if(! $this->$checkMethod($value[$subfield], $fConf)){
					if((!isset($this->errorMessage)) && isset($fErrorMsg)){
						$this->errorMessage = $fErrorMsg;
					}
					if(isset($subfieldsErrorMsg)){
						if(isset($this->errorMessage)){
							$this->errorMessage = $subfieldsErrorMsg." : ".$this->errorMessage;
						}
						else{
							$this->errorMessage = $subfieldsErrorMsg;
						}
					}
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * A method you can override in a subclass if cross-fields validation is needed
	 * @param type $vars
	 * @return boolean
	 */
	protected function globalCheck($vars){
		return true;
	}
	
	/**
	 * Sets error type. Internal use.
	 * @param integer $error one of the values of class constants ERROR_SYSTEM, ERROR_REQUIRED, ERROR_CHECK, ERROR_GLOBAL
	 *   if called in overridden globalCheck method, you should use self::ERROR_GLOBAL as parameter
	 */
	protected function setError($error){
		$this->error = $error;
	}
	
	/**
	 * 
	 * @param string $field the fieldname in error
	 */
	protected function setErrorField($field){
		$this->errorField = $field;
	}
	
	/**
	 * 
	 * @param string $message an error message
	 */
	protected function setErrorMessage($message){
		$this->errorMessage = $message;
	}
	
	
	/**
	 * Gets the error type
	 * @return integer the error type
	 */
	public function getError(){
		return $this->error;
	}
	
	/**
	 * Gets the field in error
	 * @return string the fieldname
	 */
	public function getErrorField(){
		return $this->errorField;
	}
	
	/**
	 * Gets the error message
	 * @return string the message
	 */
	public function getErrorMessage(){
		return $this->errorMessage;
	}
}
?>
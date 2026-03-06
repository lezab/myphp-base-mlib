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

namespace mlib\utils\config;
/**
 * La classe MConfig permet de charger et de travailler avec des fichiers de configuration d'un type à mi-chemin entre
 * les fichiers de configuration classiques (type Properties, cle=valeur) et les fichiers de type xml.<br><br>
 *
 * @author Denis ELBAZ
 * @version 1.0.1
 * @since 2006/02
 * @example
 * <pre>
 *  # un commentaire
 *
 *  cle1=val1
 *  <bloc1>
 *    clebloc1 = val=bloc1
 *    <bloc11> clebloc11 = valbloc11 </bloc11>
 *  </bloc1>
 *  <bloc2> # un autre commentaire
 *    clebloc2 = valbloc2
 *    <bloc21> clebloc211 = valbloc211; clebloc212 = valbloc212 </bloc21>
 *    <bloc22>
 *       <bloc221> clebloc221 = valbloc221 </bloc221>
 *       clebloc22
 *    </bloc22>
 *  </bloc2>
 *  cle2 = val2
 * </pre>
 * Si une cle n'a pas de valeur elle est considérée comme étant de type booléen et est positionnée à true.<br>
 * Le point virgule n'est pas obligatoire apres une paire clé-valeur s'il n'y a pas d'autre paire clé-valeur sur la même ligne.<br>
 * Les lignes vides et les lignes commentées ne sont pas pris en compte.<br>
 * On peut mettre des commentaires en fin de ligne.<br>
 * Les espaces ne sont pas pris en compte.<br>
 * Une cle ne peut pas contenir le signe "=", mais une valeur oui.
 *
 * @example
 * <pre>
 *   //Exemple d'utilisation
 *   try{
 *      $myconf = new MConfig("./myconf.conf");
 *   }
 *   catch(MConfigException $e){
 *      echo $e->getMessage();
 *   }
 * 
 *   echo $myconf->toString();
 *   $value = myconf->getParameter("cle1");
 *   echo "valeur : ".$value."&lt;br&gt;";
 * </pre>
 * 
 * @category utils
 */
class MConfig {

	private $config = array();
	private $name = "";
	private $path = "";

	private static $debugMode = false;
	
	/**
	 * Constructeur
	 * @param string $filename le fichier à charger
	 * @throws MConfigException
	 */
	public function __construct(string $filename = null, array $array_config = null){
		if(isset($filename)){
			if(! $input = file($filename)){
				throw new MConfigException("File not found: $filename", 0);
			}
			$i = 0;
			$line = $input[$i++];
			$key = null;
			$value = null;
			$tags = array();
			$currentTag = null;
			$endTag = null;
			$nodes = array();
	
			$currentNode = $this;
			$nodes[] = $currentNode;
	
			while(isset($line)){	
				// Suppression des espaces en début et fin de ligne
				$line = trim($line);
	
				// Commentaire ou ligne vide
				if (($line == "") || substr($line,0,1) == '#') {
					if(!($line == "")){
						self::debug("Comment : $line");
					}
					$line = isset($input[$i]) ? $input[$i++]: null;
					//$line = $input[$i++];
				}
				// Balise fin de groupe de valeurs
				elseif(substr($line,0,2) == '</'){
					$endTagEndigIndex = strpos($line, ">");
					$endTag = substr($line, 2, $endTagEndigIndex - 2);
					$endTag = trim($endTag);
					self::debug("End bloc : $endTag");
					if(! ($endTag == $currentTag)){
						throw new MConfigException("Configuration not correct : closing tag does not match opening tag ($filename : line $i)", 1);
					}
					else{
						array_pop($tags);
						$currentTag = (count($tags) > 0) ? $tags[count($tags) - 1] : null;
						array_pop($nodes);
						$currentNode = $nodes[count($nodes) - 1];
					}
					$line = substr($line, $endTagEndigIndex + 1);
				}
				// Balise debut de groupe de valeurs
				elseif(substr($line,0,1) == '<'){
					$beginTagEndingIndex = strpos($line, ">");
					$currentTag = substr($line, 1, $beginTagEndingIndex - 1);
					$currentTag = trim($currentTag);
					self::debug("Begin bloc : $currentTag");
					$tags[] = $currentTag;
	
					$temp = new MConfig();
					$temp->setName($currentTag);
					$temp->setPath($currentNode->getPath());
					$currentNode->setSubEntry($currentTag, $temp);
					$currentNode = $temp;
					$nodes[] = $currentNode;
					$line = substr($line, $beginTagEndingIndex + 1);
				}
				// Key - Value
				else{
					$parameter = null;
					$parameterEndingIndex = self::nextUnescaped($line,";");
					if($parameterEndingIndex !== false){
						$parameter = substr($line, 0, $parameterEndingIndex);
						$line = substr($line, $parameterEndingIndex + 1);
					}
					else{
						$parameterEndingIndex = self::nextUnescaped($line,"<");
						if($parameterEndingIndex !== false){
							$parameter = substr($line, 0, $parameterEndingIndex);
							$line = substr($line, $parameterEndingIndex - 1);
						}
						else{
							$parameterEndingIndex = self::nextUnescaped($line,"#");
							if($parameterEndingIndex !== false){
								$parameter = substr($line, 0, $parameterEndingIndex);
								self::debug("Ending Comment : ".substr($line, $parameterEndingIndex - 1));
								$line = isset($input[$i]) ? $input[$i++]: null;
							}
							else{
								$parameter = $line;
								$line = isset($input[$i]) ? $input[$i++]: null;
							}
						}
					}
					$parameter = trim($parameter);
					$stringTab =  explode("=", $parameter);
					$key = $stringTab[0];
					if(count($stringTab) < 2){
						$value = "true";
					}
					else{
						array_shift($stringTab);
						$value = join('=', $stringTab);
					}
					$key = trim($key);
					$value = trim($value);
					if(substr($value,0,1) == '"'){
						while(substr($value,-1) != '"'){
							$value .= PHP_EOL;
							$should_end = false;
							$valuePartEndingIndex = self::nextUnescaped($line,";");
							if($valuePartEndingIndex !== false){
								$valuePart = substr($line, 0, $valuePartEndingIndex);
								$line = substr($line, $valuePartEndingIndex + 1);
								$should_end = true;
							}
							else{
								$valuePartEndingIndex = self::nextUnescaped($line,"<");
								if($valuePartEndingIndex !== false){
									$valuePart = substr($line, 0, $valuePartEndingIndex);
									$line = substr($line, $valuePartEndingIndex - 1);
									$should_end = true;
								}
								else{
									$valuePartEndingIndex = self::nextUnescaped($line,"#");
									if($valuePartEndingIndex !== false){
										$valuePart = substr($line, 0, $valuePartEndingIndex);
										self::debug("Ending Comment : ".substr($line, $valuePartEndingIndex - 1));
										$line = $input[$i++];
									}
									else{
										$valuePart = $line;
										$line = isset($input[$i]) ? $input[$i++]: null;
									}
								}
							}
							$value .= trim($valuePart);
							if($should_end && (substr($value,-1) != '"')){
								throw new MConfigException("Configuration not correct : closing double quote not found for a double quotted value (line : $i)", 2);
							}
						}
						$value = substr($value, 1, -1);
					}
					$value = str_replace(array('\"', '\;', '\#', '\<', '\>'),
					                     array('"', ';', '#', '<', '>'),
					                     $value);
					if($value === "true" || $value === "TRUE"){
						$value = true;
					}
					if($value === "false" || $value === "FALSE"){
						$value = false;
					}
					$currentNode->setSubEntry($key, $value);
					self::debug("Key - Value : $key => $value");
				}
			}
			self::debug("reading conf file ok");
		}
		
		if(isset($array_config) && is_array($array_config)){
			$this->mergeRecursive($array_config);
		}
	}

	private static function nextUnescaped($haystack, $needle){
		//return strpos($haystack, $needle);
		//Plus compliqué, mais désormais on peut échaper des " ; # < dans des valeurs et les conserver
		return preg_match("/(?<!\\\)$needle/",$haystack, $out, PREG_OFFSET_CAPTURE) ? $out[0][1] : false;
	}
	
	private function mergeRecursive(array $array_config){
		foreach($array_config as $key => $value){
			if(is_array($value)){
				if(isset($this->config[$key]) && (! is_object($this->config[$key]))){
					throw new MConfigException("Cannot merge array config while key $key already exists in original config object and refers to a parameter");
				}
				if(! isset($this->config[$key])){
					$this->config[$key] = new MConfig();
				}
				$this->config[$key]->mergeRecursive($value);
			}
			else{
				$this->config[$key] = $value;
			}
		}
	}


	public function setName($name){
		$this->name = $name;
	}

	/**
	 * 
	 * @return string the config object's name
	 */
	public function getName(){
		return $this->name;
	}
	
	public function setPath($path){
		$this->path = $path;
	}

	/**
	 * 
	 * @return string the config object's path
	 */
	public function getPath(){
		return $this->path.$this->name."/";
	}

	public function setSubEntry($key, $entry){
		$this->config[$key] = $entry;
	}

	/**
	 * 
	 * @param string $key the parameter's name
	 * @return string|null the parameter value if exists or null if not.
	 */
	public function getParameter($key){
		if(array_key_exists($key, $this->config)){
			$tmp = $this->config[$key];
			if(isset($tmp) && (is_string($tmp) || is_bool($tmp))){
				return $tmp;
			}
		}
		return null;
	}
	
	/**
	 * 
	 * @param string $key the subconfig's name
	 * @return MConfig|null the MConfig object corresponding to the provided key if exists or null if not.
	 */
	public function getConfig($key){
		if(array_key_exists($key, $this->config)){
			$tmp = $this->config[$key];
			if(isset($tmp) && is_object($tmp)){
				return $tmp;
			}
		}
		return null;
	}
	
	/**
	 * 
	 * @return array[string] all the names of sub-entries of the config
	 */
	public function getSubEntriesNames(){
		return array_keys($this->config);
	}
    
	/**
	 * 
	 * @return array[string] all the paramameter's names of the config
	 */
	public function getParametersNames(){
		$names = array();
		foreach($this->config as $key => $object){
			if(is_string($object) || is_bool($object)){
				$names[] = $key;
			}
		}
		return $names;
	}
	
	/**
	 * 
	 * @return array[string] all the sub-config's names of the config
	 */
	public function getConfigsNames(){
		$names = array();
		foreach($this->config as $key => $object){
			if(is_object($object)){
				$names[] = $key;
			}
		}
		return $names;
	}

	/**
	 * 
	 * @param integer $offset just ignore this parameter
	 * @return string a readable representation of the MConfig object
	 */
	public function toString($offset = 0){
		$soffset = '';
		for($o=0; $o < $offset; $o++){
			$soffset .= '   ';
		}
		$string = "";
		foreach($this->config as $name => $value){
			if(is_object($value)){
				$string .= $soffset."MConfig :::: $name :".PHP_EOL;
				$string .= $value->toString($offset + 1);
			}
			else{
				if(is_bool($value)){
					$value = $value ? "true" : "false";
				}
				$string .= $soffset."Parameter :: $name --> $value".PHP_EOL;
			}
		}
		return $string;
	}
        
	/**
	 * @param $preserveEmptyValues bool if true a parameter like "paramName = " will be part of the array as an empty string value else it will not be set. Default false.
	 * @return array an array representation of the MConfig object
	 */
	public function toArray(bool $preserveEmptyValues = false){
		$arrayReturn = array();
		foreach($this->config as $name => $value){
			if(is_object($value)){
				if($preserveEmptyValues){
					$arrayReturn[$name] = $value->toArray();
				}
				else{
					$tmp = $value->toArray();
					if(!empty($tmp)){
						$arrayReturn[$name] = $tmp;
					}
				}
			}
			else{
				if($preserveEmptyValues){
					$arrayReturn[$name] = $value;
				}
				else{
					if($value != ""){
						$arrayReturn[$name] = $value;
					}
				}
			}
		}
		return $arrayReturn;
	}

	protected static function debug($message){
		if(self::$debugMode){
			if(is_array($message) || is_object($message)){
				error_log(print_r($message,true));
			}
			else{
				error_log($message);
			}
		}
	}
}
?>
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

namespace mlib\net\ldap;
/**
 * Class MLdap
 * A class which overlie php_ldap functions and tend to make ldap acces more simple
 * @author : Denis ELBAZ
 * @version : 1.0.0
 * 
 * @category net
 */
class MLdap {

	private $LDAP = null;

	private $address = null;
	private $port = null;
	private $bind_dn = null;
	private $bind_passwd = null;
	
	private $auth_base = null;
	private $auth_attribute = null;
	private $auth_filter = null;
	private $default_base = null;

	private static $debugMode = false;

	/**
	 * Constructor
	 * 
	 * @param string $address the ldap address (ex : ldap.mycorp.com) 
	 * @param string $port the ldap port (default : 389)
	 * @param string $bind_dn the user dn to bind with. Probably required if modification have to be made. (Ex cn=manager,dc=mycorp,dc=com)
	 * @param string $bind_passwd the user password if $bind_dn is provided.
	 */
	public function __construct($address, $port = 389, $bind_dn = null, $bind_passwd = null) {
		$this->address = $address;
		$this->port = $port;
		$this->bind_dn = $bind_dn;
		$this->bind_passwd = $bind_passwd;
	
		if(isset($this->port)){
			$ldap = ldap_connect($this->address, $this->port);
		}
		else{
			$ldap = ldap_connect($this->address);
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	
		if (!isset ($this->bind_dn) || ($this->bind_dn == "")) {
			$result = ldap_bind($ldap);
		} else {
			$result = ldap_bind($ldap, $this->bind_dn, $this->bind_passwd);
		}
		if (!$result) {
			$message  = "Connection failed. Check your parameters.".PHP_EOL;
			$message .= "More informations :".PHP_EOL;
			$message .= ldap_error($ldap);
			$message .= PHP_EOL;
			throw new MLdapException($message, ldap_errno($ldap));
		} else {
			$this->LDAP = $ldap;
		}
	}
	
	/**
	 * Destructor
	 * Closes the connection to ldap server
	 */
	public function __destruct() {
        if ($this->LDAP) {
			ldap_unbind($this->LDAP);
			$this->LDAP = null;
		}
    }
	
	/**
	 * Get the default base. This base is used when no base is given while using search or get method.
	 * 
	 * @return string|null default base if set or null if not
	 */
	public function getDefaultBase(){
		return $this->default_base;
	}

	/**
	 * Set the default base. This base will be used if no base is given while using search or get method.
	 *  
	 * @param string $base
	 */
	public function setDefaultBase($base){
		$this->default_base  = $base;
	}
	
	/**
	 * Searching entries from ldap
	 * Warning : sorting result on multivaluated attributes could lead to weird results
	 * 
	 * @param string $filter
	 * @param array|null $attributes les noms des attributs que l'on veut récupérer pour cette entrée
	 *	null indicates that all attributes should be retrieved.
	 *	an empty array indicates that none should be retrieved.
	 * @param string|null $ldap_base
	 * @param string|null $sortAttributes
	 * @return array|false an array representing the entries retrieved from ldap or false if no entries found. See http://php.net/manual/fr/function.ldap-get-entries.php for more information
	 * @throws MLdapException
	 */
	public function search($filter = "(objectclass=*)", $attributes = null, $ldap_base = null, $sortAttributes = null){
		$ldap = $this->LDAP;
		$base = isset($ldap_base) ? $ldap_base : $this->default_base;
		if(!isset($base)){
			throw new MLdapException("Method 'search' can be called only if \$ldap_base parameter is set or after the default base has been set (see : setDefaultBase method)", 1001);
		}
		self::log("Searching for $filter in $base");
		if($attributes == null){
			$sr = ldap_search($ldap, $base, $filter);
		}
		else{
  			$sr = ldap_search($ldap, $base, $filter, $attributes);
		}
		
		if(! $sr){
			throw new MLdapException("An error occurred while searching in ldap directory : ".ldap_error($ldap), ldap_errno($ldap));
		}
		$result = ldap_get_entries($ldap, $sr);
		if($result['count'] > 0){
			self::log("... entries found");
			if(isset($sortAttributes)){
				if(is_array($sortAttributes)){
					$params = array();
					$i = 0;
					foreach($sortAttributes as &$attribute){
						$params[$i] = array();
						$params[$i+1] = SORT_ASC;
						$params[$i+2] = SORT_REGULAR;
						$attribute = strtolower($attribute);
						$i += 3;
					}
					unset($attribute);
					foreach($result as $entry){
						$i = 0;
						foreach($sortAttributes as $attribute){
							if($entry != 'count'){
								if(isset($entry[$attribute][0])){
									$params[$i][] = $entry[$attribute][0]; // le tri peut donner des choses bizarres sur des attributs multivalués
								}
								else{
									$params[$i][] = 0;
								}
							}
							else{
								$params[$i][] = 0;
							}
							$i += 3;
						}
					}
					$params[$i] = &$result;
					call_user_func_array("array_multisort", $params);
				}
				else{
					$params = array();
					$sortAttributes = strtolower($sortAttributes);
					foreach($result as $entry){
						if($entry != 'count'){
							if(isset($entry[$sortAttributes][0])){
								$params[] = $entry[$sortAttributes][0]; // le tri peut donner des choses bizarres sur des attributs multivalués
							}
							else{
								$params[] = 0;
							}
						}
						else{
							$params[] = 0;
						}
					}
					array_multisort($params, $result);
				}
			}
			return $result;
		}
		self::log("... no entries found");
		return false;
	}
	
	/**
	 * Get an entry by its dn
	 * 
	 * @param string $dn
	 * @param array|null $attributes les noms des attributs que l'on veut récupérer pour cette entrée
	 *	null indicates that all attributes should be retrieved.
	 *	an empty array indicates that none should be retrieved.
	 * @return array|false une entrée : un tableau représesentant l'entrée ldap, false si l'entrée n'existe pas. See http://php.net/manual/fr/function.ldap-get-entries.php for more information
	 * @see ldap_get_entries
	 */
	public function get($dn, $attributes = null){
		$ldap = $this->LDAP;
		self::log("Retrieving $dn entry");
		if($attributes == null){
			$sr = ldap_read($ldap, $dn, "(objectclass=*)");
		}
		else{
  			$sr = ldap_read($ldap, $dn, "(objectclass=*)", $attributes);
		}
		if(! $sr){
			throw new MLdapException("An error occurred while retrieving entry from ldap directory : ".ldap_error($ldap), ldap_errno($ldap));
		}
		$result =  ldap_get_entries($ldap, $sr);
		return $result['count'] > 0 ? $result[0] : false;
	}
        
	/**
	 * Get an entry by filtering on its rdn in the base given or in the default base
	 * 
	 * @param string $filter a filter which define the entry. Should filter on the rdn.
	 *        Ex : uid=test or cn=test
	 *        Unlike filter in search method, the filter here must not begin and end with parenthesis
	 * @param array|null $attributes les noms des attributs que l'on veut récupérer pour cette entrée
	 *        null indicates that all attributes should be retrieved.
	 * 	      an empty array indicates that none should be retrieved.
	 * @param string|null $ldap_base the ldap base on which we should retrieve the entry. Required if the the default base has not been set.
	 *        default null. If null, the default base is used if it has been set.
	 * @return array|false une entrée : un tableau représesentant l'entrée ldap, false si l'entrée n'existe pas. See http://php.net/manual/fr/function.ldap-get-entries.php for more information
	 * @throws MLdapException
	 */
	public function getByRdn($filter, $attributes = null, $ldap_base = null){
		$ldap = $this->LDAP;
		$base = isset($ldap_base) ? $ldap_base : $this->default_base;
		if(!isset($base)){
			throw new MLdapException("Method 'getByRdn' can be called only if \$ldap_base parameter is set or after the default base has been set (see : setDefaultBase method)", 2001);
		}
		$dn = $filter.','.$base;
		self::log("Retrieving $dn entry");
		if($attributes === null){
			$sr = ldap_read($ldap, $dn, "(objectclass=*)");
		}
		else{
  			$sr = ldap_read($ldap, $dn, "(objectclass=*)", $attributes);
		}
		if(! $sr){
			throw new MLdapException("An error occurred while retrieving entry from ldap directory : ".ldap_error($ldap), ldap_errno($ldap));
		}
		$result =  ldap_get_entries($ldap, $sr);
		return $result['count'] > 0 ? $result[0] : false;
	}
        
	/**
	 * Check if an entry exists
	 * 
	 * @param string $dn
	 * @return bool true if the entry exists, false otherwise
	 * @throws MLdapException
	 */
	public function exists($dn){
		$ldap = $this->LDAP;
		self::log("Checking if $dn exists");
  		$sr = ldap_read($ldap, $dn, "(objectclass=*)", array());
		if(! $sr){
			throw new MLdapException(ldap_error($ldap), ldap_errno($ldap));
		}
		$result = ldap_get_entries($ldap, $sr);
		return $result['count'] > 0;
	}
        
	/**
	 * Add an entry
	 * 
	 * @param string $dn
	 * @param array $entry An array that specifies the information about the entry.
	 *                     The values in the entries are indexed by individual attributes.
	 *                     In case of multiple values for an attribute, they are indexed using integers starting with 0.
	 *                     Doit contenir tous les attributs de l'entrée.
	 * @throws MLdapException
	 */
	 public function add($dn, $entry){
		if(! ldap_add($this->LDAP, $dn, $entry)){
			throw new MLdapException(ldap_error($this->LDAP), ldap_errno($this->LDAP));
		}
	}
	
	/**
	 * Delete an entry
	 * 
	 * @param string $dn le dn de l'entrée
	 * @throws MLdapException
	 */
	public function delete($dn){
		if(! ldap_delete($this->LDAP, $dn)){
			throw new MLdapException(ldap_error($this->LDAP), ldap_errno($this->LDAP));
		}
	}
	
	/**
	 * Rename an entry
	 * 
	 * @param string $dn the dn of the entry
	 * @param string $newDn the new dn of the entry
	 * @throws MLdapException
	 */
	public function rename($dn, $newDn){
		self::log("Renaming $dn entry to $newDn");
		$newRdn = substr($newDn, 0, strpos($newDn, ','));
		$newBase = substr($newDn, strpos($newDn, ',')+1);
		if(! ldap_rename($this->LDAP, $dn, $newRdn, $newBase, TRUE)){
			throw new MLdapException(ldap_error($this->LDAP), ldap_errno($this->LDAP));
		}
	}
	
	
	/**
	 * Update an entry
	 * Met à jour une entrée en fonction de ce qui est passé
	 * $attributes ne doit contenir que les valeurs des attributs que l'on veut modifier, mais toutes les valeurs.
	 * On peut egalement utiliser cette methode pour ajouter un attribut, ou en supprimer un en indiquant un tableau vide.
	 *
	 * @param string $dn
	 * @param array $attributes
	 * @throws MLdapException
	 */
	public function modify($dn, $attributes){
		if(! ldap_modify($this->LDAP, $dn, $attributes)){
			throw new MLdapException(ldap_error($this->LDAP), ldap_errno($this->LDAP));
		}
	}
	
	/**
	 * Add values to an attribute of an entry
	 * Ajoute les valeurs $attribute['myattr'][] à l'attribut 'myattr' de l'entrée $dn.
	 * La modification est effectuée au niveau attribut, par opposition au niveau objet.
	 * Les additions au niveau objet sont réalisées par la méthode add
	 *
	 * @param string $dn
	 * @param array $attributes
	 * @throws MLdapException
	 */
	public function modifyAdd($dn, $attributes){
		if(! ldap_mod_add($this->LDAP, $dn, $attributes)){
			throw new MLdapException(ldap_error($this->LDAP), ldap_errno($this->LDAP));
		}
	}
	
	
	/**
	 * Delete values from an attribute of an entry
	 * Supprime les valeurs $attribute['myattr'][] à l'attribut 'myattr' de l'entrée $dn.
	 * La modification est effectuée au niveau attribut, par opposition au niveau objet.
	 * Les supressions au niveau objet sont réalisées par la méthode delete
	 *
	 * @param string $dn
	 * @param array $attributes
	 * @throws MLdapException
	 */
	public function modifyDel($dn, $attributes){
		if(! ldap_mod_del($this->LDAP, $dn, $attributes)){
			throw new MLdapException(ldap_error($this->LDAP), ldap_errno($this->LDAP));
		}
	}
	
	/**
	 * Retrieve binary attribute values
	 * La methode search, ecrite pour simplifier la recherche et l'accès aux résultats ne permet pas, dans le cas d'attribut
	 * à valeur binaire d'accéder directement aux valeurs.
	 * Cette fonction le permettra, mais il faudra repasser en parametre le dn de l'entrée concernée.
	 * Ex :
	 *   // Pour avoir le mail d'une personne
	 *   $LDAP = new MLdap($ldapurl);
	 *   $results = $LDAP->search("(uid=toto)", array('mail'));
	 *   $mail = $results[0]['mail'][0];
	 * 
	 *   // mais pour avoir la photo (type d'attribut binaire) :
	 *   $photos = $LDAP->getBinaryAttribute("uid=toto,$base", "jpegphoto");
	 *   $photo = photos[0];
	 *
	 * @param string $dn
	 * @param string $attribute
	 * @return array|null
	 * @throws MLdapException
	 */
	 public function getBinaryAttribute($dn, $attribute){
		$ldap = $this->LDAP;
		$filter = "(".substr($dn, 0, strpos($dn, ',')).")";
		$base = substr($dn, strpos($dn, ',')+1);
		$sr = ldap_search($ldap, $base, $filter, array("$attribute"));	
		if(! $sr){
			throw new MLdapException("An error occurred while retrieving informations from ldap directory : ".ldap_error($ldap), ldap_errno($ldap));
		}
		$entry = ldap_first_entry($ldap, $sr);
		$attrs = ldap_get_attributes($ldap, $entry);
        if($attrs['count'] > 0){
			return ldap_get_values_len($ldap, $entry, $attribute);
		}
		return null;
	}
	
	/**
	 * Set authentication parameters
	 * An instance of MLdap can be used to authenticate users.
	 * For that, these parameter should be set before using the authenticate method.
	 *  
	 * @param string $auth_attribute the attribute in the branch used to authenticate (often the rdn)
	 * @param string|null $auth_base the base in which user should be searched for authentication
	 * @param string|null $auth_filter a filter in ldap format that should be applied to filter users in the branch if not all users should be allowed.
	 *                    If not set every user will be authenticated if good login and password are provided to authenticate method.
	 */
	 public function setAuthParameters($auth_attribute, $auth_base = null, $auth_filter = null){
		$this->auth_base = $auth_base;
		$this->auth_attribute = $auth_attribute;
		$this->auth_filter = $auth_filter;
	}
	
	/**
	 * Get the authentication attribute
	 * 
	 * @return string|null id attribute used to authenticate if set, or NULL if not
	 * @see setAuthParameters
	 */
	public function getAuthAttribute(){
		return $this->auth_attribute;
	}
	
	/**
	 * Get the authentication base
	 * 
	 * @return string|null base in which user should be searched for authentication if set, or NULL if not
	 * @see setAuthParameters
	 */
	public function getAuthBase(){
		return $this->auth_base;
	}
	
	/**
	 * Get the authentication filter
	 * 
	 * @return string|null filter that should be applied for authentication if set, or NULL if not
	 * @see setAuthParameters
	 */
	public function getAuthFilter(){
		return $this->auth_filter;
	}
	
	/**
	 * Authenticate a user in ldap. Auth parameters should have been set.
	 * 
	 * @param string $login
	 * @param string $passwd
	 * @return bool true if success, false if invalid credentials.
	 * @throws MLdapException
	 * @see setAuthParameters
	 */
	public function authenticate($login, $passwd) {
		if(! (isset($this->auth_attribute))){
			throw new MLdapException("Method 'authenticate' could not be called. Parameters needed to proceed have not been set in this MLdap instance.", 3001);
		}
	
		$result = $this->search("(".$this->auth_attribute."=".$login.")", array(), $this->auth_base);
		if($result){
			if($result['count'] > 1){
				//throw new MLdapException("Could not authenticate user. Multiple users found matching ".$this->auth_attribute."=".$login, 3002);
				return false;
			}
			else{
				$ldap_bind_dn = $result[0]["dn"];
			}
		}
		else{
			//throw new MLdapException("Could not authenticate user. No user found matching ".$this->auth_attribute."=".$login, 3003);
			return false;
		}
	
		$ldap = ldap_connect($this->address, $this->port);
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	
		if($ldap) {
			$result = ldap_bind($ldap, $ldap_bind_dn, $passwd);
			
			if (!$result) {
				ldap_unbind($ldap);
				return false;
			}
			else {
				if(isset($this->auth_filter)){
					$filter = '(&'.$this->auth_filter.'('.$this->auth_attribute.'='.$login.'))';
					$sr = ldap_search($ldap, $ldap_bind_dn, $filter);
					$result = ldap_get_entries($ldap, $sr);
					$nbEntries = $result['count'];
					ldap_unbind($ldap);
					if ($nbEntries > 0) {
						return true;
					}
					return false;
				}
				else{
					return true;
				}
			}
		}
		else {
			throw new MLdapException("Error connecting to LDAP server ".$this->address, 3000);
		}
	}
	
	/**
	 * Get the first free value found of an unsigned int attribute.
	 *       Ex : uidNumber. The uidNumber must be unique among all the entries of an ldap. It can be usefull to know the first value which is not used.
	 *       However, this method does not proceed on the entire ldap but only on the base given or on the default base.
	 * @param string $filter
	 * @param string $attribute
	 * @param integer $min
	 * @param integer $max
	 * @param string $ldap_base
	 * @return integer|false the first free value found or false if not found
	 */
	function getFirstFreeAttribute($filter = "", $attribute, $min = 0, $max = null, $ldap_base = null) {
		$ldap = $this->LDAP;
		$filter = "(&($attribute=*)$filter)";
		$base = isset($ldap_base) ? $ldap_base : $this->default_base;
		self::log("Seaching for first free $attribute value (between $min and $max) in $base");

		$sr = ldap_search($ldap, $base, $filter, array ("$attribute"));
		$result = ldap_get_entries($ldap, $sr);
		$nbEntries = $result['count'];
	
		if($nbEntries == 0){
			return $min;
		}
	
		$values = array ();
		for ($i = 0; $i < $nbEntries; $i++) {
			$values[] = $result[$i]["$attribute"][0];
		}
		sort($values);
	
		// 1 - on saute toutes les valeurs jusqu'à min
		$index = 0;
		while (isset($values[$index]) && ($values[$index] < $min)) {
			$index++;
		}
		// Si la boucle précédente s'est arrétée parce qu'on a passé en revue toutes les valeurs
		// ou bien que on a atteint le min et la valeur est strictement supérieure au min
		if ((!isset ($values[$index])) || ($values[$index] > $min)) {
			return $min;
		}

		// 2 - On cherche le premier "trou"
		if (isset ($max)) {
			while (isset ($values[$index+1]) && ($values[$index +1] - $values[$index] <= 1) && ($values[$index +1] < $max)) {
				$index++;
			}
			if (isset ($values[$index+1]) && ($values[$index +1] >= $max)) {
				return false;
			}
			else {
				return $values[$index] + 1;
			}
		}
		else {
			while (isset ($values[$index+1]) && ($values[$index +1] - $values[$index] <= 1)) {
				$index++;
			}
			return $values[$index] + 1;
		}
	}
        
	private static function log($message){
		if(self::$debugMode){
			if(is_array($message)){
				error_log(print_r($message,true));
			}
			else{
				error_log($message);
			}
		}
	}
}
?>
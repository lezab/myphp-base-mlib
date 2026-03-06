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

namespace mlib\utils\crypto;
/**
 * MEncoderDecoder
 * A class which provide easy methods to reversible crypt strings
 * @author : Denis ELBAZ
 * @version : 3.0
 * 
 * @category utils
 */
class MEncoderDecoder{
	
	private $key = null;
	private $privateKey = null;
	private $symetric = true;
	private $method = 'AES-256-CBC';
	private $saltLength = 16;
	
	/**
	 * 
	 * @param string $key a key for symetric encoding or a public key in case of non symetric encoding. Could be null, if $symetricEncoding is set to false and $privateKey is given
	 * @param boolean $symetricEncoding says if encoding is symetric, ie. $key could be use to encode and decode. Default true.
	 * @param string $privateKey if $symetriEncoding is false, should be set to decode messages.
	 * @throws MEncoderDecoderException
	 */
	public function __construct($key = null, $symetricEncoding = true, $privateKey = null){
		if (! function_exists("openssl_encrypt")) {
			throw new MEncoderDecoderException("MEncoderDecoder rely on openssl librairy. It seems the librairy is missing. Please check you php install");
		}
		
		if($symetricEncoding){
			if(! isset($key)){
				throw new MEncoderDecoderException("MEncoderDecoder constructor should be called with a key. See constructor's parameters.");
			}
			$this->symetric = true;
		}
		else{
			if(! (isset($key) || isset($privateKey))){
				throw new MEncoderDecoderException("MEncoderDecoder constructor should be called with a key. See constructor's parameters.");
			}
			$this->symetric = false;
		}
		
		if(isset($key)){
			$this->key = $key;
		}
		if(isset($privateKey)){
			$this->privateKey = $privateKey;
		}
	}
	
	private static function getRandomString($length){
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$str = '';
		for ($i=0; $i < $length; $i++){
			$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
		}
		return $str;
	}
	
	public function encode($string){
		if(! isset($this->key)){
			throw new MEncoderDecoderException("MEncoderDecoder encode method could not be called in MEncodeDecoder object instanciate with no key. See constructor's parameters.");
		}
		if($this->symetric){
			$salt = self::getRandomString($this->saltLength); //Not really a salt but an "initilisation vector" for openssl_encrypt function
			return $salt.openssl_encrypt($string, $this->method, $this->key, 0, $salt);
		}
		else{
			if(openssl_public_encrypt($string, $result, $this->key)){
				return $result;
			}
			else{
				throw new MEncoderDecoderException("MEncoderDecoder : error while encoding : ".openssl_error_string());
			}
		}
	}
	
	public function decode($string){
		if($this->symetric){
			return openssl_decrypt(substr($string, $this->saltLength), $this->method, $this->key, 0, substr($string, 0, $this->saltLength));
		}
		else{
			if(! isset($this->privateKey)){
				throw new MEncoderDecoderException("MEncoderDecoder decode method could not be called in MEncodeDecoder object instanciate with no private key and symetric encoding set to false. See constructor's parameters.");
			}
			if(openssl_private_decrypt($string, $result, $this->privateKey)){
				return $result;
			}
			else{
				throw new MEncoderDecoderException("MEncoderDecoder : error while decoding : ".openssl_error_string());
			}
		}
	}
}
?>
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

namespace mlib\net\mail;

/**
 * MMailer
 * A php mailer. It sends messages in html format. It allows attachments
 * 
 * If sendmail is configured on the server, there's nothing more to do than calling
 * send() static method
 * Else, you must call setSmtp() method first, then call send() method.
 * setSmtp method could be call once for sending several mails
 * In any case the mailer will try to send mails using php mail function first, then if failed
 * it will use an internal smtp method.
 * 
 * @author : Denis ELBAZ
 * @version : 1.0.4
 * 
 * @category net
 */
class MMailer{
	private static $smtp = null;
	private static $port = 25;
	private static $secure_mode = false;
	private static $login = null;
	private static $password = null;
	private static $auth_type = null;
	
	private static $simulation_mode = false;
	private static $force_smtp = false;
	
 	private static $client_sockets_pool = array();
	
	private static $debugMode = false;
	
	/**
	 * Sets the smtp server to use if sendmail is not configured
	 * 
	 * @param type $adress
	 * @param type $port
	 * @param type $secure_mode could be null or 'tls' or 'ssl'
	 * @param type $login
	 * @param type $password
	 * @param type $auth_type one of 'PLAIN', 'LOGIN', 'CRAM-MD5' or 'XOAUTH'. Default null : means it will use one of auth method
	 * found on server among PLAIN, LOGIN or CRAM-MD5 if login and password are given and XOAUTH if only login is given
	 */
	public static function setSmtp($adress, $port = 25, $secure_mode = null, $login = null, $password = null, $auth_type = null){
		self::$smtp = $adress;
		self::$port = $port;
		self::$secure_mode = $secure_mode;
		self::$login = $login;
		self::$password = $password;
		self::$auth_type = $auth_type;
		
		
		if(isset($auth_type) && $auth_type == 'XOAUTH'){
			if(isset($password)){
				throw new MMailerConfigException("Do not set password for XOAUTH auth method.");
			}
		}
		else{
			if(isset($login) && (! isset($password))){
				throw new MMailerConfigException("You must set a password for smtp authentication.");
			}
		}
	}
	
	
 	/**
	 * Turns the mailer into simulation mode : the send method simply return true without sending anything
	 * It could be usefull in development process
	 */
	public static function setSimulationMode($mode = true){
		self::$simulation_mode = (bool)$mode;
	}
	
	/**
	 * Forces or unforces the mailer to use SMTP only, skipping the php mail() function attempt
	 * This is useful when you want to ensure all emails go through the configured SMTP server
	 * 
	 * @param bool $force Set to true to force SMTP mode, false to use default behavior (try mail() first, then SMTP)
	 */
	public static function setForceSmtpMode($force = true){
		self::$force_smtp = (bool)$force;
	}
 	
 	/**
 	 * The only static method to use
 	 * @param string $from
 	 * @param string $to
 	 * @param string $subject
 	 * @param string $message
 	 * @param array $additional_params possible keys are (case sensitive): 'Cc', 'Bcc', 'Reply-To' or 'Return-Path'
	 * @param mixed $attachments	if single value, should be a filename.
	 *                          	if multiple attachments or if you want to specify a different name for the file in attachment, should be an array
	 *	Ex : 
	 *		'myfile.pdf'
	 *		array('myfile.pdf', 'myfile.jpg')
	 *		array('yourdocument.pdf' => 'myfile.pdf', 'yourimagename.jpg' => 'myfile.jpg')
	 * 
 	 * @return boolean true in case of success, false if failed
 	 */
 	public static function send($from, $to, $subject, $message, $additional_params = null, $attachments = null){
 		if(self::$simulation_mode){
			return true;
		}
		
		$boundary = 'mixed-'.md5(uniqid(time()));
		$alt_boundary = 'alt-'.md5(uniqid(time()));
		
		$return_path_param = null;
		$headers  = "MIME-version: 1.0\r\n";
		$headers  = "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
 		$headers .= "X-Mailer: mail\r\n";
 		$headers .= "From: $from\r\n";
 		if(isset($additional_params) && is_array($additional_params)){
 			foreach($additional_params as $key => $value){
 				switch($key){
 					case 'Cc' :
 						$headers .= "Cc: $value\r\n";
 						break;
 					case 'Bcc' :
 						$headers .= "Bcc: $value\r\n";
 						break;
 					case 'Reply-To' :
 						$headers .= "Reply-To: $value\r\n";
 						break;
					case 'Message-Id' :
						$headers .= "Message-Id: $value\r\n";
 						break;
					case 'In-Reply-To' :
						$headers .= "In-Reply-To: $value\r\n";
						break;
 					case 'Return-Path' :
						$headers .= "Return-Path: $value\r\n";
 						$return_path_param = "-f $value";
 						break;
 					default : break;
 				}
 			}
 		}
		

		$final_message  = "--$boundary\r\n";
		$final_message .= "Content-Type: multipart/alternative; boundary=\"$alt_boundary\"\r\n\r\n";
		$final_message .= "This is a multi-part message in MIME format.\r\n";
		$final_message .= "--$alt_boundary\r\n";
		$final_message .= "Content-Type: text/plain; charset= utf-8\r\n\r\n";
		$final_message .=  strip_tags($message)."\r\n";
		$final_message .= "--$alt_boundary\r\n";
		$final_message .= "Content-Type: text/html; charset= utf-8\r\n\r\n";
		$final_message .=  $message."\r\n";
		$final_message .= "--$alt_boundary--\r\n";

		if(isset($attachments)){
			if(is_array($attachments)){
				foreach($attachments as $name => $filename){
					if(is_int($name)){
						$name = $filename;
					}
					$final_message .= "\r\n--$boundary\r\n";
					$final_message .= "Content-Type: application/octet-stream; name=\"$name\"\r\n";
					$final_message .= "Content-Transfer-Encoding: base64\r\n";
					$final_message .= "Content-Disposition: attachment\r\n\r\n";
					$final_message .= chunk_split(base64_encode(file_get_contents($filename)))."\r\n\r\n";
				}
			}
			else{
				$final_message .= "\r\n--$boundary\r\n";
				$final_message .= "Content-Type: application/octet-stream; name=\"$attachments\"\r\n";
				$final_message .= "Content-Transfer-Encoding: base64\r\n";
				$final_message .= "Content-Disposition: attachment\r\n\r\n";
				$final_message .= chunk_split(base64_encode(file_get_contents($attachments)))."\r\n\r\n";
			}
		}
		$final_message .= "--$boundary--";
		
		
 		if(!self::$force_smtp && mail($to, mb_encode_mimeheader($subject, "ISO-8859-1"), $final_message, $headers, $return_path_param)){
			return true;
		}
		else{
			if(isset(self::$smtp)){
 				return self::sendSMTP($boundary, $from, $to, $subject, $final_message, $additional_params);
			}
			else{
				if(self::$force_smtp){
					$message  = "Mode SMTP forcé activé mais aucun serveur SMTP n'a été configuré.".PHP_EOL;
					$message .= "Vous devez appeler la méthode setSmtp avant l'appel à la fonction mail";
				}
				else{
					$message  = "Impossible d'envoyer le mail : serveur SMTP non configuré.".PHP_EOL;
					$message .= "Vous devez soit configurer sendmail sur la machine, soit appeler la méthode setSmtp avant l'appel à la fonction mail";
				}
				throw new MMailerConfigException($message);
			}
 		}
 	}
	
 	private static function sendSMTP($boundary, $from, $to, $subject, $message, $additional_params = null){
 		
 		// on calcule les entêtes
 		// on constitue au passage la liste des destinataires
 		// et vérifie également que le vrai sender n'a pas été spécifié
 	 	$headers  = "";
 	 	$true_from = $from;
 	 	// On traite le return path avant les autres parce qu'il doit tjs figurer en premier
 		if(isset($additional_params) && is_array($additional_params)){
 			if(isset($additional_params['Return-Path'])){
 				$headers .= "Return-Path: ".$additional_params['Return-Path']."\r\n";
 				$true_from = $additional_params['Return-Path'];
 				// On ne peut pas mettre un return-path différent du vrai from (le MAIL FROM du protocole)
 				// Le champs From de l'entête du mail lui, peut contenir ce qu'on veut
 			}
 		}
		$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
		$headers .= "X-Mailer: MMail mailer\r\n";
		$headers .= "From: $from\r\n";
		$headers .= "Subject: ".mb_encode_mimeheader($subject, "ISO-8859-1")."\r\n";
		$headers .= "To: $to\r\n";
		
		$to_array = explode(',', $to);
		
 		if(isset($additional_params) && is_array($additional_params)){
 			foreach($additional_params as $key => $value){
 				switch($key){
 					case 'Cc' :
 						$headers .= "Cc: $value\r\n";
 						$cc_array = explode(',', $value);
 						foreach($cc_array as $i => $cc){
 							if(!in_array($cc, $to_array)){
 								$to_array[] = $cc;
 							}
 						}
 						break;
 					case 'Bcc' :
 						$headers .= "Bcc: $value\r\n";
 						$bcc_array = explode(',', $value);
 						foreach($bcc_array as $i => $bcc){
 							if(!in_array($bcc, $to_array)){
 								$to_array[] = $bcc;
 							}
 						}
 						break;
 					case 'Reply-To' :
 						$headers .= "Reply-To: $value\r\n";
 						break;
					case 'Message-Id' :
						$headers .= "Message-Id: $value\r\n";
 						break;
					case 'In-Reply-To' :
						$headers .= "In-Reply-To: $value\r\n";
						break;
 					default : break;
 				}
 			}
 		}
 		
 		
		// on utilise la boundary comme identifiant unique de la connexion
		self::$client_sockets_pool[$boundary] = self::getSocket();
		
		//on recoit la ligne qui nous dit que l'on est connecté
		self::receiveDAta($boundary, "220", "Nothing (connection initialized)");

		
		$helo = 'EHLO';
		self::log("sending EHLO ...");
		self::sendDAta($boundary, "EHLO locahost");
		try{
			self::log("receiving datas after EHLO ...");
			$server_response = self::receiveMultiDAta($boundary, "250", "EHLO locahost");
			if(isset(self::$login)){
				if(! (isset(self::$secure_mode) && (self::$secure_mode == 'tls'))){ // Sinon on a pas besoin de parser, ça sera fait quand on renverra EHLO après être passé en TLS
					self::log("parsing datas to find auth methods ...");
					$lines = explode("\n", $server_response);

					// la première ligne ne nous sert pas
					array_shift($lines);
					foreach ($lines as $line) {
						//First 4 chars contain response code followed by - or space
						$line = trim(substr($line, 4));
						if(! empty($line)){
							$tokens = explode(' ', $line);
							$extension = array_shift($tokens);
							if($extension == 'AUTH'){
								$auth_methods = $tokens;
							}
						}
					}
					if((! isset($auth_methods)) || empty($auth_methods)){
						throw new MMailerException("Could not authenticate on server : no authentication method found", 2, null);
					}
				}
			}
		}
		catch(MMailerProtocolException $e){
			if(isset(self::$login)){
				throw new MMailerException("Could not authenticate on server that not supports EHLO message", 1, $e);
			}
			else{
				self::sendDAta($boundary, "HELO locahost");
				self::receiveDAta($boundary, "250", "HELO locahost");
				$helo = 'HELO';
			}
		}
		
		if(isset(self::$secure_mode) && (self::$secure_mode == 'tls')){
			$auth_methods = self::enableTLSOnSocket($boundary, $helo);
		}
		
		
		// Authenticate if required
		if(isset(self::$login)){
			if(isset(self::$auth_type)){
				if(! in_array(self::$auth_type, $auth_methods)){
					throw new MMailerException(self::$auth_type." method is not supported on this server", 3, $e);
				}
			}
			else{
				if(! isset(self::$password)){
					self::$auth_type = 'XOAUTH';
				}
				else{
					self::$auth_type = $auth_methods[0];
				}
			}
			
			
			switch(self::$auth_type) {
				case 'PLAIN':
					self::sendDAta($boundary, 'AUTH PLAIN '.base64_encode("\0".self::$login."\0".self::$password));
					self::receiveDAta($boundary, "235", 'AUTH PLAIN '.base64_encode("\0".self::$login)."xxxxxxxxxxxxxxxxxxx");
					break;
				
				case 'LOGIN':
					self::sendDAta($boundary, 'AUTH LOGIN');
					self::receiveDAta($boundary, "334", 'AUTH LOGIN');
					self::sendDAta($boundary, base64_encode(self::$login));
					self::receiveDAta($boundary, "334", base64_encode(self::$login));
					self::sendDAta($boundary, base64_encode(self::$password));
					self::receiveDAta($boundary, "235", "xxxxxxxxxxxxxxxxxxx");
					break;
				
				case 'CRAM-MD5':
					self::sendDAta($boundary, 'AUTH CRAM-MD5');
					$response = self::receiveDAta($boundary, "334", 'AUTH CRAM-MD5');
					$code = base64_decode(substr($response, 4));
					self::sendDAta($boundary, base64_encode(self::$login.' '.self::hmac($code, self::$password)));
					self::receiveDAta($boundary, "235", "xxxxxxxxxxxxxxxxxxx");
					break;
				
				case 'XOAUTH':
					throw new MMailerException(self::$auth_type." method is not yet supported on MMailer client", 4, $e);
					break;
				default:
					throw new MMailerException("No auth method defined ???", 5, $e);
					break;
			}
		}
		
		// on envoie l'adresse de l'expediteur
		self::sendDAta($boundary, "MAIL FROM: <$true_from>");

		//on nous dit que ca s'est bien passé
		self::receiveDAta($boundary, "250", "MAIL FROM: <$true_from>");
 		
 		//on envoie l'adresse de tous les destinataires
 		foreach($to_array as $i => $to_value){
 			self::sendDAta($boundary, "RCPT TO: <$to_value>");
			//on recupère la réponse
			self::receiveDAta($boundary, "250", "RCPT TO: <$to_value>");
 		}
		
		//on passe aux choses serieuses pour commencer l'envoi du corps
		self::sendDAta($boundary, "DATA");
		
		//on verifie que le serveur est pret
		self::receiveDAta($boundary, "354", "DATA");
 		
		// on envoie d'abord les entêtes
		self::sendDAta($boundary, "$headers");
				
		//on envoi le contenu du mail
		self::sendDAta($boundary, $message);
		
		//pour finir l'envoi d'un mail il faut envoyer un point sur une seule ligne
		self::sendDAta($boundary, ".");
		
		//on verifie que le mail a été accepté
		self::receiveDAta($boundary, "250", $headers.$message.".");
		
		//on quitte
		self::sendDAta($boundary, "QUIT");

		//on nous dit que ca a bien quitté
		self::receiveDAta($boundary, "221", "QUIT");

		// on ferme la connexion
		fclose(self::$client_sockets_pool[$boundary]);
		unset(self::$client_sockets_pool[$boundary]);
		return true;
 	}
 	
	
	private static function getSocket(){
		if(isset(self::$secure_mode) && (! defined('OPENSSL_ALGO_SHA256'))){
			throw new MMailerException("Extension open_ssl manquante : connexion smtp sécurisée impossible");
		}
		$error_code = 0;
		$error_message = '';
		
		if(function_exists('stream_socket_client')){
			if((! isset(self::$secure_mode)) || (self::$secure_mode == 'tls')){
				self::log("opening socket");
				$socket = stream_socket_client(self::$smtp.":".self::$port, $error_code, $error_message);
			}
			elseif(self::$secure_mode == 'ssl'){
				self::log("opening ssl secured socket");
				$socket = stream_socket_client("ssl://".self::$smtp.":".self::$port, $error_code, $error_message);
			}
			else{
				throw new MMailerConnectionException("Connexion impossible sur ".self::$smtp.":".self::$port." : Mode secure ".self::$secure_mode." inconnu");
			}
			/*stream_set_read_buffer($socket, 0);
			stream_set_write_buffer($socket, 0);*/
		}
		else{
			if(isset(self::$secure_mode) && (self::$secure_mode == 'ssl')){
				throw new MMailerConnectionException("Connexion impossible sur ".self::$smtp.":".self::$port." : fonction stream_socket_client introuvable (mode secure non applicable sur une socket obtenue avec fsockopen)");
			}
			$socket = fsockopen(gethostbyname(self::$smtp), self::$port, $error_code, $error_message);
		}
 		if(! $socket){
			$message = "Connexion impossible sur ".self::$smtp.":".self::$port;
			if($error_message != ''){
				$message .= " ($error_message)";
			}
			throw new MMailerConnectionException($message, $error_code);
		}
		return $socket;
	}
	
	
	private static function enableTLSOnSocket($socket_id, $helo){
		self::log("starting tls on stream");
		self::sendDAta($socket_id, "STARTTLS");
		self::receiveDAta($socket_id, "220", "STARTTLS");
		
		
		//Allow the best TLS version(s) we can
		$method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

		//PHP 5.6.7 dropped inclusion of TLS 1.1 and 1.2 in STREAM_CRYPTO_METHOD_TLS_CLIENT
		//so add them back in manually if we can
		if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
			$method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
			$method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
		}

		if(! stream_socket_enable_crypto(self::$client_sockets_pool[$socket_id], true, $method)){
			throw new MMailerConnectionException("Connexion impossible sur ".self::$smtp.":".self::$port." : Echec du passage en mode TLS");
		}
		
		//Must resend EHLO or HELO after TLS negotiation
		self::log("sending again $helo after tls negociation");
		$auth_methods = array();
		self::sendDAta($socket_id, "$helo locahost");
		if($helo == 'EHLO'){
			try{
				if(isset(self::$login)){
					$server_response = self::receiveMultiDAta($socket_id, "250", "EHLO locahost");
					$lines = explode("\n", $server_response);

					// la première ligne ne nous sert pas
					array_shift($lines);
					foreach ($lines as $line) {
						//First 4 chars contain response code followed by - or space
						$line = trim(substr($line, 4));
						if(! empty($line)){
							$tokens = explode(' ', $line);
							$extension = array_shift($tokens);
							if($extension == 'AUTH'){
								$auth_methods = $tokens;
							}
						}
					}
					if((! isset($auth_methods)) || empty($auth_methods)){
						throw new MMailerException("Could not authenticate on server : no authentication method found", 2);
					}
				}
				else{
					self::receiveDAta($socket_id, "250", "EHLO locahost");
				}
			}
			catch(MMailerProtocolException $e){
				throw new MMailerException("Problem while sending EHLO again after TLS negociation", 1, $e);
			}
		}
		else{
			self::receiveDAta($socket_id, "250", "$helo locahost");
		}
		return $auth_methods;
	}
	
	
 	private static function receiveData($socket_id, $expected=null, $sent=null){
 		$received = fgets(self::$client_sockets_pool[$socket_id], 128);
			
		if($received == ''){
			$message  = "No response from SMTP server".PHP_EOL;
			$message .= "Expected : $expected".PHP_EOL;
			$message .= "Sent : $sent".PHP_EOL;
			
			fclose(self::$client_sockets_pool[$socket_id]);
			unset(self::$client_sockets_pool[$socket_id]);
			
			throw new MMailerProtocolException($message);
		}
		
		if(isset($expected)){
			if(!preg_match("#^$expected#",$received)){
				$message  = "Bad response from SMTP server".PHP_EOL;
				$message .= "Sent : $sent".PHP_EOL;
				$message .= "Expected : $expected".PHP_EOL;
				$message .= "Received : $received".PHP_EOL;
				
				fclose(self::$client_sockets_pool[$socket_id]);
				unset(self::$client_sockets_pool[$socket_id]);

				throw new MMailerProtocolException($message);
			}
		}
		
		return $received;
 	}
	
	private static function receiveMultiData($socket_id, $expected=null, $sent=null){
		$message  = "Problem while reading response from SMTP server".PHP_EOL;
		$message .= "Expected : $expected".PHP_EOL;
		$message .= "Sent : $sent".PHP_EOL;
		
		$received = '';
		
		$loop = 1;	
		while(is_resource(self::$client_sockets_pool[$socket_id]) && (! feof(self::$client_sockets_pool[$socket_id]))) {
			
			$streamsR = [self::$client_sockets_pool[$socket_id]];
			$streamsW = null;
			if(stream_select($streamsR, $streamsW, $streamsW, 1) !== false){
				$str = fgets(self::$client_sockets_pool[$socket_id]);
				self::log($loop++." - ".$str);

				$received .= $str;
				if((! $str) || ($str == null) || $str == ''){
					self::log("str vide");
					
					/*fclose(self::$client_sockets_pool[$socket_id]);
					unset(self::$client_sockets_pool[$socket_id]);
					throw new MMailerProtocolException($message);*/
					break;
				}
				if(!isset($str[3]) || $str[3] === ' ' || $str[3] === "\r" || $str[3] === "\n") {
					self::log("str[3] vide ou fin de ligne");
					break;
				}
				if((substr_compare($str, "\r", -strlen("\r")) !== 0) && (substr_compare($str, "\n", -strlen("\n")) !== 0)){
					self::log("pas de retour à la ligne ligne à la fin de str");
					break;
				}
				$info = stream_get_meta_data(self::$client_sockets_pool[$socket_id]);
				if ($info['timed_out']) {
					self::log("pas de retour à la ligne ligne à la fin de str");
					break;
				}
			}
			else{
				throw new MMailerProtocolException($message);
			}
		}
		
        self::log($received);
		
		if($received == ''){
			$message  = "No response from SMTP server".PHP_EOL;
			$message .= "Expected : $expected".PHP_EOL;
			$message .= "Sent : $sent".PHP_EOL;
			
			fclose(self::$client_sockets_pool[$socket_id]);
			unset(self::$client_sockets_pool[$socket_id]);
			
			throw new MMailerProtocolException($message);
		}
		
		if(isset($expected)){
			if(!preg_match("#^$expected#",$received)){
				$message  = "Bad response from SMTP server".PHP_EOL;
				$message .= "Sent : $sent".PHP_EOL;
				$message .= "Expected : $expected".PHP_EOL;
				$message .= "Received : $received".PHP_EOL;
				
				fclose(self::$client_sockets_pool[$socket_id]);
				unset(self::$client_sockets_pool[$socket_id]);

				throw new MMailerProtocolException($message);
			}
		}
		return $received;
 	}
	
	
 	private static function sendData($socket_id, $message){
 		if(! fwrite(self::$client_sockets_pool[$socket_id], "$message\r\n")){
			
			fclose(self::$client_sockets_pool[$socket_id]);
			unset(self::$client_sockets_pool[$socket_id]);
			
			throw new MMailerSocketException("Impossible d'envoyer le message $message");
		}
 	}
	
	private static function hmac($data, $key){
        if (function_exists('hash_hmac')) {
            return hash_hmac('md5', $data, $key);
        }

        //The following borrowed from
        //http://php.net/manual/en/function.mhash.php#27225

        //RFC 2104 HMAC implementation for php.
        //Creates an md5 HMAC.
        //Eliminates the need to install mhash to compute a HMAC
        //by Lance Rushing

        $bytelen = 64; //byte length for md5
        if (strlen($key) > $bytelen) {
            $key = pack('H*', md5($key));
        }
        $key = str_pad($key, $bytelen, chr(0x00));
        $ipad = str_pad('', $bytelen, chr(0x36));
        $opad = str_pad('', $bytelen, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack('H*', md5($k_ipad . $data)));
    }
	
	private static function log($message){	
		if(self::$debugMode){
			$backtrace = debug_backtrace();
			$trace = $backtrace[1]['function'].'('.$backtrace[0]['line'].')';
			if(isset($backtrace[2])){
				$trace = $backtrace[2]['function'].'('.$backtrace[1]['line'].')::'.$trace;
			}
			if(is_array($message)){
				error_log($trace." : ".print_r($message,true));
			}
			else{
				error_log($trace." : ".$message);
			}
		}
	}
 }
?>
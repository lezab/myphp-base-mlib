<?php
/**
 * This file is part of MyLib
 * Copyright (C) 2018-2025 Denis ELBAZ
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

namespace mlib\utils\messages;
/**
 * MMessager
 * A class to store and display messages via the php session
 * @author Denis ELBAZ
 * @version 1.0.3
 * 
 * @category view
 */
class MMessenger {
	
	static private $nb_zones = 1;
	static private $messages = null;
	
    /**
     * Saves a message that can be retrieve only once
     * 
     * @param string $type one of 'error', 'warning' or 'info' value
     * @param string $message the message to send to the view part
	 * @param string $group optional - a key to store this message for a particular context
	 * @param mixed $options what you want - additionnal informations about this message that you could retrieve later.
	 *		These informations could only be retrieve by getMessage method call.
	 *		They will be lost on displayMessages call
     */
    public static function send($type, $message, $group = 'default', $options = null){
		$_SESSION['mmessenger'][$group][] = array('type' => $type, 'message' => $message, 'options' => $options);
    }
	
	/**
     * Saves a message that can be retrieve only once
     * 
     * @param string $message the messege to send to the view part
	 * @param string $group optional - a key to store this message for a particular context
	 * @param mixed $options what you want - additionnal informations about this message that you could retrieve later.
	 *		These informations could only be retrieve by getMessage method call.
	 *		They will be lost on displayMessages call
     */
    public static function sendError($message, $group = 'default', $options = null){
		$_SESSION['mmessenger'][$group][] = array('type' => 'error', 'message' => $message, 'options' => $options);
    }
	
	/**
     * Saves a message that can be retrieve only once
     * 
     * @param string $message the messege to send to the view part
	 * @param string $group optional - a key to store this message for a particular context
	 * @param mixed $options what you want - additionnal informations about this message that you could retrieve later.
	 *		These informations could only be retrieve by getMessage method call.
	 *		They will be lost on displayMessages call
     */
    public static function sendWarning($message, $group = 'default', $options = null){
		$_SESSION['mmessenger'][$group][] = array('type' => 'warning', 'message' => $message, 'options' => $options);
    }
	
	/**
     * Saves a message that can be retrieve only once
     * 
     * @param string $message the messege to send to the view part
	 * @param string $group optional - a key to store this message for a particular context
	 * @param mixed $options what you want - additionnal informations about this message that you could retrieve later.
	 *		These informations could only be retrieve by getMessage method call.
	 *		They will be lost on displayMessages call
     */
    public static function sendInfo($message, $group = 'default', $options = null){
		$_SESSION['mmessenger'][$group][] = array('type' => 'info', 'message' => $message, 'options' => $options);
    }
    
	/**
     * Saves a message that can be retrieve only once
     * 
     * @param string $message the messege to send to the view part
	 * @param string $group optional - a key to store this message for a particular context
	 * @param mixed $options what you want - additionnal informations about this message that you could retrieve later.
	 *		These informations could only be retrieve by getMessage method call.
	 *		They will be lost on displayMessages call
     */
    public static function sendSuccess($message, $group = 'default', $options = null){
		$_SESSION['mmessenger'][$group][] = array('type' => 'success', 'message' => $message, 'options' => $options);
    }
    /**
     * Returns messages saved in the session
     * @param string $group
     * @return array An array of messages. Each is an array with 'type', 'message' and 'options' keys.
     */
    public static function getMessages($group = 'default') {
		if(! isset($_SESSION['mmessenger'])){
			return null;
		}
		elseif(! isset($_SESSION['mmessenger'][$group])){
			return null;
		}
		$messages = $_SESSION['mmessenger'][$group];
		unset($_SESSION['mmessenger'][$group]);
		return $messages;
    }
    
    /**
     * Displays the messages
     *
     * @param string $group
     * @return string
     */
    public static function displayMessages($group = 'default'){
		$messages = self::$messages == null ? self::getMessages($group) : self::$messages;
		self::$messages = $messages;
		if($messages != null){
			if(self::$nb_zones == 1){
				// Better to hard load css here in the html flow, in case of displaying messges on an ajax load (in a modal box for example)
				echo "<style>";
				echo file_get_contents(__DIR__."/_resources/messenger.css");
				echo "</style>";
			}
			
			echo "<div id=mmessenger-zone-".self::$nb_zones." class=\"mmessenger-zone\">";
			foreach ($messages as $message) {
				echo "<div class=\"mmessenger-".$message['type']."-message\">".$message["message"]."</div>";
			}
			echo "</div>";
			echo "<script type=\"text/javascript\">\n";
			echo " var mmessenger_zone_".self::$nb_zones."_style = document.getElementById('mmessenger-zone-".self::$nb_zones."').style;";
			echo " mmessenger_zone_".self::$nb_zones."_style.opacity = 1; (function fade(){if((mmessenger_zone_".self::$nb_zones."_style.opacity-=.01) > 0.6 ) setTimeout(fade,20);})();";
			echo "</script>\n";
			self::$nb_zones++;
		}
	}
}
?>
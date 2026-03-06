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

namespace mlib\utils\logger;

class MMysqlLogger extends MLogger{
    
	private $pdo;
	
	private $table;
	private $datetimeColumn;
	private $levelColumn;
	private $messageColumns;
	private $messageColumnsCount;
	private $statement;
	
    public function __construct($level, $options){
		$this->level = $level;
		
		$db = $options['database'];
		
		$this->pdo = new \PDO("mysql:host=".$db['url'].";port=".$db['port'].";dbname=".$db['name'], $db['user'], $db['password'], array( \PDO::ATTR_PERSISTENT => true, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		$this->pdo->query("SET NAMES 'UTF8'");
		
		$this->table = $options['table'];
		$this->datetimeColumn = $options['columns']['datetime'];
		$this->levelColumn = $options['columns']['level'];
		$this->messageColumns = $options['columns']['message'];
		$this->messageColumnsCount = count($this->messageColumns);
		
		$sql = "INSERT INTO `$this->table`";
				
		$fields = array('`'.$this->datetimeColumn.'`', '`'.$this->levelColumn.'`');
		$stmt_vars = array('?', '?');
		foreach ($this->messageColumns as $field) {
			$fields[] = "`$field`";
			$stmt_vars[] = '?';
		}
		$sql .= " (".implode(', ', $fields).") VALUES (".implode(', ', $stmt_vars).")";
		try {
			$this->statement = $this->pdo->prepare($sql);
		}
		catch(\Exception $e) {
			throw new MLoggerException($e->getMessage(), 2, $e);
		}
    }
 
	
	public function log($level, $message) {
		if($level <= $this->level){
			try {
				$this->statement->bindValue(1, date('Y-m-d H:i:s:u'), \PDO::PARAM_STR);
				$this->statement->bindValue(2, MLogger::LABELS[$level], \PDO::PARAM_STR);
				if($this->messageColumnsCount == 1){
					$this->statement->bindValue(3, self::stringify($message), \PDO::PARAM_STR);
				}
				else{
					if((! is_array($message)) || (count($message) != $this->messageColumnsCount)){
						throw new MLoggerException("MMysqlLogger : message should be an array with same size options[columns][message] size in config file", 3);
					}
					foreach($message as $i => $messagePart){
						$this->statement->bindValue($i + 3, self::stringify($messagePart), \PDO::PARAM_STR);
					}
				}
				$this->statement->execute();
			}
			catch(\Exception $e) {
				throw new MLoggerException($e->getMessage(), 3, $e);
			}
		}
    }
}
?>
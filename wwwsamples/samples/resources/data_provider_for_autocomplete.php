<?php
include_once("../../autoload.php");

$noms = array('Dupont', 'Durand', 'Martin', 'Lefevre', 'Dupond', 'Doe', 'Charles', 'Henry', 'Louis', 'Philippe');
$prenoms = array('Jean', 'Michel', 'Luc', 'Matthieu', 'Paul', 'Judas', 'Jacques', 'Pierre', 'Thomas', 'Philippe');

$search = isset($_GET['name']) && preg_match('/^[A-Za-z]+$/', $_GET['name']) ? $_GET['name'] : null;

$result = array();
if($search){
	for($i = 0; $i < count($noms); $i++){
		
		if(mlib\utils\string\MString::startsWith($noms[$i], $search, true)){
			$result[] = array('id' => $i, 'displayname' => $prenoms[$i].' '.$noms[$i]);
		}
	}
}
echo json_encode($result);
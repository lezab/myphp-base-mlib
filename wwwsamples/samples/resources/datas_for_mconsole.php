<?php

$noms = array('Dupont', 'Durand', 'Martin', 'Lefevre', 'Dupond', 'Doe', 'Charles', 'Henry', 'Louis', 'Philippe');
$prenoms = array('Jean', 'Michel', 'Luc', 'Matthieu', 'Paul', 'Judas', 'Jacques', 'Pierre', 'Thomas', 'Philippe');
$mails = array('jean', 'michel', 'luc', 'matthieu', 'paul', 'judas', 'jacques', 'pierre', 'thomas', 'philippe.philippe.quiaunelongueadressemail');
$dates = array('01/01/2000', '01/01/2001', '01/01/2002', '01/01/2003', '01/07/2000', '31/07/2000', '15/07/2000', '15/06/2000', '01/06/2000', '30/06/2000');

$datas = array();
// Les valeurs pour chaque colonne (on peut les mettre dans le désordre)
// Pour les types link et mailto, la valeur est un tableau
for($i = 0; $i < 10; $i++){
	$datas[$i] = array("nom" => $noms[$i],
					 "prenom" => $prenoms[$i],
					 "email" => array($mails[$i]."@mycorp.com"),    // array(link[, link_name])
					 "delete" => true,
					 "modif" => true,
					 "website" => array('http://www.mycorp.com', 'http://www.mycorp.com', false),   // array(link, link_name, is_external_link)
					 "add_date" => array($dates[$i], join('', array_reverse(explode('/', $dates[$i])))), // for sorting purpose
					 "console_id" => $i,            // obligatoire
					 "console_display" => "$prenoms[$i] $noms[$i]",  // obligatoire si une colonne de type "delete" ou "details" a été définie
					 // ci-dessous, obligatoire si une colonne de type "details" a été définie sans préciser d'url
					 // c'est un tableau de tableau, chaque tableau définissant un groupe de valeurs
					 // pour chaque pair clé/valeur, valeur peut être un tableau
					 "console_details" => array(array("Nom" => $noms[$i],
													  "Prenom" => $prenoms[$i]),
												array("Mail" => array($mails[$i]."@mycorp.com", "all@mycorp.com")))
					);
}
?>
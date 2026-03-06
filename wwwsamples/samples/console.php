<div align=center>
<?php
include(__DIR__."/resources/datas_for_mconsole.php"); //fournit la variable $datas


if(isset($_GET['sort'])){ // voir colonne sortable avec sortable_method = server
	if($_GET['sort'] == 'nom'){
		$noms = array();
		foreach($datas as $data){
			$noms[] = $data['nom'];
		}
		
		$order = isset($_SESSION['nom_order']) && $_SESSION['nom_order'] == 'SORT_ASC' ? 'SORT_DESC' : 'SORT_ASC';
		$_SESSION['nom_order'] = $order;
		
		error_log($order);
		array_multisort($datas, constant($order), SORT_REGULAR, $noms);
		//array_multisort($datas, SORT_ASC, SORT_NUMERIC, $noms);
	}
}

// Le fichier de configuration de la console est un fichier où l'on décrit les colonnes du tableau de bord
// (Voir le fichier de config dans samples/resources pour plus d'infos)
$console = mlib\ui\console\MConsoleFactory::createFromFile(__DIR__."/resources/console.conf");
$console->registerAssetsPath("mlib/assets/mlib/MConsole"); // devra être modifié en fonction des emplacements où l'on met les assets.

// Enfin on fournit les données au tableau de bord
$console->setDatas($datas);
$console->setTitle("Utilisateurs");
// Affichage de la console
$console->display();
?>
</div>
<!-- SPLIT -->
<br><br>
Code de "resources/console.conf" : <br>
<div class="code">
<?php
	highlight_file(__DIR__."/resources/console.conf");
?>
</div>
<br><br>
Code de "resources/consoles_datas.php" : <br>
<div class="code">
<?php
	highlight_file(__DIR__."/resources/datas_for_mconsole.php");
?>
</div>
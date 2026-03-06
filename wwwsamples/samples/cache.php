<?php
// Exemple d'utilisation de MCache
// On va mettre en cache un objet assez important (qui met du temps à être instancié), pour juger de l'utilité du cache.
// Ici un objet de type MConsole (voir l'exemple MConsole pour plus d'explications).

$time00 = microtime(true);
$cache = new mlib\utils\cache\MCache(__DIR__."/tmp/");	// On indique un répertoire
										// Chaque variable mise en cache, le sera dans un fichier différent.

if(! $console = $cache->get('console', 5)){ // On indique la durée de vie en secondes du cache
											// Ici si le cache a plus de 5 secondes, on considère qu'il est expiré.
											// il faut refaire
	
	$time10 = microtime(true);
	
	/*--------------------------------------------------------------------*/
	/* Instanciation d'un objet                                           */
	/*--------------------------------------------------------------------*/
	// Plus l'objet instancié est lourd à créer plus le cache est efficace
	// (par exemple si les donnée ci-dessous étaient récupérées dans
	// une base de données)
	include(__DIR__."/resources/console_datas.php");
	$console = mlib\ui\console\MConsoleFactory::createFromFile(__DIR__."/resources/console.conf");
	$console->registerAssetsPath("mlib/assets/mlib/MConsole");
	$console->setDatas($datas);
	/*--------------------------------------------------------------------*/
	/* Fin Instanciation d'un objet                                       */
	/*--------------------------------------------------------------------*/
	
	$time11 = microtime(true);
	
	$elapsed_time1 = ($time11 - $time10)*1000;
	echo "Temps instanciation : $elapsed_time1 millisecondes<br>";
	
	// Mise en cache
	$cache->set('console', $console);
	echo "L'objet a été mis en cache";
}
else{
	$time01 = microtime(true);
	$elapsed_time0 = ($time01 - $time00)*1000;
	echo "Temps lecture cache : $elapsed_time0 millisecondes<br>";
}
?>
<span style="float:right;"><a href="index.php?sample=cache">Rafraichir</a> (rafraichissez avant 5 secondes pour voir la différence)</span>
<br>
<br>
<i>!!! le serveur web doit avoir les droits d'écriture sur le répertoire samples/tmp/ pour que MCache fonctionne !!!</i>
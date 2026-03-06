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

include_once("autoload.php");
session_start();

$samples = array(
	'config' => 'MConfig : fichiers de configuration',
	'form' => 'MForm : une classe pour faire des formulaires',
	'validator' => "MFormValidator : vérifier simplement tout ce qui est envoyé par un formulaire",
	'console' => 'MConsole : des tableaux de bord',
	'autocomplete' => 'MAutoComplete : pour afficher un champ de recherche dynamique',
	'tree' => 'MTree : pour afficher un arbre',
	'ajax' => 'MAjaxZone : pour afficher une zone asynchrone qui peut être rafraichie',
	'tooltip' => "MTooltip : pour faire un peu mieux que l'attribut 'title'",
	'captcha' => 'MCaptcha : pour mettre un captcha dans un formulaire',
	'cache' => 'MCache : une classe pour faire du cache serveur simplement',
	'encode_decode' => 'MEncoderDecoder : une classe pour encoder/decoder facilement des chaines de caractères'
);

$title = '';

if(isset($_GET['sample'])){
	$sample = $_GET['sample'];
	if(isset($samples[$sample])){
		$title = $samples[$sample];
	}
}
?>
<!doctype html>
<html>
	<head>
		<title>MLIB</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="favicon.png" rel="icon" type="images/png" />
		<style>
			* {
				box-sizing: border-box;
			}
			html{
				font-family: arial, verdana, sans-serif;
			}
			body{
				background-color: #aaa;
				margin: 0;
			}
			.main{
				background-color: #ffffff;
				width: 1200px;
				margin: 0 auto;
			}
			header{
				height:70px;
				text-align:center;
				background-color: #CCC;
				color: #FF6F00;
				font-size: 25px;
				padding-top: 20px;
			}
			#sample_title{
				text-align:center;
				background-color: #d9e8ff;
				color: #444;
				font-size: 22px;
				padding: 18px;
				margin-bottom: 15px;
					
			}
			#content{
				padding : 15px;
				text-align: left;
				font-size: 14px;
			}
			footer{
				text-align:center;
				background-color: #e0e0e0;
				font-size: 11px;
				padding: 5px;
			}
			a{
				color: #0088cc;
				text-decoration: none;
			}
			a:hover, a:focus{
				color: #005580;
				text-decoration: underline;
			}
			.btn_back{
				color: #fff;
				background-color: #ec971f;
				border-color: #d58512;
				text-decoration: none;
				font-weight: normal;
				text-align: center;
				vertical-align: middle;
				padding: 6px 12px;
				font-size: 14px;
				line-height: 1.42857143;
				border-radius: 4px;
			}
			.code{
				border : 1px solid #ccc;
				border-radius: 5px;
				background-color: #e0e0e0;
				padding-left:10px;
				overflow-x: scroll;
			}
			.sample{
				border : 1px solid #ccc;
				border-radius: 5px;
				background-color: #f9f9f9;
				padding:10px;
			}
			.sample > .code{
				background-color: #f0f0f0;
			}
			
			@media (max-width: 1200px) {
				.main {
					width: 100%;
				}
			}
		</style>
	</head>
	<body align="center">
		
		<div class=main>
			<header>
				<div>MLIB (v<?= mlib\MLib::getVersion();?>)</div>
			</header>
			<div id="content">
				<?php
				if(isset($sample)){
					?>
					<div id=sample_title><?=$title?></div>
					<div align=right><a class="btn_back" href=index.php>Retour</a></div>					
					<br><br>
					Démo :<br>
					<div class="sample">
					<?php
					include("samples/$sample.php");
					?>
					</div><!-- div class=sample (Demo) -->
					<br><br>
					Code source :<br>
					<div class="code">
						<?php
						$source_code = highlight_file("samples/$sample.php", true);
						$pos = strpos($source_code, "&lt;!-- SPLIT --&gt;");
						if($pos){
							echo substr($source_code, 0, $pos);
							echo "</code>";
						}
						else{
							echo $source_code;
						}
						?>
					</div><!-- div class=code (Code source) -->
					<br><br>
					<div align=right><a class="btn_back" href=index.php>Retour</a></div>
					<?php
				}
				else{
					echo "<br><br><ul>\n";
					$scripts = array_keys($samples);
					foreach($scripts as $script){
						echo "<li>";
						$sample = $script;
						$label = $samples[$sample];
						echo "<a href=\"index.php?sample=$sample\">$label</a>";
						echo "</li>";
					}
					echo "</ul>\n";
					echo "<br><br>";
					echo "<i>Toutes les classes de mlib ne font pas l'objet d'un exemple. Voir ces classes directement dans le code source</i>";
				}
				?>
			</div>
			<footer>
				Copyright &copy; 2008 - <?=date("Y")?> - mlib
			</footer>
		</div>
	</body>
</html>

<div align="center">
	<?php
	$form = mlib\ui\form\MFormFactory::createFromFile(__DIR__."/resources/form.conf");
	$form->registerAssetsPath("mlib/assets/mlib/MForm/");

	$form->setError("Un champ a été mal rempli");
	$form->setErrorOn("anInput3"); //ne s'applique qu'en mode edition
	
	// Pour le mode edition
	$vars = array("anInput2" => "une valeur non modifiable", 'fichier' => "onefile.pdf");
	
	$form->display($vars); //le fait de passer un paramètre indique qu'on est en mode edition
	?>
</div>
<!-- SPLIT -->
<br><br>
Code de "resources/form.conf" : <br>
<div class="code">
<?php
	highlight_file(__DIR__."/resources/form.conf");
?>
</div>
<?php
$array_config = array('test_section' => array("param1" => 22, "param2" => "testvalue"), "test_param" => "33");

// Voir le fichier de config dans samples/resources pour plus d'infos
$config = new mlib\utils\config\MConfig(__DIR__."/resources/sample_config.conf", $array_config);
echo "<pre>".htmlspecialchars($config->toString())."</pre>";
echo "<br><br>";

$sectionConfig = $config->getConfig("section1");
$type = $sectionConfig->getParameter('type');
echo "Type is : $type<br><br>";


$parameter = $config->getParameter("parametreBooleen");
echo "parametreBooleen a pour valeur ".($parameter ? "true" : "false");
echo "<br><br>";

$parameter = $config->getParameter("parametreBooleenFalse");
echo "parametreBooleenFalse a pour valeur ".($parameter ? "true" : "false");
echo "<br><br>";
?>
<!-- SPLIT -->
<br><br>
Code de "resources/testconfig.conf" : <br>
<div class="code">
<?php
	highlight_file(__DIR__."/resources/sample_config.conf");
?>
</div>
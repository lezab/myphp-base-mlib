<?php
mlib\ui\ajaxzone\MAjaxZone::registerAssetsPath("mlib/assets/mlib/MAjaxZone/");

mlib\ui\ajaxzone\MAjaxZone::display(
	"Cliquer pour afficher la zone",
	"samples/resources/content_for_majaxzone.php",
	array(
		'hideonclick' => true,
		'visible_button_label' => 'Masquer la zone'
	)
);

mlib\ui\ajaxzone\MAjaxZone::display(
	"Cliquer pour afficher la zone",
	"samples/resources/content_for_majaxzone.php",
	array(
		'hideonclick' => false,
		'visible_button_label' => 'Rafraichir la zone'
	)
);

mlib\ui\ajaxzone\MAjaxZone::display(
	"Cliquer pour afficher la zone",
	"samples/resources/content_for_majaxzone.php",
	array(
		'button_align' => 'right',
		'visible_button_label' => 'Rafraichir la zone'
	)
);

mlib\ui\ajaxzone\MAjaxZone::display(
	"Cliquer pour afficher la zone",
	"samples/resources/content_for_majaxzone.php",
	array(
		'align' => 'center',
		'button_color' => '#0088DD',
		'visible_button_label' => 'Rafraichir la zone <i>icone</i>'
	)
);
?>
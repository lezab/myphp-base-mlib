<input type='text' id='selected_data' />
<br><br>
<script>
	var onSelect = function(suggestion) {
		document.getElementById('selected_data').value = suggestion['id'];
		return suggestion['displayname'];
	};
	var onValidate = function() {
		var id = document.getElementById('selected_data').value;
		alert("Ici l'action qui est lancée quand on clique sur le bouton");
		// On remplacera l'alert par un code comme ci-dessous par exemple
		/* 
		var url = 'sample_process_url?id='+id;
		window.location = url;
		*/
	};
	var render = function(suggestion){
		return '<div><strong>'+suggestion['displayname']+'</strong> ('+suggestion['id']+')</div>';
	};
</script>
<?php
mlib\ui\autocomplete\MAutoComplete::registerAssetsPath("mlib/assets/mlib/MAutoComplete/");
mlib\ui\autocomplete\MAutoComplete::display(
	'search_field',
	'samples/resources/data_provider_for_autocomplete.php?name=',
	array(
		'onSelect' => 'onSelect',
		'onValidate' => 'onValidate',
		'renderResult' => 'render'
	),
	array(
		'button' => true,
		'button_label' => 'Appliquer', // on peut mettre ici du code html pour obtenir une icone
		'placeholder' => "Rechercher ...",
		'empty_message' => "Aucune personne ne correspond"
	)
);
?>
<br><br>
Un autre exemple minimaliste sans bouton:
<br><br>
<script>
	var onSelect2 = function(suggestion) {
		alert('Selected : ' + suggestion['id'] + '(' +suggestion['displayname'] + ')');
		return suggestion['displayname'];
	};
</script>
<?php
mlib\ui\autocomplete\MAutoComplete::display(
	'search_field_2',
	'samples/resources/data_provider_for_autocomplete.php?name=',
	array(
		'onSelect' => 'onSelect2'
	),
	array(
		'width' => '300px'
	)
);
?>
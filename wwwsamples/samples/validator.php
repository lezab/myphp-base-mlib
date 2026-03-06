<?php
if(isset($_POST['validated'])){
	$validator = mlib\utils\validator\MFormValidatorFactory::createFromFile(__DIR__.'/resources/validator.conf');
	if(! $validator->run()){
		echo "Champ en erreur : ".$validator->getErrorField().'<br><br>';
		echo "Message de l'erreur : ".$validator->getErrorMessage().'<br><br>';
	}
}
?>
<form method="POST" action="index.php?sample=validator">
	<label for="testtext">TEXT</label>
	<input name="testtext" type="text" /><br>
	<label for="testselect">SELECT</label>
	<select name="testselect">
		<option></option>
		<option value="1">Un</option>
		<option value="2">Deux</option>
	</select><br>
	<label for="testnumber">NUMBER</label>
	<input name="testnumber" type="text" /><br>
	<label for="testdate">DATE</label>
	<input name="testdate" type="text" placeholder="jj/mm/aaaa hh:mm" /><br>
	<input type="submit" name=validated value="valider" />
</form>
<!-- SPLIT -->
<br><br>
Code de "resources/validator.conf" : <br>
<div class="code">
<?php
	highlight_file(__DIR__."/resources/validator.conf");
?>
</div>
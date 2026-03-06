<?php
$stringToEncode = "une chaine de test";
echo "<pre>";
echo "Chaine à encoder : \"$stringToEncode\"<br>";
echo "--------------------------------------- <br><br>";

echo "CRYPTAGE SYMETRIQUE :<br>";
echo "---------------------<br>";
$coder = new mlib\utils\crypto\MEncoderDecoder("une clé de cryptage quelconque");
$encodedString = $coder->encode($stringToEncode);

echo "Chaine cryptée : $encodedString <br>";

$decodedString = $coder->decode($encodedString);

echo "Chaine décryptée : $decodedString ";

if($decodedString == $stringToEncode){
	echo " : SUCCESS <br><br>";
}
else{
	echo " : FAILED <br><br>";
}

echo "CRYPTAGE ASYMETRIQUE :<br>";
echo "----------------------<br>";

$publicKey = file_get_contents(__DIR__.'/resources/public.key');
$privateKey = file_get_contents(__DIR__.'/resources/private.key');

echo "$publicKey<br><br>";
echo "$privateKey<br><br>";


$coder = new mlib\utils\crypto\MEncoderDecoder($publicKey, false);
$encodedString = $coder->encode($stringToEncode);

echo "Chaine cryptée : $encodedString <br>";

$decoder = new mlib\utils\crypto\MEncoderDecoder(null, false, $privateKey);
$decodedString = $decoder->decode($encodedString);

echo "Chaine décryptée : $decodedString ";

if($decodedString == $stringToEncode){
	echo " : SUCCESS <br><br>";
}
else{
	echo " : FAILED <br><br>";
}
echo "</pre>";
?>
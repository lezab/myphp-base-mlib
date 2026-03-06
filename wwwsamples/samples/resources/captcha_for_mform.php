<?php
include("../../mlib/mlib/ui/captcha/MCaptcha.php");
$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Certains caractères ont été enlevés car ils prêtent à confusion
$code = '';
for ($i=0; $i<5; $i++) {
	$code .= $chars[mt_rand( 0, strlen($chars)-1 )];
}
// Stockage du code dans la session
// devra être récupéré et vérifié dans le controlleur du formulaire qui aura appelé ce captcha
$_SESSION['captcha'] = $code;

$captcha = new mlib\ui\captcha\MCaptcha($code);
header("Content-type: image/png");
header('Content-disposition: inline; filename=captcha.png');
$captcha->display();
?>
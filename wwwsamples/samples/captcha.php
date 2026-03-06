<script language="javascript">
	function renewCaptcha(id_img, url) {
		var image = document.getElementById(id_img);
		image.setAttribute("src", url+'?'+new Date().getTime()); // pour forcer le cache navigateur
	}
</script>

<img id=captchaimg src=samples/otherfiles/captcha.php /><br> <!-- VOIR LE CODE DE CAPTCHA.PHP -->
<a href="javascript:renewCaptcha('captchaimg', 'samples/otherfiles/captcha.php');" />Changer</a><br>

<!-- SPLIT -->
<br><br>
Code de "samples/otherfiles/captcha.php" : <br>
<div class="code">
<?php
	highlight_file("samples/otherfiles/captcha.php");
?>
</div>
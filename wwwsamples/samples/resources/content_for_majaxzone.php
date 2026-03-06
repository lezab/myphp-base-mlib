<!DOCTYPE html>
<!--
This file is part of MyTools
Copyright (C) 2016 Denis ELBAZ <denis.elbaz at univ-perp.fr>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->

Un contenu html un peu long qui permet de voir l'effet de l'alignement du bouton par rapport à la zone ajax<br>
Avec une deuxième ligne<br>
On peut aussi y mettre une image<br>
<div align=center>
	<img src='samples/resources/image_for_majaxzone.png' title='An image' />
</div>
On y met un temps pour verifier que la page est bien rechargée : <?php echo date("H:i:s"); ?>
<br>
<br>
Enfin on y met un petit script pour verifier que ça s'éxécute bien
<script>
	confirm('un message affiché avec un script');
</script>
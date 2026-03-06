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
namespace mlib\ui\captcha;

/**
 * class MCaptcha
 * @author Denis ELBAZ
 * @version 1.0.0
 * 
 * @category view
 */
class MCaptcha {
	
	private $image = null;
	
	/**
	 * Constructor
	 * @param string $string
	 */
	public function __construct($string){

		/*
		glob() retourne un tableau répertoriant les fichiers du dossier ayant l'extension .ttf ( pas .TTF ! ).
		On peut donc ajouter autant de polices TrueType que désiré, en veillant à les renommer.
		*/
		$fonts = glob(__DIR__."/_resources/*.ttf");
		$nb_fonts = count($fonts);
		
		/*
		imagecreatefrompng() crée une nouvelle image à partir d'un fichier PNG.
		Cette nouvelle $image va être ensuite modifiée avant l'affichage.
		 */
		$image = imagecreatefrompng(__DIR__."/_resources/captcha_bg.png");
		$image = imagecrop($image, array('x' => mt_rand(0, 280), 'y' => mt_rand(0, 80), "width" => 150, "height" => 50));
		/*
		imagecolorallocate() retourne un identifiant de couleur.
		On définit les couleurs RVB qu'on va utiliser pour nos polices et on les stocke dans le tableau $colors[].
		On peut ajouter autant de couleurs qu'on veut.
		*/
		$colors=array();
		for($i=0;$i<strlen($string);$i++){
			$grey_level = mt_rand(80, 150);
			$colors[] = imagecolorallocate($image, $grey_level, $grey_level, $grey_level);
			//$colors[] = imagecolorallocate($image, mt_rand(100, 205), mt_rand(100, 205), mt_rand(100, 170));
		}

		/*
		Mise en forme de chacun des caractères et placement sur l'image.
		imagettftext(image, taille police, inclinaison, coordonnée X, coordonnée Y, couleur, police, texte) écrit le texte sur l'image.
		*/
		for($i=0;$i<strlen($string);$i++){				
			imagettftext($image, 28+mt_rand(-5, +2), 0 + mt_rand(-35, 35), 30*$i, 37 + mt_rand(-10, +10), $colors[$i], $fonts[mt_rand(0, $nb_fonts-1)], $string[$i]);
		}
		
		$this->image = $image;
	}
	
	
	public function display(){
		imagepng($this->image);
	}
}
?>
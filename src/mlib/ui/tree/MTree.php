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

namespace mlib\ui\tree;
/**
 * MTree
 * A class to easily display clickable trees
 * @author : Denis ELBAZ
 * @version : 1.0.0
 * 
 * @category view
 */
class MTree {

	protected $ASSETS_PATH = null;
	
	protected $tree;
	protected $labels;

	const FULL_OPENED = 'OPENED';
	
	/**
	 * Constructor
	 * @param array $tree a multidimensional array representing a tree id's. Thess ids are the ids of the elements displayed.
	 * @param array $labels an associative array where keys are ids and values are labels of tree elements.
	 */
	public function __construct($tree, $labels){
		$this->tree = $tree;
		$this->labels = $labels;
	}

	/**
	 * 
	 * @param string $path the relative path to the directory containing js, css and images
	 */
	public function registerAssetsPath($path){
		$this->ASSETS_PATH = $path;
	}
	
	/**
	 * Displays the tree
	 * @param string|false $opened_on the id on which element the tree must be eventually opened on. Constant MTree::FULL_OPENED can be used
	 */
	public function display($opened_on = false){
		echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"".$this->ASSETS_PATH."/MTree.css\" />\n";
		echo "<script type=\"text/javascript\" language=\"javascript\" src=\"".$this->ASSETS_PATH."/MTree.js\"></script>\n";
		echo "<div class=mtree>\n";
	
		if($opened_on === false){
			$this->print_tree($this->tree, $this->labels, 0);
		}
		else{
			if($opened_on === self::FULL_OPENED){
				$this->print_tree($this->tree, $this->labels, 0, 'opened');
			}
			else{
				$this->print_tree($this->tree, $this->labels, 0);
				echo "<script type=\"text/javascript\">\n";
				echo "	MTree.init('$opened_on');\n";
				echo "</script>\n";
			}
		}
		echo "</div>\n";
	}
	
	/**
	 * 
	 * @param array $tree
	 * @param array $labels
	 * @param int $depth
	 * @param string $default
	 */
	private function print_tree($tree, $labels, $depth, $default = 'closed'){
		$default_style = $default == 'closed' ? "style=\"display:none;\"" : '';
		if(count($tree) != 0){
			$keys = array_keys($tree);
	
			if($depth > 1){
				echo "<ul id=\"level_$depth\" class=\"level_$depth\" $default_style>\n";
			}
			else{
				echo "<ul id=\"level_$depth\" class=\"level_$depth\">\n";
			}
			for($j=0; $j<count($keys); $j++){
				if($depth > 0){
					if(count($tree[$keys[$j]]) != 0){
						if($j == count($keys) - 1){
							echo "	<li class=\"last\"><span class=$default onclick=\"MTree.expandHide(this.parentNode)\">&nbsp;&nbsp;</span><span id=".$keys[$j].">".$labels[$keys[$j]]."</span>";
							$this->print_tree($tree[$keys[$j]], $labels, $depth + 1, $default);
						}
						else{
							echo "	<li><span class=$default onclick=\"MTree.expandHide(this.parentNode)\">&nbsp;&nbsp;</span><span id=".$keys[$j].">".$labels[$keys[$j]]."</span>";
							$this->print_tree($tree[$keys[$j]], $labels, $depth + 1, $default);
						}
						echo "</li>\n";
					}
					elseif($j == count($keys) - 1){
						echo "	<li class=\"last\"><span id=".$keys[$j].">".$labels[$keys[$j]]."</span></li>\n";
					}
					else{
						echo "	<li class=\"join\"><span id=".$keys[$j].">".$labels[$keys[$j]]."</span></li>\n";
					}
				}
				else{
					echo "	<li>".$labels[$keys[$j]]."\n";
					if($j == count($keys) - 1){
						$this->print_tree($tree[$keys[$j]], $labels, $depth + 1, $default);
					}
					else{
						$this->print_tree($tree[$keys[$j]], $labels, $depth + 1, $default);
					}
					echo "	</li>";
				}
			}
			echo "</ul>\n";
		}
	}
}
?>
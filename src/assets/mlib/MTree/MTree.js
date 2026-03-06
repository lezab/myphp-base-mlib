/** 
 * This file is part of MyTools
 * Copyright (C) 2016 Denis ELBAZ
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


function MTree(){};

MTree.expandHide = function (li) {
	var node = li.firstChild;
	// parcours tous les fils pour trouver l'element UL
	while(node.nodeName != "UL"){
		node = node.nextSibling;
	}
	var span = li.firstChild;

	if(node.style.display == 'none'){
		node.style.display = 'block';
		span.className = 'opened';
	}
	else {
		node.style.display = 'none';
		span.className = 'closed';
	}
};

// Fonction pour ouvrir l'arbre à un endroit donné
MTree.expandMultiple = function (id) {
	var ul = document.getElementById(id);
	while ( ul.nodeName != "UL" ) {
		ul = ul.parentNode;
	}
	// affiche la branche et les branches supérieures
	while((ul.nodeName=="UL") && (ul.id != 'level_1')) {
		MTree.expandHide(ul.parentNode); //le li qui contient l'ul
		ul = ul.parentNode.parentNode; //l'ul au dessus
	}
};

// Fonction qui ferme l'arbre (ne restent visibles que les branches de niveau 1)
MTree.collapseMultiple = function() {
	// récupere l'arbre de niveau 1
	level_1 = document.getElementById('level_1');
	// recupere toutes les branches
	tab_ul = level_1.getElementsByTagName("ul");
	nb = tab_ul.length;
	// cache tous les menus
	for(var i=0; i<nb; i++) {
		MTree.expandHide(tab_ul[i].parentNode);
	}
};

// Fonction qui initialise l'arborescence
MTree.init = function(branch) {
	MTree.expandMultiple(branch);
};
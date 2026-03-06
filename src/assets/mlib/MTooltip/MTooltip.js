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


function MTooltip(){};


MTooltip.showtooltip = function(tid) {
	var tooltip = document.getElementById(tid);

	if(tooltip){
		var triggerElement = tooltip.previousElementSibling;
		
		// Récupérer les dimensions et positions
		var tooltipRect = tooltip.getBoundingClientRect();
		var viewportWidth = document.documentElement.clientWidth;
		var viewportHeight = document.documentElement.clientHeight;

		var right = tooltipRect.right;
		var bottom = tooltipRect.bottom;

		if(right > viewportWidth || bottom > viewportHeight) {
			var html = document.getElementsByTagName('HTML')[0];
			var htmlRect = html.getBoundingClientRect();
			var htmlOffsetWidth = html.offsetWidth;
			var htmlOffsetHeight = html.offsetHeight;
			var coefW = htmlOffsetWidth / htmlRect.width;
			var coefH = htmlOffsetHeight / htmlRect.height;

			if(right > viewportWidth) {
				// Essayer de le caler à gauche par rapport à l'élément
				var decalage_left = (viewportWidth - right - 5) * coefW;
				tooltip.style.marginLeft = decalage_left + 'px';
			}
			if (bottom > viewportHeight) {
				// Le placer au-dessus de l'élément
				var triggerRect = triggerElement.getBoundingClientRect();
				var decalage_top = (- triggerRect.height - tooltipRect.height - 15) * coefH;
				tooltip.style.marginTop = decalage_top + 'px';
			}
		}

		tooltip.style.visibility = "visible";
	}
};

MTooltip.hidetooltip = function(tid) {
	var tooltip = document.getElementById(tid);
	if (tooltip) {
		// Si on a scrollé, il vaut mieux retrouver un comportement normal
		tooltip.style.marginTop = '';
		tooltip.style.marginLeft = '';
		tooltip.style.visibility = "hidden";
	}
};
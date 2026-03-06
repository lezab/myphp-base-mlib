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


/*******************************************************************************/
/** MAJAX ZONE                                                                 */
/*******************************************************************************/
function MAjaxZone(){};

MAjaxZone.getRequestObject = function(){
	var xhr_object;
	if(window.XMLHttpRequest){ // Firefox 
		xhr_object = new XMLHttpRequest();
		return xhr_object;
	}
	else if(window.ActiveXObject){ // Internet Explorer
		xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
		return xhr_object;
	}
	else { // XMLHttpRequest non supporté par le navigateur 
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		return false; 
	}
};

MAjaxZone.refresh = function(id, url, button = null){
	var div = document.getElementById(id);
	var requestObject = MAjaxZone.getRequestObject();

	if(requestObject){
		requestObject.onreadystatechange = function() {
			if(requestObject.readyState == 4){
				var received = requestObject.responseText;
				div.innerHTML = received;
				div.style.display = 'block';
				if(button !== null){
					button.innerHTML = button.getAttribute('label2');
				}
				var scripts = div.getElementsByTagName("script");
				var geval = eval; // workaround for local vars declared in eval could be accessible in global scope
				for (var i=0; i<scripts.length; i++) {
					geval(scripts[i].innerHTML);
				}
			}
		};
		requestObject.open("GET", url, true);
        requestObject.send();
	}
};

MAjaxZone.showhide = function(id, url, button){
	var div = document.getElementById(id);
	if(div.style.display == 'block'){
		div.style.display = 'none';
		button.innerHTML = button.getAttribute('label1');
	}
	else{
		var requestObject = MAjaxZone.getRequestObject();

		if(requestObject){
			requestObject.onreadystatechange = function() {
				if(requestObject.readyState == 4){
					var received = requestObject.responseText;
					div.innerHTML = received;
					div.style.display = 'block';
					button.innerHTML = button.getAttribute('label2');
					var scripts = div.getElementsByTagName("script");
					var geval = eval; // workaround for local vars declared in eval could be accessible in global scope
					for (var i=0; i<scripts.length; i++) {
						geval(scripts[i].innerHTML);
					}
				}
			};
			requestObject.open("GET", url, true);
			requestObject.send();
		}
	}
};
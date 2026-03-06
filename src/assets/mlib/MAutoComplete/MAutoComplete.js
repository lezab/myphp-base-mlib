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
/** MAutoComplete                                                              */
/*******************************************************************************/
export default class MAutoComplete {

	static getRequestObject(){
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
	}
	
	static bind(input_id, search_url, min_chars, onSelectFunction, renderFunction, emptyResponseMessage){
		var input = document.getElementById(input_id);	
		input.addEventListener('input', function(){MAutoComplete.search(input_id, search_url, min_chars, onSelectFunction, renderFunction, emptyResponseMessage);});
		//input.addEventListener('input', MAutoComplete.search(input_id, search_url, min_chars, onSelectFunction, renderFunction, emptyResponseMessage));
	}

	static search(input_id, search_url, min_chars, onSelectFunction, renderFunction, emptyResponseMessage){
		var input = document.getElementById(input_id);
		var value = input.value;
		if(value.length >= min_chars){
			var requestObject = MAutoComplete.getRequestObject();
			if(requestObject){
				requestObject.onreadystatechange = function() {
					if(requestObject.readyState === 4){
						var received = JSON.parse(requestObject.responseText);
						MAutoComplete.showSuggestions(input, received, onSelectFunction, renderFunction, emptyResponseMessage);
					}
				};
				requestObject.open("GET", search_url+value, true);
				requestObject.send();
			}
		}
		else{
			MAutoComplete.hideSuggestions(input);
		}
	}
	
	static showSuggestions(input, suggestions, onSelectFunction, renderFunction, emptyResponseMessage) {
		var renderCallback;
		if(renderFunction !== null){
			renderCallback = renderFunction;
		}
		else{
			renderCallback = MAutoComplete.defaultRender;
		}
		
		var listContainer = input.parentNode.nextSibling || null;
        if(listContainer === null) {
			var wrapper = input.parentNode.parentNode;
			listContainer = document.createElement('ul');
			listContainer.setAttribute('class', 'mautocomplete-suggestions');
            wrapper.appendChild(listContainer);
					
			document.addEventListener('click', function(event) {
				if (! listContainer.contains(event.target)) {
					MAutoComplete.hideSuggestions(input);
				}
			});
        }
		else{
			listContainer.style.display = 'block';
		}

		if (suggestions === undefined || suggestions.length === 0) {
			listContainer.innerHTML = "<li>"+emptyResponseMessage+"</li>";
		}
		else{
			listContainer.innerHTML = "";
			
			var choice;
			suggestions.forEach(function (value) {
				choice = document.createElement('li');
				choice.innerHTML = renderCallback(value);
				choice.addEventListener(
						'click',
						function(){
							var input_inner = onSelectFunction(value);
							input.value = input_inner;
							MAutoComplete.hideSuggestions(input);
						}
				);
				listContainer.appendChild(choice);
			});
			MAutoComplete.addKeybordNavigation(input, listContainer);
		}
    }
	
	static highlightFocusedOption(listContainer, indexOfCurrentOption) {
        // Allow for going over and under in list
		var options = listContainer.children;
        var lengthOfOptions = options.length;
        if (indexOfCurrentOption >= lengthOfOptions) {
            indexOfCurrentOption = 0;
        } else if (indexOfCurrentOption < 0) {
            indexOfCurrentOption = lengthOfOptions - 1;
        }
		var active = listContainer.querySelector('.active');
		if(active !== null){
			active.classList.remove('active');
		}
        options[indexOfCurrentOption].classList.toggle('active');
        return indexOfCurrentOption;
    }
	static addKeybordNavigation(input, listContainer) {
        var indexOfCurrentHighlightedItem = -1;
        input.addEventListener('keydown', function (e) {
            var options = listContainer.children;
            var keycode = e.keyCode;
            if (keycode === 40) { // upKey
                indexOfCurrentHighlightedItem = MAutoComplete.highlightFocusedOption(listContainer, indexOfCurrentHighlightedItem += 1);
            } else if (keycode === 38) { // Down Key
                indexOfCurrentHighlightedItem = MAutoComplete.highlightFocusedOption(listContainer, indexOfCurrentHighlightedItem -= 1);
            } else if (keycode === 13) { // Enter Key
                indexOfCurrentHighlightedItem >= 0 && options.length > 0 && options[indexOfCurrentHighlightedItem].click();
            }
        });
    }
	
	static hideSuggestions(input) {
		var listContainer = input.parentNode.nextSibling || null;
        if(listContainer !== null) {
			listContainer.style.display = 'none';
		}
	}
	
	static defaultRender(suggestion){
		return "--> "+suggestion['id'];
	}
}
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
function MConsole(){
		
	this.details_window = null;
	this.details_window_title = "Détails de l'entrée";
	this.details_window_width = 450;
	this.details_window_height = 400;
	this.details_window_url = null;
	
	this.printable_window = null;
	this.printable_cols = null;
	
	this.exportable_cols = null;
	
	this.delete_url = null;
	this.expand_url = null;
	
	this.name = 'mconsole';
	this.title = null;
	this.datas = null;
	
	this.orders = new Array();
	
	this.setDetailsWindowTitle = function(title) {
		this.details_window_title = title;
	};
	
	this.setDetailsWindowDimensions = function(width, height) {
		this.details_window_width = width;
		this.details_window_height = height;
	};
	
	this.setDetailsWindowUrl = function(url) {
		if(url.indexOf("?") >= 0){
			url = url + "&";
		}
		else{
			url = url + "?";
		}
		this.details_window_url = url;
	};
	
	this.setPrintableColumns = function(cols) {
		this.printable_cols = cols;
	};
	
	this.setExportableColumns = function(cols) {
		this.exportable_cols = cols;
	};
	
	this.setDeleteUrl = function(url) {
		if(url.indexOf("?") >= 0){
			url = url + "&";
		}
		else{
			url = url + "?";
		}
		this.delete_url = url;
	};
	
	this.setExpandUrl = function(url) {
		if(url.indexOf("?") >= 0){
			url = url + "&";
		}
		else{
			url = url + "?";
		}
		this.expand_url = url;
	};
	
	this.setTitle = function(title) {
		this.title = title;
	};
	
	this.setName = function(name) {
		this.name = name;
	};
	
	this.setDatas = function(datas) {
		this.datas = datas;
	};
	
	
	this.details = function(id){
		
		var useClientMethod = true;
		if(this.details_window_url !== null){
			useClientMethod = false;
		}
		if(useClientMethod){
		   var localElement = this.datas[id];
		   var details_content = "     <div align=center>";
		   details_content += "<table width=95% class=details_infos>";
		   details_content += "<tr><td colspan=2 align=center><b>" + localElement['console_display'] + "</b><hr></td></tr>";
		   var localElementDatas = localElement['console_details'];
		   for(index in localElementDatas){
			   var groupOfdatas = localElementDatas[index];
			   for(libelle in groupOfdatas){
				   if(groupOfdatas[libelle] instanceof Array){
					   details_content += "<tr><td align=right width=40%>" + libelle + "&nbsp;:&nbsp;</td><td>";
						for(var i=0; i<groupOfdatas[libelle].length; i++){
							details_content += groupOfdatas[libelle][i];
							details_content += "<br>";
						}
						details_content += "</td></tr>";
					}
					else{
						details_content += "<tr><td align=right width=40%>" + libelle + "&nbsp;:&nbsp;</td><td>" + groupOfdatas[libelle] + "</td></tr>";
					}
			   }
			   details_content += "<tr><td colspan=2><hr></td></tr>";
		   }
		   details_content += "</table>";
		   details_content += "     </div>";

		   MConsoleDetailsBox.show(this.details_window_title, details_content, false, this.details_window_width,this.details_window_height);
	   }
	   else{
			var url = this.details_window_url;
			
			MConsoleDetailsBox.show(this.details_window_title, url + "id=" + id, true, this.details_window_width,this.details_window_height);
	   }
	};
	
	this.expandCollapse = function(rowId, id){
		var url = this.expand_url;
		var imgmore = document.getElementById(rowId+"_img_more");
		var imgless = document.getElementById(rowId+"_img_less");
		if(MConsoleExpandableRow.showHide(rowId, url + "id=" + id)){
			imgmore.style.display = 'none';
			imgless.style.display = '';
		}
		else{
			imgmore.style.display = '';
			imgless.style.display = 'none';
		}
	};
	
	this.confirmDelete = function(id){
		var localElement = this.datas[id];
		var message = 'Voulez-vous vraiment supprimer l\'entrée \"' + localElement['console_display'] +'\" ?';
		if(confirm(message)){
			var url = this.delete_url;
	    	window.location.replace(url + "id=" + id);
		}
	};

		
	this.displayPrintable = function() {
		
		var cssPath = this.style_path + "/MConsole.css";
		
		var console_table = document.getElementById(this.name);
		
		if((this.printable_window == null) || (typeof(this.printable_window) == "undefined") || (this.printable_window.closed == true)) {
			this.printable_window = window.open("", '_blank');
		}
		this.printable_window.focus();
		
		var printable = this.printable_window.document;
		
		printable.writeln("<HTML>");
		printable.writeln("  <HEAD>");
		printable.writeln("    <TITLE>Version Imprimable</TITLE>");
		printable.writeln("    <LINK REL=\"StyleSheet\" HREF=\"" + cssPath + "\" TYPE=\"text/css\">");
		printable.writeln("  </HEAD>");
		printable.writeln("  <BODY>");
		
		printable.writeln("    <div align=center style=\"width:95%;margin:auto;\">");
		if(this.title != null){
			printable.writeln("    <div class='mconsole_title'>" + this.title + "</div>");
		}
		
		printable.writeln("      <table class=mconsole_table width=100%>");		

		var indexes_to_keep = new Array();
		var headerline = (console_table.getElementsByTagName('thead')[0]).getElementsByTagName('tr')[0];
		var headerline_cols = headerline.getElementsByTagName('th');
		
		var i = 0;
		for(var index=0; index< headerline_cols.length; index++){
			for(var id in this.printable_cols){
				if(headerline_cols[index].id == this.printable_cols[id]){
					indexes_to_keep[i++] = index;
				}
			}
		}
		
		printable.writeln(MConsole.getDisplayable(headerline, false, false));	;
		for(var k=0; k<indexes_to_keep.length; k++){
			printable.writeln(MConsole.getDisplayable(headerline_cols[indexes_to_keep[k]], true, true));
		}
		printable.writeln("      </tr>");
			
		var console_lines = (console_table.getElementsByTagName('tbody')[0]).getElementsByTagName('tr');
		var console_cols;
		for(var lindex=0; lindex < console_lines.length; lindex++){
			if(console_lines[lindex].id.startsWith(this.name) && (! console_lines[lindex].id.endsWith("_expanded"))){
				printable.writeln(MConsole.getDisplayable(console_lines[lindex], false, false));
				console_cols = console_lines[lindex].getElementsByTagName('td');
				for(var k=0; k<indexes_to_keep.length; k++){
					printable.writeln(MConsole.getDisplayable(console_cols[indexes_to_keep[k]], true, true));
				}
				printable.writeln("      </tr>");
			}
		}
		
		var footer = console_table.getElementsByTagName('tfoot');
		if(typeof(footer) !== 'undefined' && typeof(footer[0]) !== 'undefined'){
			var footerline = footer[0].getElementsByTagName('tr')[0];
			printable.writeln(MConsole.getDisplayable(footerline, false, false));
			
			var footerline_cols = footerline.getElementsByTagName('td');
			
			var cell;
			var cloneCell;
			var attribute;
			var colspan;
			var i;
			var virtualstart = 0;
			var virtualend;
			var realcolspan;
			
			for(var index=0; index< footerline_cols.length; index++){
				cell = footerline_cols[index];
				colspan = 1;
				for(var attrindex = 0; attrindex < cell.attributes.length; attrindex++){
					if((cell.attributes[attrindex].nodeValue != null) && (cell.attributes[attrindex].nodeValue != '')){
						attribute = cell.attributes[attrindex].nodeName;
						if(attribute == 'colspan'){
							colspan = cell.attributes[attrindex].nodeValue;
							break;
						}
					}
				}
				virtualend = parseInt(virtualstart) + parseInt(colspan);
				realcolspan = 0;
				for(var k=0; k<indexes_to_keep.length; k++){
					if(indexes_to_keep[k] >= virtualstart && indexes_to_keep[k] < virtualend){
						realcolspan++;
					}
				}
				virtualstart = virtualend;
				if(realcolspan > 0){
					cloneCell = cell.cloneNode(true);
					cloneCell.colSpan = realcolspan;
					printable.writeln(MConsole.getDisplayable(cloneCell, true, true));
				}
			}
			printable.writeln("      </tr>");
		}
		
		printable.writeln("      </table>");
		printable.writeln("    </div>");
		printable.writeln("  </BODY>");
		printable.writeln("</HTML>");
		
		printable.close();
		this.printable_window.focus();
	};
	
	this.exportToCSV = function(filename){
		var downloadLink;
		
		var tableData = "";
		
		var console_table = document.getElementById(this.name);
		
		var indexes_to_keep = new Array();
		var headerline = (console_table.getElementsByTagName('thead')[0]).getElementsByTagName('tr')[0];
		var headerline_cols = headerline.getElementsByTagName('th');
		
		var i = 0;
		for(var index=0; index< headerline_cols.length; index++){
			for(var id in this.exportable_cols){
				if(headerline_cols[index].id == this.exportable_cols[id]){
					indexes_to_keep[i++] = index;
				}
			}
		}
		
		for(var k=0; k<indexes_to_keep.length; k++){
			tableData += "\""+headerline_cols[indexes_to_keep[k]].innerText+"\";"
		}
		tableData += "\n";
		
		var console_lines = (console_table.getElementsByTagName('tbody')[0]).getElementsByTagName('tr');
		var console_cols;
		for(var lindex=0; lindex < console_lines.length; lindex++){
			if(console_lines[lindex].id.startsWith(this.name) && (! console_lines[lindex].id.endsWith("_expanded"))){
				console_cols = console_lines[lindex].getElementsByTagName('td');
				for(var k=0; k<indexes_to_keep.length; k++){
					tableData += "\""+console_cols[indexes_to_keep[k]].innerText+"\";"
				}
				tableData += "\n";
			}
		}
		
		var footer = console_table.getElementsByTagName('tfoot');
		if(typeof(footer) !== 'undefined' && typeof(footer[0]) !== 'undefined'){
			var footerline = footer[0].getElementsByTagName('tr')[0];
			var footerline_cols = footerline.getElementsByTagName('td');
			
			var cell;
			var attribute;
			var colspan = 1;
			var align = 'left';
			var i;
			var virtualstart = 0;
			var virtualend;
			var realcolspan;
			
			for(var index=0; index< footerline_cols.length; index++){
				cell = footerline_cols[index];
				for(var attrindex = 0; attrindex < cell.attributes.length; attrindex++){
					if((cell.attributes[attrindex].nodeValue != null) && (cell.attributes[attrindex].nodeValue != '')){
						attribute = cell.attributes[attrindex].nodeName;
						if(attribute == 'colspan'){
							colspan = cell.attributes[attrindex].nodeValue;
							continue;
						}
						if(attribute == 'align'){
							align = cell.attributes[attrindex].nodeValue;
							continue;
						}
					}
				}
				virtualend = parseInt(virtualstart) + parseInt(colspan);
				realcolspan = 0;
				for(var k=0; k<indexes_to_keep.length; k++){
					if(indexes_to_keep[k] >= virtualstart && indexes_to_keep[k] < virtualend){
						realcolspan++;
					}
				}
				virtualstart = virtualend;
				if(realcolspan > 0){
					if(align == 'right'){
						for(i = 1; i < realcolspan; i++){
							tableData += "\"\";"
						}
						tableData += "\""+cell.innerText+"\";";
					}
					else{
						tableData += "\""+cell.innerText+"\";";
						for(i = 1; i < realcolspan; i++){
							tableData += "\"\";"
						}
					}
				}
			}
			tableData += "\n";
		}

		
		// Make download link element
		downloadLink = document.createElement("a");
		var data = new Blob([tableData]);
		downloadLink.href = window.URL.createObjectURL(data);
		downloadLink.download = filename;
		document.body.appendChild(downloadLink);
		downloadLink.click();
	 };

		
	this.sort = function(n) {
		
		var order = false; // desc
		if(this.orders[n]){
			this.orders[n] = false;
		}
		else{
			this.orders[n] = true;
			order = true; //asc
		}
		
		var tb = document.getElementById(this.name);
		if (tb.tBodies && tb.tBodies[0]) tb = tb.tBodies[0];
		
		//if rows are expandable, and some rows has been expanded, it disturbs the sort process
		if(this.expand_url !== null){
			var expanded_rows = document.querySelectorAll("[id$='_expanded']");
			for (var i=0; i< expanded_rows.length; i++) {
				tb.removeChild(expanded_rows[i]);
			}
		}

		var index = 0, value = null, maxvalue = null, minvalue = null;
		
		if(order){
			for (var i= tb.rows.length -1; i >= 0; i -= 1) {
				minvalue = value = null;
				index = -1;
				for (var j=i; j >= 0; j -= 1) {
					value = tb.rows[j].cells[n].firstChild;
					//console.log(value);
					while(value !== null && value.nodeType !== Node.TEXT_NODE && value.nodeName !== 'IMG' && value.getAttribute('mconsole_sort_value') === null){
						value = value.firstChild;
					}
					 
					if(value !== null){
						if(value.nodeType === Node.TEXT_NODE){
							value = value.nodeValue;
						}
						else if(value.nodeName === 'IMG'){
							value = value.getAttribute('alt');
						}
						else{ //if(value.getAttribute('mconsole_sort_value') !== 'undefined'){
							value = value.getAttribute('mconsole_sort_value');
						}
						if (isNaN(value)){
							value = value.toLowerCase();
						}
						else{
							value = parseFloat(value);
						}
					}
					
					if(value == null){ // on met les valeurs null au début
						index = j;
						break;
					}
					if (minvalue == null || value < minvalue) {
						index = j;
						minvalue = value;
					}
				}
				if (index != -1) {
					var row = tb.rows[index];
					if (row) {
						tb.removeChild(row);
						tb.appendChild(row);
					}
				}
			}
		}
		else{
			for (var i= tb.rows.length -1; i >= 0; i -= 1) {
				maxvalue = value = null;
				index = -1;
				for (var j=i; j >= 0; j -= 1) {
					value = tb.rows[j].cells[n].firstChild;
					while(value !== null && value.nodeType != Node.TEXT_NODE && value.nodeName != 'IMG' && value.getAttribute('mconsole_sort_value') === null){
						value = value.firstChild;
					}
					
					if(value !== null){
						if(value.nodeType === Node.TEXT_NODE){
							value = value.nodeValue;
						}
						else if(value.nodeName == 'IMG'){
							value = value.getAttribute('alt');
						}
						else{ // if(value.getAttribute('mconsole_sort_value') !== 'undefined'){
							value = value.getAttribute('mconsole_sort_value');
						}

						if(isNaN(value)){
							value = value.toLowerCase();
						}
						else{
							value = parseFloat(value);
						}
					}
					else{ // (value == null) : on met les valeurs null à la fin
						index = j;
						break;
					}
					
					if (maxvalue == null || value > maxvalue) {
						index = j;
						maxvalue = value;
					}
				}
				if (index != -1) {
					var row = tb.rows[index];
					if (row) {
						tb.removeChild(row);
						tb.appendChild(row);
					}
				}
			}
		}
	};
};

var mailto_hexa_tab = new Array();
mailto_hexa_tab['0'] = 0;  mailto_hexa_tab['1'] = 1;  mailto_hexa_tab['2'] = 2;  mailto_hexa_tab['3'] = 3;
mailto_hexa_tab['4'] = 4;  mailto_hexa_tab['5'] = 5;  mailto_hexa_tab['6'] = 6;  mailto_hexa_tab['7'] = 7;
mailto_hexa_tab['8'] = 8;  mailto_hexa_tab['9'] = 9;  mailto_hexa_tab['A'] = 10; mailto_hexa_tab['B'] = 11;
mailto_hexa_tab['C'] = 12; mailto_hexa_tab['D'] = 13; mailto_hexa_tab['E'] = 14; mailto_hexa_tab['F'] = 15;
	
MConsole.mailto_compute = function(my_string){
	var the_string = '';
	var string_code;
	var code;
	var car;
	
	for(var i=0;i<my_string.length;i=i+2){
    	string_code = my_string.substr(i,1);
    	code = 16*mailto_hexa_tab[string_code];
    	string_code = my_string.substr(i+1,1);
    	code += mailto_hexa_tab[string_code];
    	
    	car = String.fromCharCode(code);
    	the_string += car;
    }
    return the_string;
};


MConsole.getDisplayable = function(node, withChildren, withClosingTag){
	var string = null;
	if(node.nodeType == 1) {
		string = '<' + node.nodeName + ' ';

		for(var attrindex = 0; attrindex < node.attributes.length; attrindex++){
			if((node.attributes[attrindex].nodeValue != null) && (node.attributes[attrindex].nodeValue != '')){
				string += node.attributes[attrindex].nodeName + '=' + node.attributes[attrindex].nodeValue + ' ';
			}
		}
		string += '>';
		
		if(string == '<SPAN class=mconsole_sort_button >' || string.startsWith('<SCRIPT ')){
			return '';
		}
	}
	else if(node.nodeType == 3) {
		string = node.nodeValue;
	}

	if(withChildren && node.hasChildNodes()){
		for(var childindex = 0; childindex < node.childNodes.length; childindex++){
			string += this.getDisplayable(node.childNodes[childindex], true, true);
		}
	}
	if((node.nodeType == 1) && withClosingTag) {
		string += '</' + node.nodeName + '>';
	}
	return string;
};




function MConsoleDetailsBox(){};

MConsoleDetailsBox.DEFAULT_WIDTH = 450;
MConsoleDetailsBox.DEFAULT_HEIGHT = 400;

MConsoleDetailsBox.show = function(title,content,ajax,width,height) {

	ajax = typeof(ajax) !== 'undefined' ? ajax : false;
	width = typeof(width)  !== 'undefined' ? width : MConsoleDetailsBox.DEFAULT_WIDTH;
	height = typeof(height) !== 'undefined' ? height : MConsoleDetailsBox.DEFAULT_HEIGHT;

	var modal;
	var modalheader;
	var modalclose;
	var modaltitle;
	var modalcontent;
	var modalmask;

	if(!document.getElementById('mconsole_details_box')) {
		modal = document.createElement('div');
		modal.id = 'mconsole_details_box';

		modalheader = document.createElement('div');
		modalheader.id = 'mconsole_details_box-header';

		modaltitle = document.createElement('span');
		modaltitle.id = 'mconsole_details_box-title';

		modalclose = document.createElement('div');
		modalclose.id = 'mconsole_details_box-close';
		modalclose.setAttribute('onclick','MConsoleDetailsBox.hide()');
		modalclose.onclick = MConsoleDetailsBox.hide;

		modalcontent = document.createElement('div');
		modalcontent.id = 'mconsole_details_box-content';

		modalmask = document.createElement('div');
		modalmask.id = 'mconsole_details_box-mask';
		modalmask.setAttribute('onclick','MConsoleDetailsBox.hide()');
		modalmask.onclick = MConsoleDetailsBox.hide;

		modalheader.appendChild(modaltitle);
		modalheader.appendChild(modalclose);
		modal.appendChild(modalheader);
		modal.appendChild(modalcontent);
		modalmask.appendChild(modal);
		document.body.appendChild(modalmask);
	}
	else {
		modal = document.getElementById('mconsole_details_box');
		modaltitle = document.getElementById('mconsole_details_box-title');
		modalcontent = document.getElementById('mconsole_details_box-content');
		modalmask = document.getElementById('mconsole_details_box-mask');
	}

	modal.style.width = width + "px";
	modalcontent.height = height + "px";
	modaltitle.innerHTML = title;

	if(ajax){
		MConsoleDetailsBox.update(content);
	}
	else{
		modalcontent.innerHTML = content;
	}

	modalmask.style.display = 'block';
	document.body.classList.add('mconsole_details_modal_open');
};
	
// hide the box
MConsoleDetailsBox.hide = function() {
	var modalmask = document.getElementById('mconsole_details_box-mask');
	modalmask.style.display = "none";
	document.body.classList.remove('mconsole_details_modal_open');
};
	

MConsoleDetailsBox.getRequestObject = function(){
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

MConsoleDetailsBox.update = function(url){
	var div = document.getElementById('mconsole_details_box-content');
	var requestObject = MConsoleDetailsBox.getRequestObject();

	if(requestObject){
		requestObject.onreadystatechange = function() {
			if(requestObject.readyState == 4){
				var received = requestObject.responseText;
				div.innerHTML = received;
				var scripts=div.getElementsByTagName("script");
				for (var i=0; i<scripts.length; i++) {
					eval(scripts[i].innerHTML); 
				}
			}
		};
		requestObject.open("GET", url, true);
		requestObject.send();
	}
};



function MConsoleExpandableRow(){};

MConsoleExpandableRow.TIMER = 10;
MConsoleExpandableRow.SPEED = 5;

MConsoleExpandableRow.showHide = function(rowid,url) {
	var expandable_row = document.getElementById(rowid+'_expanded');
	if(expandable_row === null || expandable_row.style.visibility === "hidden") {
		MConsoleExpandableRow.show(rowid,url);
		return true;
    }
    else {
		MConsoleExpandableRow.hide(rowid);
		return false;
    }
}

MConsoleExpandableRow.show = function(rowid,url) {
	var expandable_row = document.getElementById(rowid+'_expanded');

	if(expandable_row === null) {
		var row = document.getElementById(rowid);
		
		expandable_row = document.createElement('tr');
		expandable_row.id = rowid+'_expanded';

		var expandable_content;
		expandable_content = document.createElement('td');
		expandable_content.colSpan = row.cells.length;
		expandable_content.className = "expanded_row";
		//console.log('NB COLS : ' + row.cells.length);
		expandable_row.appendChild(expandable_content);
		
		MConsoleExpandableRow.insertAfter(expandable_row, row);
		
		var requestObject = MConsoleExpandableRow.getRequestObject();
		if(requestObject){
			requestObject.onreadystatechange = function() {
				if(requestObject.readyState == 4){
					var received = requestObject.responseText;
					expandable_content.innerHTML = received;
					var scripts = expandable_content.getElementsByTagName("script");
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
 
	expandable_row.style.opacity = .00;
	expandable_row.style.filter = 'alpha(opacity=0)';
	expandable_row.alpha = 0;
	expandable_row.style.visibility = "visible";
	expandable_row.style.display = '';
	
	expandable_row.timer = setInterval("MConsoleExpandableRow.fade('"+rowid+'_expanded'+"', 1)", MConsoleExpandableRow.TIMER);
};
	
// hide the box
MConsoleExpandableRow.hide = function(id) {
	  var row = document.getElementById(id+'_expanded');
	  row.timer = setInterval("MConsoleExpandableRow.fade('"+id+'_expanded'+"',0)", MConsoleExpandableRow.TIMER);
};
	
// fade-in-out the box
MConsoleExpandableRow.fade = function(id, flag) {
	flag = typeof(flag) !== 'undefined' ? flag : 1;

	var obj = document.getElementById(id);
	var value;
	if(flag === 1) {
		value = obj.alpha + MConsoleExpandableRow.SPEED;
	} else {
		value = obj.alpha - MConsoleExpandableRow.SPEED;
	}
	obj.alpha = value;
	obj.style.opacity = (value / 100);
	obj.style.filter = 'alpha(opacity=' + value + ')';
	if(value >= 99) {
		clearInterval(obj.timer);
		obj.timer = null;
	}
	else if(value <= 1) {
	  obj.style.visibility = "hidden";
	  obj.style.display = "none";
	  clearInterval(obj.timer);
	  obj.timer = null;
	}
};

MConsoleExpandableRow.getRequestObject = function(){
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

MConsoleExpandableRow.insertAfter = function(newElement,targetElement) {
	//target is what you want it to go after. Look for this elements parent.
	var parent = targetElement.parentNode;
	//if the parents lastchild is the targetElement...
	if(parent.lastchild == targetElement) {
		//add the newElement after the target element.
		parent.appendChild(newElement);
	}
	else {
		// else the target has siblings, insert the new element between the target and it's next sibling.
		parent.insertBefore(newElement, targetElement.nextSibling);
	}
};
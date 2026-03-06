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
/** Date class extension                                                       */
/*******************************************************************************/
/**----------------------------------------------------------------------------*/
/** Date class extension                                                       */
/** originaly distributed in date-functions.js :                               */
/** https://gist.github.com/xaprb/8492729                                      */
/**----------------------------------------------------------------------------*/
/**
 * Copyright (C) 2004 Baron Schwartz <baron at sequent dot org>
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, version 2.1.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for more
 * details.
 */

Date.parseFunctions = {count:0};
Date.parseRegexes = [];
Date.formatFunctions = {count:0};

Date.prototype.dateFormat = function(format) {
    if (Date.formatFunctions[format] == null) {
        Date.createNewFormat(format);
    }
    var func = Date.formatFunctions[format];
    return this[func]();
};

Date.createNewFormat = function(format) {
    var funcName = "format" + Date.formatFunctions.count++;
    Date.formatFunctions[format] = funcName;
    var code = "Date.prototype." + funcName + " = function(){return ";
    var special = false;
    var ch = '';
    for (var i = 0; i < format.length; ++i) {
        ch = format.charAt(i);
        if (!special && ch == "\\") {
            special = true;
        }
        else if (special) {
            special = false;
            code += "'" + String.escape(ch) + "' + ";
        }
        else {
            code += Date.getFormatCode(ch);
        }
    }
    eval(code.substring(0, code.length - 3) + ";}");
};

Date.getFormatCode = function(character) {
    switch (character) {
    case "d":
        return "String.leftPad(this.getDate(), 2, '0') + ";
    case "D":
        return "Date.dayNames[this.getDay()].substring(0, 3) + ";
    case "j":
        return "this.getDate() + ";
    case "l":
        return "Date.dayNames[this.getDay()] + ";
    case "S":
        return "this.getSuffix() + ";
    case "w":
        return "this.getDay() + ";
    case "z":
        return "this.getDayOfYear() + ";
    case "W":
        return "this.getWeekOfYear() + ";
    case "F":
        return "Date.monthNames[this.getMonth()] + ";
    case "m":
        return "String.leftPad(this.getMonth() + 1, 2, '0') + ";
    case "M":
        return "Date.monthNames[this.getMonth()].substring(0, 3) + ";
    case "n":
        return "(this.getMonth() + 1) + ";
    case "t":
        return "this.getDaysInMonth() + ";
    case "L":
        return "(this.isLeapYear() ? 1 : 0) + ";
    case "Y":
        return "this.getFullYear() + ";
    case "y":
        return "('' + this.getFullYear()).substring(2, 4) + ";
    case "a":
        return "(this.getHours() < 12 ? 'am' : 'pm') + ";
    case "A":
        return "(this.getHours() < 12 ? 'AM' : 'PM') + ";
    case "g":
        return "((this.getHours() %12) ? this.getHours() % 12 : 12) + ";
    case "G":
        return "this.getHours() + ";
    case "h":
        return "String.leftPad((this.getHours() %12) ? this.getHours() % 12 : 12, 2, '0') + ";
    case "H":
        return "String.leftPad(this.getHours(), 2, '0') + ";
    case "i":
        return "String.leftPad(this.getMinutes(), 2, '0') + ";
    case "s":
        return "String.leftPad(this.getSeconds(), 2, '0') + ";
    case "O":
        return "this.getGMTOffset() + ";
    case "T":
        return "this.getTimezone() + ";
    case "Z":
        return "(this.getTimezoneOffset() * -60) + ";
    default:
        return "'" + String.escape(character) + "' + ";
    }
};

Date.parseDate = function(input, format) {
    if (Date.parseFunctions[format] == null) {
        Date.createParser(format);
    }
    var func = Date.parseFunctions[format];
    return Date[func](input);
};

Date.createParser = function(format) {
    var funcName = "parse" + Date.parseFunctions.count++;
    var regexNum = Date.parseRegexes.length;
    var currentGroup = 1;
    Date.parseFunctions[format] = funcName;

    var code = "Date." + funcName + " = function(input){\n"
        + "var y = -1, m = -1, d = -1, h = -1, i = -1, s = -1;\n"
        + "var d = new Date();\n"
        + "y = d.getFullYear();\n"
        + "m = d.getMonth();\n"
        + "d = d.getDate();\n"
        + "var results = input.match(Date.parseRegexes[" + regexNum + "]);\n"
        + "if (results && results.length > 0) {";
    var regex = "";

    var special = false;
    var ch = '';
    for (var i = 0; i < format.length; ++i) {
        ch = format.charAt(i);
        if (!special && ch == "\\") {
            special = true;
        }
        else if (special) {
            special = false;
            regex += String.escape(ch);
        }
        else {
            var obj = Date.formatCodeToRegex(ch, currentGroup);
            currentGroup += obj.g;
            regex += obj.s;
            if (obj.g && obj.c) {
                code += obj.c;
            }
        }
    }

    code += "if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0 && s >= 0)\n"
        + "{return new Date(y, m, d, h, i, s);}\n"
        + "else if (y > 0 && m >= 0 && d > 0 && h >= 0 && i >= 0)\n"
        + "{return new Date(y, m, d, h, i);}\n"
        + "else if (y > 0 && m >= 0 && d > 0 && h >= 0)\n"
        + "{return new Date(y, m, d, h);}\n"
        + "else if (y > 0 && m >= 0 && d > 0)\n"
        + "{return new Date(y, m, d);}\n"
        + "else if (y > 0 && m >= 0)\n"
        + "{return new Date(y, m);}\n"
        + "else if (y > 0)\n"
        + "{return new Date(y);}\n"
        + "}return null;}";

    Date.parseRegexes[regexNum] = new RegExp("^" + regex + "$");
    eval(code);
};

Date.formatCodeToRegex = function(character, currentGroup) {
	
    switch (character) {
    case "D":
        return {g:0,
        c:null,
        s:"(?:Sun|Mon|Tue|Wed|Thu|Fri|Sat)"};
    case "j":
    case "d":
        return {g:1,
            c:"d = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{1,2})"};
    case "l":
        return {g:0,
            c:null,
            s:"(?:" + Date.dayNames.join("|") + ")"};
    case "S":
        return {g:0,
            c:null,
            s:"(?:st|nd|rd|th)"};
    case "w":
        return {g:0,
            c:null,
            s:"\\d"};
    case "z":
        return {g:0,
            c:null,
            s:"(?:\\d{1,3})"};
    case "W":
        return {g:0,
            c:null,
            s:"(?:\\d{2})"};
    case "F":
        return {g:1,
            c:"m = parseInt(Date.monthNumbers[results[" + currentGroup + "].substring(0, 3)], 10);\n",
            s:"(" + Date.monthNames.join("|") + ")"};
    case "M":
        return {g:1,
            c:"m = parseInt(Date.monthNumbers[results[" + currentGroup + "]], 10);\n",
            s:"(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)"};
    case "n":
    case "m":
        return {g:1,
            c:"m = parseInt(results[" + currentGroup + "], 10) - 1;\n",
            s:"(\\d{1,2})"};
    case "t":
        return {g:0,
            c:null,
            s:"\\d{1,2}"};
    case "L":
        return {g:0,
            c:null,
            s:"(?:1|0)"};
    case "Y":
        return {g:1,
            c:"y = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{4})"};
    case "y":
        return {g:1,
            c:"var ty = parseInt(results[" + currentGroup + "], 10);\n"
                + "y = ty > Date.y2kYear ? 1900 + ty : 2000 + ty;\n",
            s:"(\\d{1,2})"};
    case "a":
        return {g:1,
            c:"if (results[" + currentGroup + "] == 'am') {\n"
                + "if (h == 12) { h = 0; }\n"
                + "} else { if (h < 12) { h += 12; }}",
            s:"(am|pm)"};
    case "A":
        return {g:1,
            c:"if (results[" + currentGroup + "] == 'AM') {\n"
                + "if (h == 12) { h = 0; }\n"
                + "} else { if (h < 12) { h += 12; }}",
            s:"(AM|PM)"};
    case "g":
    case "G":
    case "h":
    case "H":
        return {g:1,
            c:"h = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{1,2})"};
    case "i":
        return {g:1,
            c:"i = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{2})"};
    case "s":
        return {g:1,
            c:"s = parseInt(results[" + currentGroup + "], 10);\n",
            s:"(\\d{2})"};
    case "O":
        return {g:0,
            c:null,
            s:"[+-]\\d{4}"};
    case "T":
        return {g:0,
            c:null,
            s:"[A-Z]{3}"};
    case "Z":
        return {g:0,
            c:null,
            s:"[+-]\\d{1,5}"};
    default:
        return {g:0,
            c:null,
            s:String.escape(character)};
    }
};

Date.prototype.getTimezone = function() {
    return this.toString().replace(
        /^.*? ([A-Z]{3}) [0-9]{4}.*$/, "$1").replace(
        /^.*?\(([A-Z])[a-z]+ ([A-Z])[a-z]+ ([A-Z])[a-z]+\)$/, "$1$2$3");
};

Date.prototype.getGMTOffset = function() {
    return (this.getTimezoneOffset() > 0 ? "-" : "+")
        + String.leftPad(Math.floor(this.getTimezoneOffset() / 60), 2, "0")
        + String.leftPad(this.getTimezoneOffset() % 60, 2, "0");
};

Date.prototype.getDayOfYear = function() {
    var num = 0;
    Date.daysInMonth[1] = this.isLeapYear() ? 29 : 28;
    for (var i = 0; i < this.getMonth(); ++i) {
        num += Date.daysInMonth[i];
    }
    return num + this.getDate() - 1;
};

Date.prototype.getWeekOfYear = function() {
    // Skip to Thursday of this week
    var now = this.getDayOfYear() + (4 - this.getDay());
    // Find the first Thursday of the year
    var jan1 = new Date(this.getFullYear(), 0, 1);
    var then = (7 - jan1.getDay() + 4);
    //document.write(then);
    return String.leftPad(((now - then) / 7) + 1, 2, "0");
};

Date.prototype.isLeapYear = function() {
    var year = this.getFullYear();
    return ((year & 3) == 0 && (year % 100 || (year % 400 == 0 && year)));
};

Date.prototype.getFirstDayOfMonth = function() {
    var day = (this.getDay() - (this.getDate() - 1)) % 7;
    return (day < 0) ? (day + 7) : day;
};

Date.prototype.getLastDayOfMonth = function() {
    var day = (this.getDay() + (Date.daysInMonth[this.getMonth()] - this.getDate())) % 7;
    return (day < 0) ? (day + 7) : day;
};

Date.prototype.getDaysInMonth = function() {
    Date.daysInMonth[1] = this.isLeapYear() ? 29 : 28;
    return Date.daysInMonth[this.getMonth()];
};

Date.prototype.getSuffix = function() {
    switch (this.getDate()) {
        case 1:
        case 21:
        case 31:
            return "st";
        case 2:
        case 22:
            return "nd";
        case 3:
        case 23:
            return "rd";
        default:
            return "th";
    }
};

String.escape = function(string) {
    return string.replace(/('|\\)/g, "\\$1");
};

String.leftPad = function (val, size, ch) {
    var result = new String(val);
    if (ch == null) {
        ch = " ";
    }
    while (result.length < size) {
        result = ch + result;
    }
    return result;
};

Date.daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
Date.monthNames =
   ["January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"];
Date.dayNames =
   ["Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"];

Date.y2kYear = 50;
Date.monthNumbers = {
    Jan:0,
    Feb:1,
    Mar:2,
    Apr:3,
    May:4,
    Jun:5,
    Jul:6,
    Aug:7,
    Sep:8,
    Oct:9,
    Nov:10,
    Dec:11
};
Date.patterns = {
    ISO8601LongPattern:"Y-m-d H:i:s",
    ISO8601ShortPattern:"Y-m-d",
    ShortDatePattern: "n/j/Y",
    LongDatePattern: "l, F d, Y",
    FullDateTimePattern: "l, F d, Y g:i:s A",
    MonthDayPattern: "F d",
    ShortTimePattern: "g:i A",
    LongTimePattern: "g:i:s A",
    SortableDateTimePattern: "Y-m-d\\TH:i:s",
    UniversalSortableDateTimePattern: "Y-m-d H:i:sO",
    YearMonthPattern: "F, Y"
};
/*******************************************************************************/
/** END : Date class extension                                                 */
/*******************************************************************************/


/*******************************************************************************/
/** MDatePicker class                                                          */
/*******************************************************************************/
/**----------------------------------------------------------------------------*/
/** Classe MDatePicker                                                         */
/** Originaly DateChooser in datechooser.js                                    */
/** http://www.xaprb.com/media/2005/09/datechooser.js                          */
/**----------------------------------------------------------------------------*/
    
/**
 * Copyright (C) 2004 Baron Schwartz <baron at sequent dot org>
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, version 2.1.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for more
 * details.
 */


/**
 * MDatePicker constructor
 * @param {string} input the input field on which we set a date picker
 * @param {integer} start the first date selectable
 * @param {integer} end the last date selectable
 * @param {string} format a string which represents the output date format
 * @param {boolean} isTimePicker
 * @returns {MDatePicker}
 */
function MDatePicker(input, start, end, format, isTimePicker) {
    this._input = input;
    this._inputId = input.id;
    this._start = Date.parseDate(start, format);
    this._end = Date.parseDate(end, format);
    this._format = format;
	this._date;
    this._isTimePicker = isTimePicker;
    
    // Choose a random prefix for all pulldown menus
    this._prefix = "";
    var letters = ["a", "b", "c", "d", "e", "f", "h", "i", "j", "k", "l",
        "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
    for (var i = 0; i < 10; ++i) {
        this._prefix += letters[Math.floor(Math.random() * letters.length)];
    }
    
    var pickerdiv = document.getElementById('mform_picker');
    if(! pickerdiv) {
    	pickerdiv = document.createElement('div');
    	pickerdiv.id = 'mform_picker';
    	pickerdiv.className = "mform_datepicker select-free";
    	document.body.appendChild(pickerdiv);
    }
    this._div = pickerdiv;
};

MDatePicker.monthNames = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Decembre"];

/**
 * MDatePicker prototype variables
 * @type Boolean
 */
MDatePicker.prototype._isVisible = false;

/**
 * Returns true if the picker is currently visible
 * @returns {Boolean}
 */
MDatePicker.prototype.isVisible = function() {
    return this._isVisible;
};

/**
 * Returns true if the picker is to allow choosing the time as well as the date
 * @returns {Boolean}
 */
MDatePicker.prototype.isTimePicker = function() {
    return this._isTimePicker;
};

/**
 * Gets the value, as a formatted string, of the date attached to the picker
 * @returns {unresolved}
 */
MDatePicker.prototype.getValue = function() {
    return this._date.dateFormat(this._format);
};

/**
 * Hides the picker
 */
MDatePicker.prototype.hide = function() {
    this._div.style.visibility = "hidden";
    this._div.style.display = "none";
    this._div.innerHTML = "";
    this._isVisible = false;
};

/**
 * Shows the picker on the page
 */
MDatePicker.prototype.show = function() {
	var div = this._div;
	var input = this._input;
    var inputId = this._inputId;
    
    // define bahaviour when click ouside the date picker
    document.body.onclick = function(e){
        var oElem = e ? e.target : event.srcElement;
        if( oElem === div || oElem === input){
        	return;
        }
        while(oElem.parentNode !== null && oElem.parentNode !== div){
        	oElem = oElem.parentNode;
        }
        if(oElem.parentNode === null || oElem.parentNode !== div){
        	if((document.getElementById(inputId)).datePicker.isVisible()){
        		MDatePicker.hidePickerFor(inputId);
        	}
        }
    };
    
    // calculate the position before making it visible
    var inputPos = MDatePicker.getAbsolutePosition(this._input);
	var wwidth = window.innerWidth != null ? window.innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body != null ? document.body.clientWidth : null;
	var left = inputPos[1] + this._input.offsetWidth;
	if(left < wwidth - 300){ // ici 300 est arbitraire : la largeur mas normalement est plutot de l'ordre de 250 (180 pour un date + 60 si datetime)
		this._div.style.top = inputPos[0] + "px";
		this._div.style.left = (inputPos[1] + this._input.offsetWidth) + "px";
	}
	else{
		this._div.style.top = (inputPos[0] + this._input.offsetHeight) + "px";
		this._div.style.left = inputPos[1] + "px";
	}
    this._div.innerHTML = this.createPickerHtml();
    this._div.style.display = "block";
    this._div.style.visibility = "visible";
    this._div.style.position = "absolute";
    this._isVisible = true;
};


/**
 * Sets the date attached to the picker
 * @param {Date} date
 * @returns {undefined}
 */
MDatePicker.prototype.setDate = function(date) {
    var defaultdate = new Date();
    if(defaultdate > this._end || defaultdate < this._start){
    	defaultdate = this._start;
    }
	if(date){
		if(date > this._end){
			this._date = this._end;
		}
		else if(date < this._start){
			this._date = this._start;
		}
		else{
			this._date = date;
		}
	}
	else{
		this._date = defaultdate;
	}
};

/**
 * Sets the time portion of the date attached to the picker
 * @param {integer} hour
 * @param {integer} minute
 * @returns {undefined}
 */
MDatePicker.prototype.setTime = function(hour, minute) {
    this._date.setHours(hour);
    this._date.setMinutes(minute);
};

/**
 * Creates the HTML for the whole picker
 */
MDatePicker.prototype.createPickerHtml = function() {
    var formHtml = "<form><div  id='date_part'><input type=\"hidden\" name=\""
        + this._prefix + "inputId\" value=\""
        + this._input.getAttribute('id') + "\">"
        + "\r\n  <select name=\"" + this._prefix 
        + "month\" onChange=\"MDatePicker.changeDate(this.form, '"
        + this._prefix + "');\">";
    for (var monIndex = 0; monIndex <= 11; monIndex++) {
        formHtml += "\r\n    <option value=\"" + monIndex + "\""
            + (monIndex == this._date.getMonth() ? " selected=\"1\"" : "")
            + ">" + MDatePicker.monthNames[monIndex] + "</option>";
    }
    formHtml += "\r\n  </select>\r\n  <select style=\"float:right;\" name=\""
        + this._prefix + "year\" onChange=\"MDatePicker.changeDate(this.form, '"
        + this._prefix + "');\">";
    for (var i = this._start.getFullYear(); i <= this._end.getFullYear(); ++i) {
        formHtml += "\r\n    <option value=\"" + i + "\""
            + (i == this._date.getFullYear() ? " selected=\"1\"" : "")
            + ">" + i + "</option>";
    }
    formHtml += "\r\n  </select>";
    formHtml += this.createCalendarHtml();
    if (this._isTimePicker) {
        formHtml += "</div><div id='time_part'>" + this.createTimePickerHtml();
    }
    formHtml += "</div></form>";
    return formHtml;
};


/**
 * Creates the HTML for the actual calendar part of the picker
 */
MDatePicker.prototype.createCalendarHtml = function() {
    var result = "\r\n<table cellspacing=\"0\" class=\"datePicker\">"
        + "\r\n  <tr><th>L</th><th>M</th><th>M</th>"
        + "<th>J</th><th>V</th><th>S</th><th>D</th></tr>\r\n  <tr>";
    // Fill up the days of the week until we get to the first day of the month
    var firstDay = this._date.getFirstDayOfMonth();
    firstDay = (firstDay + 6) % 7; //ligne ajoutée pour FR
    var lastDay = this._date.getLastDayOfMonth();
    lastDay = (lastDay + 6) % 7; //ligne ajoutée pour FR
    if (firstDay != 0) {
        result += "<td colspan=\"" + firstDay + "\">&nbsp;</td>";
    }
    // Fill in the days of the month
    var i = 0;
    while (i < this._date.getDaysInMonth()) {
        if (((i++ + firstDay) % 7) == 0) {
            result += "</tr>\r\n  <tr>";
        }
        var thisDay = new Date(
            this._date.getFullYear(),
            this._date.getMonth(), i);
        var js = '"MDatePicker.setInputDate(\''
            + this._input.getAttribute('id') + "', '"
            + thisDay.dateFormat(this._format) + '\');"';
        result += "\r\n    <td class=\"mform_datepicker_active"
            // If the date is the currently chosen date, highlight it
            + (i == this._date.getDate() ? " mform_datepicker_active_today" : "")
            + "\" onClick=" + js + ">" + i + "</td>";
    }
    // Fill in any days after the end of the month
    if (lastDay != 6) {
        result += "<td colspan=\"" + (6 - lastDay) + "\">&nbsp;</td>";
    }
    return result + "\r\n  </tr>\r\n</table><!--[if lte IE 6.5]><iframe></iframe><![endif]-->";
};

/**
 * Creates the extra HTML needed for choosing the time
 */
MDatePicker.prototype.createTimePickerHtml = function() {
    // Add hours
	var h = this._date.getHours();
	if(h < 10) h = "0"+h;
	var m = this._date.getMinutes();
	if(m < 10) m = "0"+m;
    var result = "<input type=number step=1 min=00 max=23 name=\"" + this._prefix + "hour\" value=\"" +h+"\" onChange=\"MDatePicker.standardizeLeadingZero(this);\">";
	result += "<br><br><input type=number step=5 min=00 max=59 name=\"" + this._prefix + "min\" value=\"" +m+"\" onChange=\"MDatePicker.standardizeLeadingZero(this);\">";
    return result;
};

MDatePicker.standardizeLeadingZero = function(input) {
	if(input.value < 10){
		input.value = "0"+input.value;
	}
};

/**
 * Gets the absolute position on the page of the element passed in
 */
MDatePicker.getAbsolutePosition = function(obj) {
    var result = [0, 0];
    while (obj != null) {
        result[0] += obj.offsetTop;
        result[1] += obj.offsetLeft;
        obj = obj.offsetParent;
    }
    return result;
};

/**
 * Shows or hides the date picker on the page
 */
MDatePicker.showHide = function(inputId, start, end, format, isTimePicker) {
    var input = document.getElementById(inputId);
    if (input.datePicker === undefined) {
        input.datePicker = new MDatePicker(input, start, end, format, isTimePicker);
    }
    if (input.datePicker.isVisible()) {
        input.datePicker.hide();
    }
    else {
		input.datePicker.setDate(Date.parseDate(input.value, format));
    	input.datePicker.show();
    }
};

MDatePicker.hidePickerFor = function(inputId) {
	var input = document.getElementById(inputId);
    if (input.datePicker !== undefined) {
    	if (input.datePicker.isVisible()) {
    		input.datePicker.hide();
    	}
    }
};

/**
 * Sets a date on the object attached to 'inputId'
 */
MDatePicker.setInputDate = function(inputId, value) {
    var input = document.getElementById(inputId);
    if (input !== undefined && input.datePicker !== undefined) {
        input.datePicker.setDate(Date.parseDate(value, input.datePicker._format));
        if (input.datePicker.isTimePicker()) {
            var prefix = input.datePicker._prefix;
			var theForm = document.getElementsByName(prefix+'inputId')[0].form;
			input.datePicker.setTime(
				parseInt(theForm.elements[prefix + 'hour'].value),
				parseInt(theForm.elements[prefix + 'min'].value));
        }
        input.value = input.datePicker.getValue();
        input.datePicker.hide();
    }
};

/**
 * The callback function for when someone changes the pulldown menus on the date
 * picker
 */
MDatePicker.changeDate = function(theForm, prefix) {
    var input = document.getElementById(theForm.elements[prefix + 'inputId'].value);
    var newDate = new Date(theForm.elements[prefix + 'year'].options[theForm.elements[prefix + 'year'].selectedIndex].value,
						   theForm.elements[prefix + 'month'].options[theForm.elements[prefix + 'month'].selectedIndex].value,
						   1);
    // Try to preserve the day of month (watch out for months with 31 days)
    newDate.setDate(Math.max(1, Math.min(newDate.getDaysInMonth(), input.datePicker._date.getDate())));
    input.datePicker.setDate(newDate);
    if (input.datePicker.isTimePicker()) {
		input.datePicker.setTime(
            parseInt(theForm.elements[prefix + 'hour'].value),
            parseInt(theForm.elements[prefix + 'min'].value));
    }
    input.datePicker.show();
};
/*******************************************************************************/
/** END : MDatePicker class                                                    */
/*******************************************************************************/


/*******************************************************************************/
/** MTimePicker class                                                          */
/*******************************************************************************/
/**
 * MTimePicker constructor
 * @param {string} input the input field on which we set a time picker
 * @returns {MTimePicker}
 */
function MTimePicker(input) {
    this._input = input;
    this._inputId = input.id;
	
	this._time;
	this._hours;
    this._minutes;
	
	
    // Choose a random prefix for all pulldown menus
    this._prefix = "";
    var letters = ["a", "b", "c", "d", "e", "f", "h", "i", "j", "k", "l",
        "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
    for (var i = 0; i < 10; ++i) {
        this._prefix += letters[Math.floor(Math.random() * letters.length)];
    }
    
    var pickerdiv = document.getElementById('mform_time_picker');
    if(! pickerdiv) {
    	pickerdiv = document.createElement('div');
    	pickerdiv.id = 'mform_time_picker';
    	pickerdiv.className = "mform_timepicker select-free";
    	document.body.appendChild(pickerdiv);
    }
    this._div = pickerdiv;
};

/**
 * MTimePicker prototype variables
 * @type Boolean
 */
MTimePicker.prototype._isVisible = false;

/**
 * Returns true if the picker is currently visible
 * @returns {Boolean}
 */
MTimePicker.prototype.isVisible = function() {
    return this._isVisible;
};

/**
 * Gets the value, as a formatted string, of the date attached to the picker
 * @returns {unresolved}
 */
MTimePicker.prototype.getValue = function() {
    return this._time;
};

/**
 * Gets the value, as a formatted string, of the date attached to the picker
 * @returns {unresolved}
 */
MTimePicker.prototype.getHours = function() {
    return this._hours;
};

/**
 * Gets the value, as a formatted string, of the date attached to the picker
 * @returns {unresolved}
 */
MTimePicker.prototype.getMinutes = function() {
    return this._minutes;
};

/**
 * Hides the picker
 */
MTimePicker.prototype.hide = function() {
    this._div.style.visibility = "hidden";
    this._div.style.display = "none";
    this._div.innerHTML = "";
    this._isVisible = false;
};

/**
 * Shows the picker on the page
 */
MTimePicker.prototype.show = function() {
	
	var div = this._div;
	var input = this._input;
    var inputId = this._inputId;
    
    // define bahaviour when click ouside the date picker
    document.body.onclick = function(e){
        var oElem = e ? e.target : event.srcElement;
        if( oElem === div || oElem === input){
        	return;
        }
        while(oElem.parentNode !== null && oElem.parentNode !== div){
        	oElem = oElem.parentNode;
        }
        if(oElem.parentNode === null || oElem.parentNode !== div){
        	if((document.getElementById(inputId)).timePicker.isVisible()){
        		MTimePicker.hidePickerFor(inputId);
        	}
        }
    };
    
    // calculate the position before making it visible
    var inputPos = MTimePicker.getAbsolutePosition(this._input);
	var wwidth = window.innerWidth != null ? window.innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body != null ? document.body.clientWidth : null;
	var left = inputPos[1] + this._input.offsetWidth;
	if(left < wwidth - 300){
		this._div.style.top = inputPos[0] + "px";
		this._div.style.left = (inputPos[1] + this._input.offsetWidth) + "px";
	}
	else{
		this._div.style.top = (inputPos[0] + this._input.offsetHeight) + "px";
		this._div.style.left = inputPos[1] + "px";
	}
    this._div.innerHTML = this.createPickerHtml();
    this._div.style.display = "block";
    this._div.style.visibility = "visible";
    this._div.style.position = "absolute";
    this._isVisible = true;
};

/**
 * Sets the time portion of the date attached to the picker
 * @param {integer} hour
 * @param {integer} minute
 * @returns {undefined}
 */
MTimePicker.prototype.setTime = function(time) {
	if(time){
		this._time = time;
		var hm = time.split(':');
		this._hours = hm[0];
		this._minutes = hm[1];
	}
	else{
		this._time = "12:00";
		this._hours = "12";
		this._minutes = "00";
	}
};

/**
 * Creates the HTML for the whole picker
 */
MTimePicker.prototype.createPickerHtml = function() {
    var formHtml = "<form><input type=\"hidden\" name=\""
        + this._prefix + "inputId\" value=\""
        + this._input.getAttribute('id') + "\">";
    formHtml += this.createTimePickerHtml();
    formHtml += "</form>";
    return formHtml;
};

MTimePicker.prototype.createTimePickerHtml = function() {
    // Add hours
    var result = "<input type=number step=1 min=00 max=23 name=\"" + this._prefix + "hour\" value=\"" +this.getHours()+"\" onChange=\"MTimePicker.standardizeLeadingZero(this);\">";
	result += " : ";
	result += "<input type=number step=5 min=00 max=59 name=\"" + this._prefix + "min\" value=\"" +this.getMinutes()+"\" onChange=\"MTimePicker.standardizeLeadingZero(this);\">";
	var js = '"MTimePicker.setInputTime(\'' + this._input.getAttribute('id') + '\');"';
    result += "<br><button style=\"width:100%;\" onClick=" + js + ">Appliquer</button>";
	
    return result;
};

MTimePicker.standardizeLeadingZero = function(input) {
	if(input.value < 10){
		input.value = "0"+input.value;
	}
};

/**
 * Gets the absolute position on the page of the element passed in
 */
MTimePicker.getAbsolutePosition = function(obj) {
    var result = [0, 0];
    while (obj != null) {
        result[0] += obj.offsetTop;
        result[1] += obj.offsetLeft;
        obj = obj.offsetParent;
    }
    return result;
};

/**
 * Shows or hides the date picker on the page
 */
MTimePicker.showHide = function(inputId) {
    var input = document.getElementById(inputId);
    if (input.timePicker === undefined) {
        input.timePicker = new MTimePicker(input);
    }
    input.timePicker.setTime(input.value);
    if (input.timePicker.isVisible()) {
        input.timePicker.hide();
    }
    else {
    	input.timePicker.show();
    }
};

MTimePicker.hidePickerFor = function(inputId) {
	var input = document.getElementById(inputId);
    if (input.timePicker !== undefined) {
    	if (input.timePicker.isVisible()) {
    		input.timePicker.hide();
    	}
    }
};

/**
 * Sets a date on the object attached to 'inputId'
 */
MTimePicker.setInputTime = function(inputId) {
    var input = document.getElementById(inputId);
    if (input !== undefined && input.timePicker !== undefined) {   
		var prefix = input.timePicker._prefix;
		var theForm = document.getElementsByName(prefix+'inputId')[0].form;
		input.timePicker.setTime(theForm.elements[prefix + 'hour'].value + ':' + theForm.elements[prefix + 'min'].value);

        input.value = input.timePicker.getValue();
        input.timePicker.hide();
    }
};
/*******************************************************************************/
/** END : MTimePicker class                                                    */
/*******************************************************************************/


/*******************************************************************************/
/** MForm                                                                      */
/*******************************************************************************/
function MForm(){};

/** 
 * Pour ajouter un champ quand on clique sur le "+" pour un champs de type input, multi
 */
MForm.addInput = function(field) {
	var container = document.getElementById(field+'_container');
	var nodes = container.childNodes;

	//console.log("nb elements : " + nodes.length);
	
	var last_input_index;
	var number = 0;
	for(var i=0; i<nodes.length; i++){
		//console.log(i + " : " + nodes[i]);
		if(nodes[i].nodeName === "INPUT"){
			last_input_index = i;
			number++;
		}
	}
	//console.log("last_input_index : " + last_input_index);
	
	var last_input = nodes[last_input_index];
	//console.log("last_input : " + last_input);
	
	if(last_input.value === "") return;
	
	var image_minus = nodes[last_input_index+1];
	var image_plus = nodes[last_input_index+2];
	var br = document.createElement("br");
	
	var input_clone = last_input.cloneNode();
	input_clone.value = '';
	var image_minus_clone = image_minus.cloneNode();
	image_minus_clone.setAttribute( "onclick", "MForm.deleteInput('"+field+"', "+number+");" );
	
	container.insertBefore(br, image_plus);
	container.insertBefore(input_clone, image_plus);
	container.insertBefore(image_minus_clone, image_plus);
};

MForm.deleteInput = function(field, index) {
	var container = document.getElementById(field+'_container');

	var nodes = container.childNodes;
	//Count number of inputs
	var nb_inputs = 0;
	for(var i=0; i<nodes.length; i++){
		if(nodes[i].nodeName === "INPUT"){
			nb_inputs++;
		}
	}
	
	var input;
	if(nb_inputs == 1){
		for(var i=0; i<nodes.length; i++){
			if(nodes[i].nodeName === "INPUT"){
				input = nodes[i];
				break;
			}
		}
		input.value = "";
	}
	else{
		//console.log("nb elements : " + nodes.length);
		var current_index = -1;
		var input_index;
		for(var i=0; i<nodes.length; i++){
			//console.log(i + " : " + nodes[i]);
			if(nodes[i].nodeName === "INPUT"){
				current_index++;
				if(current_index === index){
					input = nodes[i];
					input_index = i;
				}
				else if(current_index > index){
					nodes[i+1].setAttribute( "onclick", "MForm.deleteInput('"+field+"', "+(current_index-1)+");" );
				}
			}
		}

		if(index == 0){
			var image_minus = nodes[input_index+1];
			var br = nodes[input_index+2];
		}
		else{
			var image_minus = nodes[input_index+1];
			var br = nodes[input_index-1];
		}

		container.removeChild(input);
		container.removeChild(br);
		container.removeChild(image_minus);
	}
};

MForm.addComplex = function(field) {
	
	var container = document.getElementById(field+'_container');

	var table = container.getElementsByTagName("TABLE")[0];
	var tbody = table.getElementsByTagName("TBODY")[0];
	var nodes = tbody.childNodes;
	var last_complex = "";
	
	console.log("nb table rows : " + nodes.length);
	for(var i=nodes.length-1; i>=0; i--){
		console.log(i + " : " + nodes[i]);
		if(nodes[i].nodeName === "TR"){
			last_complex = nodes[i];
			break;
		}
	}
	console.log("-------------------------------");
	console.log("Exploring last row ...");
	var filled = false;
	var cells = last_complex.childNodes;
	var elements;
	var element;
	
	for(var j=0; j<cells.length - 1; j++){ // -1 parce que la dernière cellule contient les - et +
		console.log("Searching form element in cell " + j);
		elements = cells[j].childNodes;
		for(var k=0; k<elements.length; k++){
			console.log("    Cell element " + k + " : " + elements[k]);
			if(elements[k].nodeName !== "#text"){
				element = elements[k]
				// Si un element au moins d'un champ complexe est saisi, on considère que le champ est saisi, d'où les "break" ci-dessous
				console.log("Looking if form element is filled");
				if(element.nodeName === "SELECT"){
					console.log("    Form element is SELECT");
					if(element.options[element.selectedIndex].value !== ""){
						filled = true;
						console.log("        FILLED : "+element.name+" : "+element.options[element.selectedIndex].value);
						break;
					}
				}
				else if(element.nodeName === "LABEL"){
					// Il n'y a que pour les boutons radio que MForm.php génère des balise <label>
					console.log("    Form element is RADIO");
					for(var l=0; l<element.childNodes.length; l++){
						if(element.childNodes[l].nodeName !== "#text"){
							if(element.childNodes[l].checked){
								filled = true;
								console.log("        FILLED : "+element.childNodes[l].name+" : "+element.childNodes[l].value);
								break;
							}
						}
					}
				}
				else{
					console.log("    Form element is INPUT");
					if(element.value !== ""){
						filled = true;
						console.log("        FILLED : "+element.name+" : "+element.value);
						break;
					}
				}
			}
		}
		if(filled){
			break;
		}
	}
	console.log("FILLED : "+filled);
	
	if(filled){
		// on clone la dernière ligne et on la modifie (lien image 'moins') avant de l'ajouter
		var complex = last_complex.cloneNode(true);
		var number = parseInt(last_complex.id.substring(last_complex.id.lastIndexOf('_')+1)) + 1;
		complex.id = last_complex.id.substring(0, last_complex.id.lastIndexOf('_')) + "_" + number;

		var reg = new RegExp("(\[[0-9]+\])");
		cells = complex.childNodes;

		console.log("Renaming all form elements for the new line (increment indexes)");
		for(j=0; j<cells.length - 1; j++){
			console.log("Searching form element in cell " + j);
			elements = cells[j].childNodes;
			for(var k=0; k<elements.length; k++){
				console.log("    Cell element " + k + " : " + elements[k]);
				if(elements[k].nodeName !== "#text"){
					element = elements[k];
					
					/*if(element.nodeName === "SELECT"){
						console.log("Renaming SELECT and unsets value");
						element.name = element.name.replace(reg, "["+number+"]");
						element.value = '';
					}
					else */
					if(element.nodeName === "LABEL"){
						console.log("Renaming RADIO buttons and uncheck all");
						element.htmlFor = element.htmlFor.replace(reg, "["+number+"]");
						for(var l=0; l<element.childNodes.length; l++){
							if(element.childNodes[l].nodeName !== "#text"){ // Les inputs de type radio
								element.childNodes[l].name = element.childNodes[l].name.replace(reg, "["+number+"]");
								element.childNodes[l].id = element.childNodes[l].id.replace(reg, "["+number+"]");
								element.childNodes[l].checked = false;
							}
						}
					}
					else if(element.nodeName !== "#text"){
						console.log("Renaming INPUT and unset value");
						element.name = element.name.replace(reg, "["+number+"]");
						element.value = '';
					}
				}
			}
		}

		var last_cell = cells[cells.length - 1];
		var image_minus = last_cell.childNodes[0];
		
		image_minus.setAttribute( "onclick", "MForm.deleteComplex('"+field+"', "+number+");" );
		tbody.appendChild(complex);

		// On enleve le + à ce qui est mintenant l'avant derniere ligne
		cells = last_complex.childNodes;
		last_cell = cells[cells.length - 1];
		last_cell.removeChild(last_cell.childNodes[1]);// Normalement il n'y a que 2 elements (les 2 images)
	}
};

MForm.deleteComplex = function(field, index) {
	
	var container = document.getElementById(field+'_container');
	
	var table = container.getElementsByTagName("TABLE")[0];
	var tbody = table.getElementsByTagName("TBODY")[0];
	var nodes = tbody.childNodes;
	
	var last = false;
	
	//Count number of inputs
	var nb_complex = 0;
	for(var i=nodes.length-1; i>=0; i--){
		console.log(i + " : " + nodes[i]);
		if(nodes[i].nodeName === "TR" && Number.isInteger(Number(nodes[i].id.substring(nodes[i].id.lastIndexOf('_')+1)))){
			nb_complex++;
			if((nb_complex == 1) && (nodes[i].id ===  field+'_'+index)){
				last = true;
			}
		}
	}
	console.log("nb complex : " + nb_complex);
	
	var node = document.getElementById(field+'_'+index);
	
	if(nb_complex == 1){ // S'il n'y en a q'un, l'action de supprimer revient à effacer toutes les valeurs
		var cells = node.childNodes;
		var elements;
		var element;
		for(j=0; j<cells.length; j++){
			elements = cells[j].childNodes;
			for(var l=0; l<elements.length; l++){
				if(elements[l].nodeName !== "#text"){
					element = elements[l];
					if(element.nodeName === "LABEL"){
						for(var k=0; k<element.childNodes.length; k++){
							if(element.childNodes[k].nodeName !== "#text"){
								element.childNodes[k].checked = false;
							}
						}
					}
					else if(element.nodeName !== "#text"){
						element.value = '';
					}
				}
			}
		}
	}
	else{
		if(last){
			var cells = node.childNodes;
			var last_cell = cells[cells.length - 1];
			var image_plus = last_cell.childNodes[1];// Normalement il n'y a que 2 elements (les 2 images)
			var previous_row = node.previousSibling;
			var previous_row_elmts = previous_row.childNodes;
			var previous_row_last_cell = previous_row_elmts[previous_row_elmts.length - 1];
			previous_row_last_cell.appendChild(image_plus);
		}
		tbody.removeChild(node);
	}
};


/**
 * Quand on valide un formulaire, fait apparaitre un popup "Patientez", et s'occupe de transmettre le formulaire
 */
MForm.submit = function(formname) {
	// on fait apparaitre le popup
    var blocWait = document.getElementById('mform_wait');
	if (blocWait) {
    	blocWait.style.display = 'block';
    }
    
    // on remet tout les champs du formulaire qui etaient DISABLED à ENABLED
    // en effet on a pu desactiver des champs du formulaire mais les mettre qd mm à titre indicatif,
    // mais on veut pouvoir les récupérer qd meme.
	var formulaire = null;
	if(formname){
	    formulaire = window.document.forms[formname];
	}
	else{
		formulaire = window.document.forms[0];
	}
    var elements = formulaire.elements;
    for(var i=0; i<elements.length; i++){
    	elements[i].disabled = false;
    }
};


MForm.help = function(title, url, width, height) {
	width = ((typeof(width)  !== 'undefined') && (width != '')) ? width : undefined;
	height = ((typeof(height) !== 'undefined' ) && (height != '')) ? height : undefined;
	MFormHelpBox.show(title, url, width, height);
};

/**
 * Pour les fieldset "hideable" que l'on peut donc deplier ou replier
 */
MForm.showhide = function(fieldsetname) {
	
    var allTrEtelements = document.getElementsByTagName("tr");
    var fieldsetelements = new Array();
    
    var regex = "^"+fieldsetname+"_";
    for(var i=0; i<allTrEtelements.length; i++){
    	if(allTrEtelements[i].id.match(regex)){
    		fieldsetelements.push(allTrEtelements[i]);
    	}
    }
    
    var visible = 'table-row';
    if (navigator.appName == "Microsoft Internet Explorer") {
    	visible = 'inline';
    }
    
    if(fieldsetelements[0].style.display == 'none'){
    	for(var i=0; i<fieldsetelements.length; i++){
    		fieldsetelements[i].style.display = visible;
    	}
    }
    else{
    	for(var i=0; i<fieldsetelements.length; i++){ 
    		fieldsetelements[i].style.display = 'none';
    	}
    }
};


MForm.renewCaptcha = function(id_img, url) {
	var image = document.getElementById(id_img);
	image.setAttribute("src", url+'?'+new Date().getTime());
};


/*******************************************************************************/
/** MFORM  HELP BOX                                                            */
/*******************************************************************************/
function MFormHelpBox(){};

MFormHelpBox.DEFAULT_WIDTH = 350;
MFormHelpBox.DEFAULT_HEIGHT = 200;

MFormHelpBox.show = function(title,url,width,height) {
  
	width = typeof(width)  !== 'undefined' ? width : MFormHelpBox.DEFAULT_WIDTH;
	height = typeof(height) !== 'undefined' ? height : MFormHelpBox.DEFAULT_HEIGHT;

	var modal;
	var modalheader;
	var modalclose;
	var modaltitle;
	var modalcontent;
	var modalmask;

	if(!document.getElementById('mform_help_box')) {
		modal = document.createElement('div');
		modal.id = 'mform_help_box';

		modalheader = document.createElement('div');
		modalheader.id = 'mform_help_box-header';

		modaltitle = document.createElement('span');
		modaltitle.id = 'mform_help_box-title';

		modalclose = document.createElement('div');
		modalclose.id = 'mform_help_box-close';
		modalclose.setAttribute('onclick','MFormHelpBox.hide()');
		modalclose.onclick = MFormHelpBox.hide;

		modalcontent = document.createElement('div');
		modalcontent.id = 'mform_help_box-content';

		modalmask = document.createElement('div');
		modalmask.id = 'mform_help_box-mask';
		modalmask.setAttribute('onclick','MFormHelpBox.hide()');
		modalmask.onclick = MFormHelpBox.hide;

		modalheader.appendChild(modaltitle);
		modalheader.appendChild(modalclose);
		modal.appendChild(modalheader);
		modal.appendChild(modalcontent);
		modalmask.appendChild(modal);
		document.body.appendChild(modalmask);
	}
	else {
		modal = document.getElementById('mform_help_box');
		modaltitle = document.getElementById('mform_help_box-title');
		modalcontent = document.getElementById('mform_help_box-content');
		modalmask = document.getElementById('mform_help_box-mask');
	}


	modal.style.width = width + "px";
	modalcontent.style.height = height + "px";
	modalcontent.style.overflowY = 'auto';
	modaltitle.innerHTML = title;

	MFormHelpBox.update(url);

	modalmask.style.display = 'block';
	document.body.classList.add('mform_help_modal_open');
};
	
// hide the box
MFormHelpBox.hide = function() {
	var modalmask = document.getElementById('mform_help_box-mask');
	modalmask.style.display = "none";
	document.body.classList.remove('mform_help_modal_open');
};


MFormHelpBox.getRequestObject = function(){
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

MFormHelpBox.update = function(url){
	var div = document.getElementById('mform_help_box-content');
	var requestObject = MFormHelpBox.getRequestObject();

	if(requestObject){
		requestObject.onreadystatechange = function() {
			if(requestObject.readyState == 4){
				var received = requestObject.responseText;
				div.innerHTML = received;
			}
		};
		requestObject.open("GET", url, true);
        requestObject.send();
	}
};
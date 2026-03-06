### mlib
mlib (My Librairy) is a set of php (javascript/css) classes that make some frequent task in php application programming easier.
Some of these classes, which help in building client side interface, come with a style (css and images packaged in a folder) and a javascript file.
mlib is totaly independent, it means it requires no framework or other librairy to work

mlib is distributed as 2 folders :

	- mlib/
		- mlib/				(php classes)
		- assets/mlib/		(css, javascripts and images)
 
according the framework you use or the way you use to organize your php applications, you'll have to put these folders in the right place.

### List of tools

- MConfig: a class for loading configuration files halfway between xml files and properties files
- MForm : a class for generating forms from conf files
- MFormValidator: simply check everything sent by a form
- MConsole: dashboards
- MTree : to display a tree
- MTooltip: to do a little better than the 'title' attribute
- MCaptcha: to add a captcha to a form
- MLdap : an object overlay for php's native ldap_* functions
- MMail : an easy-to-use php mailer
- MString : some additional functions for manipulating character strings
- MAjaxZone : an easy way to create a zone with an ajax content
- MFileSystem : add some php native missing operations on directories
- MMessenger : a simple way to display messages (infos, warnings, errors ...) across pages
- MCache : allow to store and retrieve easily some costing to compute objects or vars, in files
- MEncoderDecoder : a class for easy symetric or asymetric encoding/decoding strings
- MLogger : classes for logging. Could easily be extended if PSR3 compliant logger is needed
- MAutoComplete : easily display an autocomplete input with many options

### Documentation

mlib comes with a wwwsamples directory.
You can easily install wwwsamples on a webserver :
	copy wwwsamples folder somewhere on your webserver (usually under /var/www/ or /var/www/html/ on a linux distribution)
	copy mlib src folder in wwwsamples directory as "mlib"
	wwwsamples/samples/tmp/ should be writable by the webserver

Running these samples in a webbrowser gives a good overview of mlib capabilities.

Warning: wwwsamples should not be used in a production environment.


A detailed documentation is available here :

	- EN : https://lezab.github.io/myPhp/en/base/mlib/
	- FR : https://lezab.github.io/myPhp/fr/base/mlib/
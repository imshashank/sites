=== AskApache Debug Viewer ===
Contributors: askapache
Donate link: http://www.askapache.com/donate/
Tags: debug, debugging, error, errors, issue, help, warning, problem, bug, problems, support, admin, programmer, developer, plugin, development, information, stats, logs, queries, htaccess, password, error, support, askapache, apache, rewrites, server
Requires at least: 3.0
Tested up to: 4.0
Stable tag: 3.1

Extreme Advanced debugging plugin for seeing the verbose of the verbose debug info.  Tech Support, Server Admins, WordPress Developers, Plugin Developers, or anyone wanting to see under the hood of their website and diagnose problems.  This debugging plugin goes further than any other in the way it uses Apache Server Status Handlers, CGI Script for server environment view, and in the shear amount of debugging information available, like the basically print_r($GLOBALS).

== Description ==
Extreme Advanced debugging plugin for seeing the verbose of the verbose debug info.  Tech Support, Server Admins, WordPress Developers, Plugin Developers, or anyone wanting to see under the hood of their website and diagnose problems.  This debugging plugin goes further than any other in the way it uses Apache Server Status Handlers, CGI Script for server environment view, and in the shear amount of debugging information available, like the basically print_r($GLOBALS).

Read the [.htaccess Tutorial](http://www.askapache.com/htaccess/htaccess.html "AskApache .htaccess File Tutorial") for more information on the advanced Apache stuff.

Only viewable to logged-in users with the 'edit_users' capability.

A standalone plugin, you set which debug output settings you want, and whether to turn it on or off.  Then every page in your administration panel will include the debug output in the footer using the 'admin_footer' action.  Or you can also output in the wp_footer.  Additionally, it has the capability of live-debugging, which changes your php.ini error settings on-the-fly for live debugging.

These are several of the debugging modules, each can be set to basic or verbose.

 * Memory Hogs - Very cool!
 * File and Directory Browser with full stat output
 * Apache Server Status
 * Apache Server Info
 * Apache Printenv
 * Extreme Server Info with Server-Env from cgi
 * Current Variables in the Global Scope
 * Gforms Debugging
 * WordPress Cron Debugging
 * WordPress JS Script Debugging
 * WordPress CSS Styles Debugging
 * Widget Debugging
 * Sidebars Debugged
 * All Taxonomies, including custom
 * Custom Post Types
 * Navigation Menus
 * WordPress Actions and Filters
 * Function Information
 * Extensions Loaded by PHP
 * Information about Owner/User/File permissions
 * Files included by php
 * Your PHP.ini settings
 * Information from phpinfo
 * Defined Constants and variables
 * Global Server/Request Information
 * WordPress RewriteRules
 * Database Queries
 * File Permissions
 * Posix Info
 * Socket/Stream Debugging
 * Information about Loaded Classes

1. http://www.askapache.com/
2. http://www.php.net/


== Installation ==

This section describes how to install the plugin and get it working.

1. Extract zip to wp-content/plugins
2. Activate the Plugin
3. Setup plugin options


== Frequently Asked Questions ==

If you have a question about .htaccess, see: [.htaccess Tutorial](http://www.askapache.com/htaccess/htaccess.html "AskApache .htaccess File Tutorial")


== Other Notes ==

The live debugging feature is for users advanced enough to look at the code and make know what its for.


== Changelog ==

= 2.9.3 =
* Added menu icon, improved get_plugin_data

= 2.9.2 =
* Added Changelog


== Screenshots ==

1. Example Use
2. Server Status
3. Server Info
4. PHP Info
5. Server Environment CGI
6. Settings Page
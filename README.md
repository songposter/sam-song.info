SAM Broadcaster Song Info Poster v2 (2010-2015)
===============================================

Song Info Poster connects your SAM to popular social networks Facebook, Twitter and MySpace.  
The basic functionality included in both networks is sending messages to either or both platforms,  
mentioning Artist and Title that just played on your station.  

Default features for all networks include standard texts you can attach to the plain Song Info  
and automatic detection of Ads, Liners etc. which will not be announced to your followers.  

You can see the Song Info Poster in action at https://www.sam-song.info/, just sign up for one of the networks it's free.  
If you'd like to run this service on your own website / server, read this guide thoroughly and follow the instructions step by step.  

Requirements
------------

* About 10 Megs Webspace
* PHP 5.3+
* A MySQL 4.1+ database
* PHP cURL Extension (should be installed by default in most cases)
* Apache mod_rewrite and your host must allow to control this by .htaccess files

General Configuration
---------------------

In index.php you need to define the environment you are running the software in. This affects the logging and error display behavior.  
In testing and production mode, errors will not be displayed on the web.  
In development mode you will see pretty much everything and your logfiles might grow rapidly.  
Unless you want to diagnose problems and start fixing them yourself, you should change the Environment like this:
```php    
define('ENVIRONMENT', 'production');
```
Depending on which environment you configured here you will have to set up the rest of the configuration.

In application/config/config.php you will need to set the base URL by which the script can be reached from the browser.  
If you put the script into a subdirectory called songposter on your website example.com you will have to enter this:
```php    
$config['base_url']	= 'http://example.com/songposter/';
```
Pay attention to the trailing forward slash!

Next config file is for the database Setup.  
The application/config/database.php is preconfigured to run in the Zend PHPCloud. In almost any other situation you cannot grab the database connection credentials from the server config and thus need to supply it directly.
```php
$db['production']['hostname'] = get_cfg_var('zend_developer_cloud.db.host');
$db['production']['username'] = get_cfg_var('zend_developer_cloud.db.username');
$db['production']['password'] = get_cfg_var('zend_developer_cloud.db.password');
$db['production']['database'] = get_cfg_var('zend_developer_cloud.db.name');
```    
becomes:
```php
$db['production']['hostname'] = '127.0.0.1';
$db['production']['username'] = 'ubercoolwebhostuser';
$db['production']['password'] = 'supersecurepassword';
$db['production']['database'] = 'toolongtorememberdatabasename';
```
    
You need to configure the connection details for every environment seperately. If you want to use the same database in testing, development and production mode, you can just configure one and set a fixed active group:
```php    
$active_group = 'production';
```    
You can replace the MySQL Database by basically any other system, like for example Postgres or MS SQL.  
Some drivers for other database systems ship with the software, however only MySQL was tested by me.

Social Network Credentials
--------------------------

You can find templates for each of the supported networks in the application/config/default folder.  
Please do not change the templates in place, but rather create a copy.  
That copy should then be placed in a folder that resembles your environment choice from the last chapter.  
Example:
```php
application/config/production/twitter.php
```
Edit these files and supply your API Keys/Secrets/whatever. The URLs are prefilled and should be fine for the Moment. If in doubt leave them as they are.  
In the facebook config you can supply the username of your App Admin and the human readable name of your site.  
You may leave these fields empty without interfering with the Application though.  

Database Preperation
--------------------

The Song Poster application heavily relies on the database for storing user accounts and preferences.  
The structure of the database is supplied as an SQL File.  
Before working with the app for the first time you need to play that file back into the database configured in the first chapter.  
If everything's configured right, the application will just add users to the database as they come by and register.  
You can enable Page Posting on Facebook by turning the ispage flag for any user from 0 to 1.  
The next time the user logs into the Settings page he/she will be able to choose from a list of Facebook Fanpages he/she is admin of.

Adjusting the PAL Template
--------------------------

In the root directory of the app there's a file called pal_template.txt. The server creates files for each user and network based on this.
Line 125 needs to be adjusted to match your setup. Make sure to keep the Variable parts beginning with /** and ending in **/ intact.  
Example:
```php
getStr := 'http://dev.sam-song.info//**FB_TWEET**//'
```
becomes:
```php
getStr := 'http://example.com/songposter//**FB_TWEET**//'
```
Pay special attention to the number of slashes, in this particular situation AND ONLY HERE there must be 2 slashes in front of and behind the FB_TWEET pattern!


Customization
-------------

You can adjust the layout of the whole service by modifying the HTML templates in application/views.  
The landing page is called entry_view.php and the footer that stays the same for all pages is outsourced.  
Icons and Logos used in the current layout can be found in the images directory.  
Some fancy stuff in the settings pages makes use of JavaScript code found in the js folder.  
Last but not least you may replace the license.txt and favicon.ico  
(A Favicon is the miniature Logo that usually appears next to the URL in the address bar of your browser)

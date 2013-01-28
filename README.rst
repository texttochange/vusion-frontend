Vusion Frontend 
===============

Vusion Frontend is build with CakePHP2.0.5.  

Installation
------------
::

	$ git clone <this repository>
	# Then retrive the Plugins and the Backend
	$ git submodule init
	$ git submodule update

You need to add the following folders
::
	$ mkdir app/tmp/cache
	$ mkdir app/tmp/cache/persistent
	$ mkdir app/tmp/cache/model

You must change the permissions of the cache folder and it's subfolders to use www-data user
who is the apache user.
::
	$ chown -R www-data app/tmp/cache

Web Server Configuration
------------------------
You need to configure you webserver according to cakephp2.x requirements. 
First the DocumentRoot pointing at the app/webroot folder. 
Second make app/tmp file writable by the webserver.    

Databases
---------
Vusion is using 2 database engines. 
The first one is the Relational Database for authentication, Access Control List, User management. The default relational database is PostGres, but anyother can be used by modifying **app/config/database.php**. 
The second one is the Document Database MongoDB  for the business data.
(installation of MongoDB version2.x Server) http://www.mongodb.org/display/DOCS/Quickstart

Relational Database Configuration:
You can create the relational database schema from file **app/Config/Schema/schema.php** with the cake console
::
	$ ./lib/Cake/Console/cake schema create
	
If file schema.php is not found, you can also create the database using Mysql by importing a file **app/Config/Schema/schema.sql** with phpmyadmin tool.
::
	1.On your phpmyadmin home go to more tab and in the drop dpwn select import
	2.Browse the file you went to import in this case schema.sql 
	3.Tick the checkbox with donot auto increment and press go
	
Create a userLogin and password in the Mysql account database which must correspond to ones in the **app/Config/database.php** 
::
	1.On your phpmyadmin home go to phpmyadmin tab 
	2.Click on add a new user
	3.Feelin the infromation but on Host select local and Global privileges check all then press go

PHP Modules
-----------
Modules need to be install and configure in PHP
 
- MongoDB PHP Driver v1.2.9 (https://github.com/mongodb/mongo-php-driver/tags) 

Development PHP Modules
----------------------- 

- Pear
- PHPUnit

Jenkins
-------
To run the different build task from build.xml, you need to install

- Jdk6
- Ant

Apache configuration for mod_xsendfile(export)
--------------------------------
You need to first install apache2-prefork-dev
::
  $ sudo apt-get install apache2-prefork-dev

Then you clone the mod_xsendfile file from github
::
	$ git clone http://github.com/nmaier/mod_xsendfile /opt/mod_xsendfile 

**Note /opt/mod_xsendfile is destination whereyou are storing the cloned file **

Compile the file you have cloned. Run this command in the mod_xsednfile directory, in our case */opt/mod_xsendfile* 
::
 	$apxs2 -cia mod_xsendfile.c


Add this line **XSendFilePath <documentroot>/files/programs/** inside your apache configuration for virtual hosts :
 if you're using Lamp server add it in *httpd.config*
 otherwise, add it in * /etc/apache/sites-available/default *

Don't forget to change permissions on the */files/programs/ * directory

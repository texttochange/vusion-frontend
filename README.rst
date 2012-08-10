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

Web Server Configuration
------------------------
You need to configure you webserver according to cakephp2.x requirements. 
First the DocumentRoot pointing at the app/webroot folder. 
Second make app/tmp file writable by the webserver.    

Databases
---------
Vusion is using 2 database engines. 
The first one is the Relational Database for authentication, Access Control List, User management. The default relational database is PostGres, but anyother can be used by modifying app/config/database.php. 
The second one is the Document Database MongoDB for the business data.
(installation of MongoDB Server) http://www.mongodb.org/display/DOCS/Quickstart

Relational Database Configuration:
You can create the relational database schema from file app/Config/Schema/schema.php with the cake console 
./lib/Cake/Console/cake schema create

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

Vusion Frontend 
===============

Vusion Frontend is build with CakePHP2.0.5. It doesn't work on his own but need the Vusion Backend: https://github.com/texttochange/vusion-backend 

Installation
------------
::

	$ git clone <this repository>
	$ git submodule init
	$ git submodule update

Web Server Configuration
------------------------
You need to configure you webserver with the DocumentRoot pointing at the app/ folder.

Create the cache file as
::

	app/
		tmp/
		cache/
    			models/
    			persistent/
    		logs/
    		session/
    		tests/

Then make those file writable by the webserver. 
Under ubuntu: sudo chmod -R 777 app/tmp   

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
- XDebug
- Mongo (https://github.com/ichikaway/cakephp-mongodb/tree/cake2.0)
- phploc (https://github.com/sebastianbergmann/phploc)
- pdepend (http://pear.pdepend.org/)
- phpmd (http://pear.phpmd.org/)
- phpcpd (https://github.com/sebastianbergmann/phpcpd)
- phpdoc (http://sourceforge.net/projects/phpdocu/files/)

Jenkins
-------
To run the different build task from build.xml, you need to install

- Jdk6 or higher
- Ant

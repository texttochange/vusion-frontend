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


You need to add the following folders: persistent & model under dir app/tmp/cache
::

	$ mkdir app/tmp/cache
	$ mkdir app/tmp/cache/persistent
	$ mkdir app/tmp/cache/model

You must change the permissions of the cache folder and it's subfolders to use www-data user
who is the apache user.

	$ chown -R www-data app/tmp/cache
	
	
Required Tools to Install
-------------------------
    ::

	$ sudo apt-get install apache2
	$ sudo apt-get install mongoDB
	$ sudo apt-get install mysql-server
	$ sudo apt-get install php

**Note: phpmyadmin to work may require you to configure the apache2.conf file by including this line: include /etc/phpmyadmin/apache.conf at the bottom.**



PHP Modules
-----------
Modules need to be install and configure in PHP

:MongoDB PHP:
    - Download the .tar.gz file of MongoDB PHP Driver **v1.2.9** (https://github.com/mongodb/mongo-php-driver/tags)
    - Open a terminal
    ::

        $ tar zxvf mongodb-php-driver-1.2.9.tar.gz
        $ cd mongodb-php-driver-1.2.9
        $ sudo apt-get install php5-dev
        $ phpize
        $ ./configure
        $ make all
        $ sudo make install

:Redis PHP:
    - Clone the Git repo git clone git://github.com/nicolasff/phpredis.git
    - Open a terminal
    ::
        
        $ phpize && ./configure && make && sudo make install



Development PHP Modules
----------------------- 

- Pear and PHPUnit Installation
    ::
      
    	$ sudo apt-get install php-pear
    	$ sudo pear channel-discover pear.phpunit.de
        $ sudo pear channel-discover components.ez.no
        $ sudo pear channel-discover pear.symfony-project.com
        $ sudo pear channel-discover pear.symfony.com
        $ sudo pear update-channels
        $ sudo pear install --alldeps phpunit/PHPUnit
        
 
 **- Note if PHPUnit fails, first upgrade pear with the command below**
 $ pear upgrade pear
 
 - try the PHPUnit again
 $ sudo pear install --alldeps phpunit/PHPUnit
 
 
Jenkins
-------
To run the different build task from build.xml, you need to install

- Jdk6
sudo apt-get install openjdk-6-jre;

- Ant
sudo apt-get install -u ant; or sudo apt-get install ant;


    

Databases
---------
Vusion is using 2 database engines. 
The first one is the Relational Database for authentication, Access Control List, User management. The default relational database is PostGres, but anyother can be used by modifying **app/config/database.php**. 
The second one is the Document Database MongoDB  for the business data.
(installation of MongoDB version2.x Server) http://www.mongodb.org/display/DOCS/Quickstart

Relational Database Configuration:
You can create the relational database schema from file **app/Config/Schema/schema.php** with the cake console

	$ ./lib/Cake/Console/cake schema create
	
If file schema.php is not found, you can also create the database using Mysql by importing a file **app/Config/Schema/schema.sql** with phpmyadmin tool.


::

	1.On your phpmyadmin home go to more tab and in the drop dpwn select import
	2.Browse the file you went to import in this case schema.sql 
	3.Tick the checkbox with donot auto increment and press go


or in the mysql console type "mysql -u root-p < app/Config/Schema/schema.sql"

	
Create a userLogin and password in the Mysql account database which must correspond to ones in the **app/Config/database.php** 

::

	1.On your phpmyadmin home go to phpmyadmin tab 
	2.Click on add a new user
	3.Feelin the infromation but on Host select local and Global privileges check all then press go

while in the mysql console,navigate to to users table and create two users; "cake" and "cake_test" and grant all privileges to these users by issuing the commands below

::

         1.GRANT ALL PRIVILEGES ON *.* TO 'cake'@'localhost' IDENTIFIED BY 'password';
         2.GRANT ALL PRIVILEGES ON *.* TO 'cake_test'@'localhost' IDENTIFIED BY 'password';

Run vusion.sql
        mysql -u root -p < app/Test/data/mySQL/vusion.sql

 
        

Web Server Configuration
------------------------
You need to configure you webserver according to cakephp2.x requirements. 
First the DocumentRoot pointing at the app/webroot folder. 
Second make app/tmp file writable by the webserver.        


Apache configuration for mod_xsendfile(export)
--------------------------------
You need to first install apache2-prefork-dev

  $ sudo apt-get install apache2-prefork-dev

Then you clone the mod_xsendfile file from github

	$ git clone http://github.com/nmaier/mod_xsendfile /opt/mod_xsendfile 

**Note /opt/mod_xsendfile is destination whereyou are storing the cloned file **

Compile the file you have cloned. Run this command in the mod_xsednfile directory, in our case */opt/mod_xsendfile* 

 	$apxs2 -cia mod_xsendfile.c


Add this line **XSendFilePath <documentroot>/files/programs/** inside your apache configuration for virtual hosts

	if you're using Lamp server add it in **httpd.config**.
 	otherwise, add it in ** /etc/apache/sites-available/default **

Don't forget to change permissions on the */files/programs/ * directory

**Note: We also want apache www folder to have access to our project folder; we are going to have to create a symlink folder inside the apache www hence when project folder files are updated apache can have access to the updates. go to command -**
::
	$ ln -s /actual project folder path/ /symlink folder path in the apache www/

In the /etc/apach2/port.conf file add this listen port 
::

	NameVirtualHost *:81
	Listen 81


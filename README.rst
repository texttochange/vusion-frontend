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
        $ sudo pear install --alldeps phpunit/PHPUnit-3.7.27
        
 
 **- Note if PHPUnit fails, first upgrade pear with the command below**
 $ pear upgrade pear
 
 - try the PHPUnit again
 $ sudo pear install --alldeps phpunit/PHPUnit
 
 
Jenkins
-------
To run the different build task from build.xml, you need to install

- Jdk6
::

 $ sudo apt-get install openjdk-6-jre;

- Ant
::

  $ sudo apt-get install -u ant; or sudo apt-get install ant;


    

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

**Note /opt/mod_xsendfile is destination whereyou are storing the cloned file**

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

Developing using Vagrant and VirtualBox
=========================================
This works on all Operating Systems.
You need to install the following:
::
	Install VirtualBox
		https://www.virtualbox.org/wiki/Downloads

	Install Vagrant
		https://www.vagrantup.com/downloads.html

	Install Github
		http://git-scm.com/downloads

	Install IDE for coding

Now you have all the installation for the vusion frontend. You need now to setup where the work project 
is going to be saved in your System.


	1. Open PowerShell as admin by right clicking on the PowerShell icon and selecting "Run as Admin".

	2. Enter the followig commands in the PowerShell.
	   ::
	     $ mkdir c:\Development
	     $ cd c:\Development
			
	3. Now you are in the directory where you are going to work form so do the commands below.
	   ::
		$ git clone https://github.com/texttochange/vusion-frontend
			 Then retrive the Plugins and the Backend
		$ git submodule init
		$ git submodule update

	4. Ask for the **Vusion.box** file and add it into **c:\\Development\\vusion-frontend**

	5. Using your IDE Open and edit the vagrantfile in **"c:\\Development\\vusion-forntend\\vagrantfile"**
	   ::
	    Edit line 5: `config.vm.box_url = "file:///Users/olivier/Development/vusion/vusion2.box"` to
	    to the file location of your development directory.
	  **Note**
	    In this vagrantfile we have port forwarding between the host and guest machine(virtual machine) with `config.vm.network`
	    ::
	      http port
	    	  		 guest:80    == host:4567
	    	  		 guest:9010  == host:4568
            ::
            
              runing tests in your host environment`          
	    	 		 guest:27017 == host:27017
	    	  		 guest:6379  == host:6379
	    ::
	    
	      pushing message to the default transports         
	    	  		 guest:2221  == host:2221
	    	  		 guest:2222  == host:2223

	   We also have the synced folders between the host and guest machine(virtual machine) with ``config.vm.synced_folder`` here the ``type:nfs`` has to change to ``type:smb``, for more information about why the type changes read the link below
	    	  http://docs.vagrantup.com/v2/synced-folders/nfs.html
	    	  
	    	  http://docs.vagrantup.com/v2/synced-folders/smb.html

					    	  
	6. Run this command in the PowerShell to start Vagrant and virtualbox
	   ::
		$ vagrant up

          Enter the URL: localhost:4567 in your web browser vusion login page will show

	7. Settingup git flow to enable you create feature from branches for easy and organised development 
        a) Download and install `getopt.exe` from the [util-linux package](http://gnuwin32.sourceforge.net/packages/util-linux-ng.htm) 
           into `C:\Program Files\Git\bin`. (Only `getopt.exe`, the others util-linux files are not used). Also install `libintl3.dll` and `libiconv2.dll` from the Dependencies packages ([libintl](http://gnuwin32.sourceforge.net/packages/libintl.htm) and [libiconv](http://gnuwin32.sourceforge.net/packages/libiconv.htm)), into the same directory
       
        b) Open a new Powershell as admin and create a directory
             $ mkdir c:\Installgitflow
             $ cd c:\Installgitflow

        c) Clone the gitflow source from GitHub
             $ git clone --recursive git://github.com/nvie/gitflow.git
             $ cd gitflow\contrib

		d) Run the `msysgit-install` script from a command-line prompt 
			 $ msysgit-install 
	

Installation to run backend development and testing
===================================================

Install Python and pip
  **For windows7(or8)**
	1. Dowload the MSI installer from http://www.python.org/download/. 
	   Select 32/64 bit based on your system setting

	2. Run the installer. Be sure to check the option to add Python to your PATH while installing.

	3. Open PowerShell as admin by right clicking on the PowerShell icon and selecting ‘Run as Admin’.

	4. To solve permission issues, run the following command.
	   ::
	    
	         Set-ExecutionPolicy Unrestricted

	5. Enter the following commands in PowerShell.
           ::

		mkdir c:\envs
		cd c:\envs

	6. Download the following files into your new folder.
	
	    http://python-distribute.org/distribute_setup.py
	     
	    https://raw.github.com/pypa/pip/master/contrib/get-pip.py
	   ::
	   
	    so now you have something like : 'c:\envs\distribute_setup.py' and 'c:\envs\get-pip.py'.

	7. Run the following commands in you terminal.
	
	   ::
		     
		  python c:\envs\distribute_setup.py
		  python c:\envs\get-pip.py

           **Note: Once these commands run successfully, you can delete the scripts get-pip.py and distribute_setup.py**
	
	8. Now typing pip should work. If it doesn’t it means the Scripts folder is not in your path. 
	   Run the next command in that case 
	   (Note that this command must be run only once or your PATH will get longer and longer).
	   Make sure to replace c:\Python27\Scripts with the correct location of your Python installation
	   ::
	   
               setx PATH "%PATH%;C:\Python27\Scripts"

           Close and reopen PowerShell after running this command.
           
    9. To create a Virtual Environment, use the following commands.
        
        ::
             
		cd c:\python
		pip install virtualenv
		pip install –no-deps -r requirements.pip
		
           Note: If you have varasall.bat fill missing please install visual studio C+++
		::
		   
		   If you have Visual Studio 2010 installed, execute
			SET VS90COMNTOOLS=%VS100COMNTOOLS%
		   or with Visual Studio 2012 installed (Visual Studio Version 11)
			SET VS90COMNTOOLS=%VS110COMNTOOLS%
                   or with Visual Studio 2013 installed (Visual Studio Version 12)
			SET VS90COMNTOOLS=%VS120COMNTOOLS%

	10. To run the virtual Environment and backend tests.
	
	    ::
	      
		 virtualenv ve
		 .\ve\Scripts\activate
		 python  ve\Scripts\trial.phy  vusion
		
		



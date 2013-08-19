This file intend to keep track of the different code debt related to our plugin

==== MongoDB ==== 
Vusion is currently working on MongoDB 2.2.3

==== MongoDB Plugin for CakePHP ====
Vusion is currently using a specific commit version of this plugin
https://github.com/ichikaway/cakephp-mongodb/tree/f3131b3224246c1abe782f51aaa421402aa50f1f

Initially this version was include as a submodule into Vusion repo.
Due to some limit in the management of timeout, we had to modify the MongodbSource and so we removed our submodule link.
Now a plugin copy is include into Vusion repo.

Note that this plugin is using a deprecated class of the MongoDB Driver that prevent us to upgrade to >= 1.3

TODO if updating CakePHP to >= 2.2.5 and so the 
=> make sure that the query() is passing the $param to command() as a second argument
=> make sure that the mapreduce() is passing the timeout parameter as a second parameter of the query()  

==== MongoDB Driver for PHP ====
Vusion is using the 1.2.9 version of the driver.
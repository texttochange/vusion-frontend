#!/bin/bash

/opt/lampp/bin/mysql -u jenkins < ./app/Test/data/mySQL/droptables.sql
/opt/lampp/bin/mysql -u jenkins < ./app/Test/data/mySQL/vusion.sql

bash ./app/Test/data/mongoDB/restore_mongo.sh

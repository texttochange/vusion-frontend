#!/bin/bash

path=./app/Test/data/mongoDB

#Drop all databases
mongo $path/dropAll.js

#general databases
general_databases=('shortcodes')

#program specific
specific_databases=('m4h' 'mrs')

for database_name in ${general_databases[@]}
do
	echo $path/$database_name/$database_name.json
	mongoimport -drop -d $database_name -c $database_name $path/$database_name/$database_name.json
done

for database_name in ${specific_databases[@]}
do
	echo $path/$database_name/participants.json
	mongoimport -drop -d $database_name -c participants $path/$database_name/participants.json
	echo /$database_name/scripts.json
	mongoimport -drop -d $database_name -c scripts $path/$database_name/scripts.json
	echo /$database_name/schedules.json
	mongoimport -drop -d $database_name -c schedules $path/$database_name/schedules.json
	echo /$database_name/status.json
	mongoimport -drop -d $database_name -c status $path/$database_name/status.json
	echo /$database_name/program_settings.json
	mongoimport -drop -d $database_name -c program_settings $path/$database_name/program_settings.json
	
done

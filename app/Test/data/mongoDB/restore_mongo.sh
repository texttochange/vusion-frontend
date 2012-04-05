#!/bin/bash

path=./app/Test/data/mongoDB

#Drop all databases
mongo $path/dropAll.js

#main database
main_database=('vusion')

#general tables
main_tables=('shortcodes' 'unmatchable_reply')

#program specific
specific_databases=('m4h' 'mrs' 'wiki')

for main_table in ${main_tables[@]}
do
	echo $path/$main_database/$main_table.json
	mongoimport -drop -d $main_database -c $main_table $path/$main_database/$main_table.json
done

for database_name in ${specific_databases[@]}
do
	echo $path/$database_name/participants.json
	mongoimport -drop -d $database_name -c participants $path/$database_name/participants.json
	echo /$database_name/scripts.json
	mongoimport -drop -d $database_name -c scripts $path/$database_name/scripts.json
	echo /$database_name/schedules.json
	mongoimport -drop -d $database_name -c schedules $path/$database_name/schedules.json
	echo /$database_name/history.json
	mongoimport -drop -d $database_name -c history $path/$database_name/history.json
	echo /$database_name/program_settings.json
	mongoimport -drop -d $database_name -c program_settings $path/$database_name/program_settings.json
	
done

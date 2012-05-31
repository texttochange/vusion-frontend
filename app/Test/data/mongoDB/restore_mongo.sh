#!/bin/bash

path=./app/Test/data/mongoDB

#Drop all databases
mongo $path/dropAll.js

#main database
main_database=('vusion')

#general tables
main_tables=('shortcodes' 'unmatchable_reply' 'templates')

#program specific database
specific_databases=('m4h' 'mrs' 'wiki')

#program specific tables
specific_tables=('participants' 'dialogues' 'schedules' 'history' 'program_settings' 'requests')

for main_table in ${main_tables[@]}
do
	echo $path/$main_database/$main_table.json
	mongoimport -drop -d $main_database -c $main_table $path/$main_database/$main_table.json
done

for database_name in ${specific_databases[@]}
do
	for table_name in ${specific_tables[@]}
	do
		echo $path/$database_name/$table_name.json
		mongoimport -drop -d $database_name -c $table_name $path/$database_name/$table_name.json
	done	
done

#!/bin/bash

path=./app/Test/data/mongoDB

#program specific tables
specific_tables=('participants' 'dialogues' 'schedules' 'history' 'program_settings' 'requests')

for database_name in "$@"
do
	for table_name in ${specific_tables[@]}
	do
		echo $path/$database_name/$table_name.json
		mongoimport -drop -d $database_name -c $table_name $path/$database_name/$table_name.json
	done	
done
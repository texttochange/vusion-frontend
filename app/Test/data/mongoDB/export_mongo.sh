#!/bin/bash

#main database
main_database=('vusion')

#general tables
main_tables=('shortcodes' 'unmatchable_reply', 'templates')

#program specific
specific_databases=('m4rh' 'mrs' 'c4c')

#program specific tables
specific_tables=('participants' 'dialogues' 'schedules' 'history' 'program_settings' 'requests')

for main_table in ${main_tables[@]}
do
	echo $main_database/$main_table.json
	mongoexport -d $main_database -c $main_table -o $main_database/$main_table.json
done

for database_name in ${specific_databases[@]}
do
	for table_name in ${specific_tables[@]}
	do
			echo /$database_name/$table_name.json
			mongoexport -d $database_name -c $table_name -o $database_name/$table_name.json
	done
done

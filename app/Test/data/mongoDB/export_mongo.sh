#!/bin/bash

#main database
main_database=('vusion')

#general tables
main_tables=('shortcodes' 'unmatchable_reply')

#program specific
specific_databases=('m4h' 'mrs' 'wiki')

for main_table in ${main_tables[@]}
do
	echo $main_database/$main_table.json
	mongoexport -d $main_database -c $main_table -o $main_database/$main_table.json
done

for database_name in ${specific_databases[@]}
do
	echo /$database_name/participants.json
	mongoexport -d $database_name -c participants -o $database_name/participants.json
	echo /$database_name/scripts.json
	mongoexport -d $database_name -c scripts -o $database_name/scripts.json
	echo /$database_name/schedules.json
	mongoexport -d $database_name -c schedules -o $database_name/schedules.json
	echo /$database_name/history.json
	mongoexport -d $database_name -c history -o $database_name/history.json
	echo /$database_name/program_settings.json
	mongoexport -d $database_name -c program_settings -o $database_name/program_settings.json
done

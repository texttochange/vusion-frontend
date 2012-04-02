#!/bin/bash

#general databases
general_databases=('shortcodes' 'unmatchable_reply')

#program specific
specific_databases=('m4h' 'mrs' 'wiki')

for database_name in ${general_databases[@]}
do
	echo $database_name/$database_name.json
	mongoexport -d $database_name -c $database_name -o $database_name/$database_name.json
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

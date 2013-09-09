#!/bin/bash

path=./app/Test/data/mongoDB

#Drop all databases
mongo $path/dropAll.js

#main database
main_database=('vusion')

#general tables
main_tables=('shortcodes' 'unmatchable_reply' 'templates')


for main_table in ${main_tables[@]}
do
	echo $path/$main_database/$main_table.json
	mongoimport -drop -d $main_database -c $main_table $path/$main_database/$main_table.json
done

bash ./app/Test/data/mongoDB/import_program_db.sh m4rh mrs c4c



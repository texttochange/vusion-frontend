var myDatabases = db.adminCommand('listDatabases')

for (var i=0; i < myDatabases['databases'].length; i++)
{
	db = db.getMongo().getDB(myDatabases['databases'][i]['name'])
	print('Dropping: '+db.getName());
	db.dropDatabase();
}

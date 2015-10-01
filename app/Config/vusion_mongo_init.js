var conn = new Mongo("localhost:27017");
//This works as "use " command in mongo shell
db = db.getSiblingDB('vusion');

printjson('Connecting to mongo vusion database ..... ');

var userLog = db.getCollection('user_logs').exists();
if (!this.userLog) {
    printjson('Creating user_logs collection into vusion database ');
    
    db.createCollection('user_logs');
}
printjson('Creating an index on timestamp in the user_logs collection');

db.user_logs.ensureIndex({'timestamp': 1});

printjson('Index added ');
printjson(db.user_logs.getIndexes());

var conn = new Mongo("localhost:27017");
//This works as "use " command in mongo shell
db = db.getSiblingDB('vusion');

var userLog = db.getCollection('user_logs').exists();
if (!this.userLog) {
    db.createCollection('user_logs');
}
db.user_logs.ensureIndex({'timestamp': 1});

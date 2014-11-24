var conn = new Mongo("localhost:27017");
db       = conn.getDB('vusion');

db.getSiblingDB('vusion2');

var userLog = db.getCollection('user_logs').exists();
if (!this.userLog) {
    db.createCollection('user_logs');
}
db.user_logs.ensureIndex({'timestamp': 1});

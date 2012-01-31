<?php class MongoModel extends Model {
    var $specific = false;

    public function __construct($id = false, $table = null, $ds = null) {
    	echo "Hello from constructed model";
        if ($this->specific) {
            // Get saved company/database name
            $dbName = Configure::read('programDB');
            // Get common company-specific config (default settings in database.php)
            $config = ConnectionManager::getDataSource('mongo')->config;

            // Set correct database name
            $config['database'] = $dbName;
            // Add new config to registry
            ConnectionManager::create($dbName, $config);
            // Point model to new config
            $this->useDbConfig = $dbName;
        }
        parent::__construct($id, $table, $ds);
    }

}?> 

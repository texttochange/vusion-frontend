<?php 
App::uses('Model', 'Model');
App::uses('MongoDbSource', 'MongoDb.Model/Datasource');

class MongoModel extends Model {
    var $specific = false;

    public function __construct($id = false, $table = null, $ds = null) {
    	//echo "Construct Model -";
    	//print_r($id);
        if ($this->specific) {
            // Get saved company/database name
            $dbName = $id['database'];
            // Get common company-specific config (default settings in database.php)
            //$mongodb = new MongodbSource();
            
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

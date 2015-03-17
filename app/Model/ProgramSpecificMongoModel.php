<?php
App::uses('MongoModel', 'Model');


abstract class ProgramSpecificMongoModel extends MongoModel
{

	var $specific = true;
	var $useDbConfig = 'mongo_program_specific';
	var $databaseName = null;
    var $contactEmail = null;


    public function __construct($id = false, $table = null, $ds = null)
    {
        if (isset($id['ds']) && $id['ds'] == 'test_mongo_program_specific') {
            unset($id['ds']);  ## fix for the test to avoid using this userDbConfig
        }
        if (isset($id['database']) and $id['database']) {
            $dbName = $id['database'];
        } else if (isset($id['id']['database'])) {
            $dbName = $id['id']['database'];
            unset($id['id']['database']);
        } 
        if (isset($dbName)) {
            $this->setDatabase($dbName);
        }
        parent::__construct($id, $table, $ds);
    }


 	static function init($className, $databaseName, $forceNew=false) {
        if (!$forceNew) {
            $model = ClassRegistry::init(array(
            	'class' => $className,
            	'id' => array('database' => $databaseName)));
        } else {
            $model = new $className();
            $model->setDatabase($databaseName, $forceNew);
        }
        return $model;
    }	


	public function setDatabase($databaseName, $forceNew=false) 
    {
        if ($databaseName == null || $databaseName == "") {
            throw new Exception("empty databasename");
        }
        if ($this->databaseName == $databaseName) {
            return;
        }
        $this->databaseName = $databaseName;
        $config = ConnectionManager::getDataSource($this->useDbConfig)->config;
        // Set correct database name
        $config['database'] = $databaseName;
        // Add new config to registry
        ConnectionManager::create($databaseName, $config);
        // Point model to new config
        $this->useDbConfig = $databaseName;
        $this->initializeDynamicTable($forceNew);
    }


    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
    }


	public function initializeDynamicTable($forceNew=false)
    {}


}
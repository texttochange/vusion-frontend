<?php 
App::uses('AppModel', 'Model');
App::uses('MongoDbSource', 'MongoDb.Model/Datasource');
App::uses('MongoModelValidator', 'Model');


abstract class MongoModel extends AppModel
{
    
    //var $specific     = false;
    //var $databaseName = null; 
    var $useDbConfig = 'vusion';   
    
    var $mongoFields = array(
        '_id',
        'modified',
        'created'
        );
    
    var $vusionFields = array(
        'model-version',
        'object-type'
        );
    
    
    abstract function getModelVersion();
    
    
    abstract function getRequiredFields($objectType);


    /*static function initModelDynamicDB($className, $databaseName, $forceNew=false) {
        if (!$forceNew) {
            $model = ClassRegistry::init($className);
        } else {
            $model = new $className();
        }
        $model->setDatabase($databaseName);
        return $model;
    }*/


    public function __construct($id = false, $table = null, $ds = null)
    {
        /*if (isset($id['ds']) && $id['ds'] == 'test_mongo_program_specific') {
            throw new Exception("problem");
        }*/
        //print_r($table);
        //print_r($ds);
        $this->defaultFields = array_merge($this->vusionFields, $this->mongoFields);
        /*
        if ($this->specific) {
            // Get saved company/database name
            if (isset($id['database']) and $id['database']) {
                $dbName = $id['database'];
            } else if (isset($id['id']['database'])) {
                $dbName = $id['id']['database'];
                unset($id['id']['database']);
            } 
            
            // Get common company-specific config (default settings in database.php)
            //$mongodb = new MongodbSource();
            
            //echo "Mongo Class is Construct ".$dbName;
            /*
            $config = ConnectionManager::getDataSource('mongo')->config;
            
            // Set correct database name
            $config['database'] = $dbName;
            $this->databaseName = $dbName;
            // Add new config to registry
            ConnectionManager::create($dbName, $config);
            // Point model to new config
            $this->useDbConfig = $dbName;
            $this->initializeDynamicTable();*/
          /*  if (isset($dbName)) {
                echo $this->name." instanciate directly with db $dbName\n";
                $this->setDatabase($dbName);
            }*/
            //$table=false;
        /*} else {
            if (Configure::check("test_mongo_db")) {
                $dbName = Configure::read("test_mongo_db");                
                echo $this->name." instanciate directly with db $dbName\n";
                $this->setDatabase($dbName);   
            } else {
                echo $this->name." instanciate directly with db vusion\n";
                $this->setDatabase('vusion');
            }
        }*/
        parent::__construct($id, $table, $ds);
        $this->validator(new MongoModelValidator($this));
    }

    /*
    public function setDatabase($databaseName) 
    {
        if ($databaseName == null || $databaseName == "") {
            throw new Exception("empty databasename");
        }
        echo $this->name."->setDatabase($databaseName)\n";
        if ($this->databaseName == $databaseName) {
            //echo $this->name." already set abord\n";
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
        $this->initializeDynamicTable();
    }*/

    /*public function initializeDynamicTable()
    {
        echo $this->name."->initializeDynamicTable\n";
    }*/

    
    public function checkFields($object)
    {        
        if (isset($object['object-type'])) {
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields($object['object-type']));
        } else {
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields());
        }
        
        foreach ($object as $field => $value) {
            if (!in_array($field, $toCheck)) {
                unset($object[$field]);
            }
        }
        
        foreach ($toCheck as $field) {
            if (!isset($object[$field])) {
                $object[$field] = null;
            }
        };
        
        return $object;
    }
    
    
    public function create($objectType=null, $createDefaultFields=true)
    {
        parent::create();
        
        if (!$createDefaultFields) {
            return;
        }
        $toCreate = array_merge($this->defaultFields, $this->getRequiredFields($objectType));
        
        foreach ($toCreate as $field) {
            if (!isset($object[$field])) {
                $this->data[$this->alias][$field] = null;
            }
        };
        $this->data[$this->alias]['model-version'] = $this->getModelVersion();
        if ($objectType==null) {
            $this->data[$this->alias]['object-type'] = strtolower($this->name);  
        } else {
            $this->data[$this->alias]['object-type'] = $objectType;
        }
    }
    
    
    public function beforeValidate()
    {
        $this->data[$this->alias] = $this->checkFields($this->data[$this->alias]);
        $this->data[$this->alias]['model-version'] = $this->getModelVersion();
        $this->data = $this->_trim_array($this->data);
        return true;
    }
    
    
    public function _trim_array($document)
    {
        if (!is_array($document)) {
            if (is_string($document)) {
                $document = trim(stripcslashes($document));
            }
            return $document;
        }
        foreach ($document as &$element) {
            $element = $this->_trim_array($element);
        }
        return $document;
    }


    public function isVeryUnique($check)
    {
        $key = array_keys($check);
        $conditions = array($key[0] => $check[$key[0]]);
        
        if ($this->id) {
            $conditions['id'] = array('$ne'=> $this->id);
        }
        $result = $this->find(
            'count', array('conditions' => $conditions)
            );
        return $result < 1;
    }
    
    
    protected function _setDefault($field, $default)
    {
        if (!isset($this->data[$this->alias][$field])) {
            $this->data[$this->alias][$field] = $default;
        } 
    }

    
    function beforeSave($option = array())
    {
        $this->data[$this->alias]['modified'] = new MongoDate(strtotime('now'));
        return true;
    }
    
    
}
?> 

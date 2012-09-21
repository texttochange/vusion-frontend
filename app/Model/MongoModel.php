<?php 
App::uses('Model', 'Model');
App::uses('MongoDbSource', 'MongoDb.Model/Datasource');


abstract class MongoModel extends Model
{
    abstract function getModelVersion();
    abstract function getRequiredFields($objectType);
    
    var $specific = false;

    var $mongoFields = array(
        '_id',
        'modified',
        'created'
        );

    var $vusionFields = array(
        'model-version',
        'object-type'
        );

    public function __construct($id = false, $table = null, $ds = null)
    {

        $this->defaultFields = array_merge($this->vusionFields, $this->mongoFields);

        //echo "Construct Model -";
        //print_r($id);
        if ($this->specific) {
            // Get saved company/database name
            if (isset($id['database']) and $id['database']) {
                    $dbName = $id['database'];
            } else {
                    $dbName = 'test';
            }
      
            // Get common company-specific config (default settings in database.php)
            //$mongodb = new MongodbSource();
            
            //echo "Mongo Class is Construct ".$dbName;
            
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


    public function checkFields($object)
    {        
        if (isset($object['object-type']))
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields($object['object-type']));
        else
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields());
       
        foreach ($object as $field => $value) {
            if (!in_array($field, $toCheck)){
                unset($object[$field]);
            }
        }

        foreach ($toCheck as $field) {
            if (!isset($object[$field])){
                $object[$field] = null;
            }
        };

        return $object;
    }


    public function create($objectType=null)
    {
        parent::create();
        
        $toCreate = array_merge($this->defaultFields, $this->getRequiredFields($objectType));
        
        foreach ($toCreate as $field) {
            if (!isset($object[$field])){
                $this->data[$this->alias][$field] = null;
            }
        };
        $this->data[$this->alias]['model-version'] = $this->getModelVersion();
        $this->data[$this->alias]['object-type'] = $objectType;
    }

    
    public function beforeValidate()
    {
        $this->data[$this->alias] = $this->checkFields($this->data[$this->alias]);
        return true;
    }
    
    
    public function isVeryUnique($check)
    {
        $key = array_keys($check);
        $conditions = array($key[0] => $check[$key[0]]);
        
        if ($this->id) {
            $conditions['id'] = array('$ne'=> $this->id);
        }
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;
    }


}
?> 

<?php 
App::uses('Model', 'Model');
App::uses('MongoDbSource', 'MongoDb.Model/Datasource');


abstract class MongoModel extends Model
{
    
    var $specific     = false;
    var $databaseName = null; 
    var $useDbConfig = 'mongo';   
    
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
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        
        $this->defaultFields = array_merge($this->vusionFields, $this->mongoFields);
        
        if ($this->specific) {
            // Get saved company/database name
            if (isset($id['database']) and $id['database']) {
                $dbName = $id['database'];
            } else if (isset($id['id']['database'])) {
                $dbName = $id['id']['database'];
                unset($id['id']['database']);
            } else {
                $dbName = 'mongo-test';
            }
            
            // Get common company-specific config (default settings in database.php)
            //$mongodb = new MongodbSource();
            
            //echo "Mongo Class is Construct ".$dbName;
            
            $config = ConnectionManager::getDataSource('mongo')->config;
            
            // Set correct database name
            $config['database'] = $dbName;
            $this->databaseName = $dbName;
            // Add new config to registry
            ConnectionManager::create($dbName, $config);
            // Point model to new config
            $this->useDbConfig = $dbName;
        }
        parent::__construct($id, $table, $ds);
    }
    
    
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
    
    
    # Need to overwrite to avoid validation error message to be written
    public function invalidate($field, $value = true) 
    {
        if ($value == 'noMessage') {
            return;
        }
        if (!is_array($this->validationErrors)) {
            $this->validationErrors = array();
        }
        $this->validationErrors[$field] []= $value;
    }
    
    function beforeSave($option = array())
    {
        $this->data[$this->alias]['modified'] = new MongoDate(strtotime('now'));
        return true;
    }
    
    
}
?> 

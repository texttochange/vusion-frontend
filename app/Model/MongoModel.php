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
                    $dbName = 'mongo-test';
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
        foreach($document as &$element) {
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
        $result = $this->find('count', array(
            'conditions' => $conditions
            ));
        return $result < 1;
    }


    # Need to be overwrite to take into accound array field in mongo
    public function save($data = null, $validate = true, $fieldList = array()) {
		$defaults = array('validate' => true, 'fieldList' => array(), 'callbacks' => true);
		$_whitelist = $this->whitelist;
		$fields = array();

		if (!is_array($validate)) {
			$options = array_merge($defaults, compact('validate', 'fieldList', 'callbacks'));
		} else {
			$options = array_merge($defaults, $validate);
		}

		if (!empty($options['fieldList'])) {
			$this->whitelist = $options['fieldList'];
		} elseif ($options['fieldList'] === null) {
			$this->whitelist = array();
		}
		$this->set($data);

		if (empty($this->data) && !$this->hasField(array('created', 'updated', 'modified'))) {
			return false;
		}

		foreach (array('created', 'updated', 'modified') as $field) {
			$keyPresentAndEmpty = (
				isset($this->data[$this->alias]) &&
				array_key_exists($field, $this->data[$this->alias]) &&
				$this->data[$this->alias][$field] === null
			);
			if ($keyPresentAndEmpty) {
				unset($this->data[$this->alias][$field]);
			}
		}

		$exists = $this->exists();
		$dateFields = array('modified', 'updated');

		if (!$exists) {
			$dateFields[] = 'created';
		}
		if (isset($this->data[$this->alias])) {
			$fields = array_keys($this->data[$this->alias]);
		}
		if ($options['validate'] && !$this->validates($options)) {
			$this->whitelist = $_whitelist;
			return false;
		}

		$db = $this->getDataSource();

		foreach ($dateFields as $updateCol) {
			if ($this->hasField($updateCol) && !in_array($updateCol, $fields)) {
				$default = array('formatter' => 'date');
				$colType = array_merge($default, $db->columns[$this->getColumnType($updateCol)]);
				if (!array_key_exists('format', $colType)) {
					$time = strtotime('now');
				} else {
					$time = $colType['formatter']($colType['format']);
				}
				if (!empty($this->whitelist)) {
					$this->whitelist[] = $updateCol;
				}
				$this->set($updateCol, $time);
			}
		}

		if ($options['callbacks'] === true || $options['callbacks'] === 'before') {
			$result = $this->Behaviors->trigger('beforeSave', array(&$this, $options), array(
				'break' => true, 'breakOn' => array(false, null)
			));
			if (!$result || !$this->beforeSave($options)) {
				$this->whitelist = $_whitelist;
				return false;
			}
		}

		if (empty($this->data[$this->alias][$this->primaryKey])) {
			unset($this->data[$this->alias][$this->primaryKey]);
		}
		$fields = $values = array();

		foreach ($this->data as $n => $v) {
			if (isset($this->hasAndBelongsToMany[$n])) {
				if (isset($v[$n])) {
					$v = $v[$n];
				}
				$joined[$n] = $v;
			} else {
				if ($n === $this->alias) {
					foreach (array('created', 'updated', 'modified') as $field) {
						if (array_key_exists($field, $v) && empty($v[$field])) {
							unset($v[$field]);
						}
					}

					foreach ($v as $x => $y) {
						if ($this->hasField($x) && (empty($this->whitelist) || in_array($x, $this->whitelist))) {
							list($fields[], $values[]) = array($x, $y);
						}
					}
				}
			}
		}
		$count = count($fields);

		if (!$exists && $count > 0) {
			$this->id = false;
		}
		$success = true;
		$created = false;

		if ($count > 0) {
			$cache = $this->_prepareUpdateFields(array_combine($fields, $values));

			if (!empty($this->id)) {
				$success = (bool)$db->update($this, $fields, $values);
			} else {
				$fInfo = $this->schema($this->primaryKey);
				$isUUID = ($fInfo['length'] == 36 &&
					($fInfo['type'] === 'string' || $fInfo['type'] === 'binary')
				);
				if (empty($this->data[$this->alias][$this->primaryKey]) && $isUUID) {
					if (array_key_exists($this->primaryKey, $this->data[$this->alias])) {
						$j = array_search($this->primaryKey, $fields);
						$values[$j] = String::uuid();
					} else {
						list($fields[], $values[]) = array($this->primaryKey, String::uuid());
					}
				}
				if (!$db->create($this, $fields, $values)) {
					$success = $created = false;
				} else {
					$created = true;
				}
			}

			if ($success && !empty($this->belongsTo)) {
				$this->updateCounterCache($cache, $created);
			}
		}

		if (!empty($joined) && $success === true) {
			$this->_saveMulti($joined, $this->id, $db);
		}

		if ($success && $count > 0) {
			if (!empty($this->data)) {
				$success = $this->data;
				if ($created) {
					$this->data[$this->alias][$this->primaryKey] = $this->id;
				}
			}
			if ($options['callbacks'] === true || $options['callbacks'] === 'after') {
				$this->Behaviors->trigger('afterSave', array(&$this, $created, $options));
				$this->afterSave($created);
			}
			if (!empty($this->data)) {
				//$success = Set::merge($success, $this->data);
				$success = $this->data;
			}
			$this->data = false;
			$this->_clearCache();
			$this->validationErrors = array();
		}
		$this->whitelist = $_whitelist;
		return $success;
	}

}
?> 

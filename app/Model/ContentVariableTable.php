<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('ValidationHelper', 'Lib');
App::uses('ContentVariable', 'Model');


class ContentVariableTable extends MongoModel
{
    var $specific = true;
    var $name = 'ContentVariableTable';
    
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'columns'
            );
    }
    
    
    public $validate = array(
        'name' => array(
            'notempty',
            'uniqueName' => array(
                'rule' => 'isVeryUnique',
                'message' => 'Another table already exist with this name.'
                ),
            ),
        'columns' => array(
            'validColumns' => array(
                'rule' => array('validColumns'),
                'message' => 'noMessage',
                ),
            'noDuplicateHeader' => array(
                'rule' => array('noDuplicateHeader'),
                'message' => 'The table cannot have duplicate headers.',
                ),
            'sameNumberOfValues' => array(
                'rule' => array('sameNumberOfValues'),
                'message' => 'All column should have the same number of values'
                ),
            'atLeastOneContentVariableColumn' => array(
                'rule' => 'atLeastOneContentVariableColumn',
                'message' => 'Not able to identify unique set of keys.'
                ),
            'uniqueKeys' => array(
                'rule' => 'uniqueKeys',
                'message' => null
                ),
            )
        );
    
    
    public $validateColumn = array(
        'header' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter header name for this column',
                ),
            'validHeader' => array(
                'rule' => array('validColumnHeader', VusionConst::CONTENT_VARIABLE_KEY_REGEX),
                'message' => 'noMessage',
                )
            ),
        'type' => array(
            'notempty' =>  array(
                'rule' => array('notempty'),
                'message' => 'noMessage',
                ),
            'validValue' => array(
                'rule' => array('inList', array('key', 'contentvariable')),
                'message' => 'The column-type value can only be \"key\" or "contentvariable"'
                )
            ),
        'validation' => array(
            'validRegex' => array(
                'rule' => array('validRegex'),
                'message' => 'The validation can only be a valid regular expression.'
                ),
            ),
        'values' => array(
            'validValues' => array(
                'rule' => array('validColumnValues', array(
                    'key' => VusionConst::CONTENT_VARIABLE_KEY_REGEX,
                    'contentvariable' => VusionConst::CONTENT_VARIABLE_VALUE_REGEX)),
                'message' => null
                ),
            'runColumnValidation' => array(
                'rule' => array('runColumnValidation'),
                'message' => null
                ),
            ),
        );
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->ValidationHelper = new ValidationHelper(&$this);

        if (isset($id['id']['database'])) {
            $options = array('database' => $id['id']['database']);
        } else {
            $options = array('database' => $id['database']);
        }
        $this->ContentVariable = new ContentVariable($options);
    }    


    function validColumns($check)
    {
        if (!is_array($check['columns'])) {
            return false;
        }
        if (count($check['columns']) < 2) {
            return __('A table must have at least 2 columns.');
        }
        $valueValidationErrors = array();
        for($i = 0; $i < count($check['columns']); $i++) {
            $valueValidationErrors[$i] = $this->ValidationHelper->runValidationRules($check['columns'][$i], $this->validateColumn);
        }
        $valueValidationErrors = $this->arrayDelete($valueValidationErrors, true);
        if ($valueValidationErrors != array()) {
            return $valueValidationErrors;
        }
        return true;
    }
    
    
    function arrayDelete($array, $element) {
        return array_diff($array, array($element));
    }
    
    
    function validColumnHeader($check, $regex)
    {
        if (!preg_match($regex[0], $check['header'])) {
            return __("The header %s can only be made of letter, digit and space.", $check['header']);
        }
        return true;
    }
    
    
    function validColumnValues($check, $regex, $data)
    {
        $values = $check['values'];
        $type = $data['type'];
        foreach ($values as $element) {
            if (!preg_match($regex[0][$type], $element)) {
                if ($type == 'key') {
                    return __("The key %s can only be made of letter, digit and space.", $element);
                } else if ($type == 'contentvariable') {
                    return __("The variable %s can only be made of letter, digit, space, dot and comma.", $element);
                } else {
                    return false;
                }
            }
        }
        return true;
    }
    

    function runColumnValidation($check, $args, $data) 
    {
        if ($data['validation'] == null || !$this->validRegex($data)) {
            return true;
        }
        $values = $check['values'];
        $type = $data['type'];
        foreach ($values as $element) {
            if (!preg_match($data['validation'], $element)) {
                return __("The value %s is not matchin validation %s.", $element, $data['validation']);
            }
        }
        return true;        
    }

    
    function noDuplicateHeader($check)
    {
        $headers = array();
        for($i = 0; $i < count($check['columns']); $i++) {
            if (isset($headers[$check['columns'][$i]['header']])) {
                return false;
            }
            $headers[$check['columns'][$i]['header']] = true;
        }
        return true;
    }

    
    //After reflexion this might not be necessary
    function sameNumberOfValues($check)
    {
        return true;
    }


    function atLeastOneContentVariableColumn($check) 
    {
        for($i = 0; $i < count($check['columns']); $i++) {
            if ($check['columns'][$i]['type'] == 'contentvariable') {
                return true;
            }
        }
        return false;
    }


    function uniqueKeys($check) 
    {
        $data = $this->data['ContentVariableTable'];
        $keysValues = $this->getAllKeysValue($check['columns']);
        for($i = 0; $i < count($keysValues); $i++) {
            $cursor = $this->ContentVariable->find('fromKeys', array('conditions' => array(
                'keys' => $keysValues[$i]['keys'],
                'table' => array('$ne' => $data['name']))));
            if (count($cursor) > 0) {
                $contentVariable = $cursor[0]['ContentVariable']; 
                if (!isset($contentVariable['table'])) {
                    return __("The key %s is already used by a keys/value.", implode('.', $keysValues[$i]['keys']));
                } else {
                    return __("The key %s is already used by the table %s.", implode('.', $keysValues[$i]['keys']), $contentVariable['table']);
                }
            }  
        }
        return true;
    }

    
    function validRegex($check) 
    {
        if ($check['validation'] === null) {
            return true;
        }
        if (@preg_match($check['validation'], 'something') === false) {
            return false;
        }
        return true;
    }
    
    
    public function beforeValidate()
    {
        parent::beforeValidate();
        
        if (isset($this->data['ContentVariableTable']['columns'])) {
            $this->selectColumnsForKeys($this->data['ContentVariableTable']['columns']);
        }
    }

    
    function selectColumnsForKeys(&$columns) 
    {
        $keys = array();
        $hasUniqueKeys = false; 
        foreach ($columns as &$column) {
            if (!isset($column['validation'])) {
                $column['validation'] = null;
            }
            if (isset($column['type'])) {
                continue;
            }
            if ($hasUniqueKeys) {
                $column['type'] = 'contentvariable';
                //remove values
                continue;
            } else if ($keys == array()) {
                $column['type'] = 'key';
                $keys = $column['values'];
            } else {
                $column['type'] = 'key';
                for ($i = 0; $i < count($column['values']); $i++) {
                    $keys[$i] .= '.'. $column['values'][$i];
                }
            }
            if (!$this->hasDupes($keys)) {
                $hasUniqueKeys = true;
            }
        }
        return $columns;
    }
    

    function getAllKeysValue($columns) 
    {
        foreach ($columns as $column) {
            if (!isset($allKeys)) {
                $allKeys = array_fill(0, count($column['values']) - 1, array('keys'=> array()));
            }
            if ($column['type'] == 'key') {
                for ($i = 0; $i < count($column['values']); $i++) {
                    $allKeys[$i]['keys'][] = $column['values'][$i];
                }
            }
        }
        foreach ($columns as $column) {
            if ($column['type'] == 'contentvariable') {
                for ($i = 0; $i < count($allKeys); $i++) {
                    $allKeys[$i]['keys'][] = $column['header'];
                    $allKeys[$i]['value'] = $column['values'][$i];
                }
            }
        }
        return $allKeys;
    }

    
    function hasDupes($array){
        return count($array) !== count(array_unique($array));
    }


    function beforeSave($option = array())
    {
        $keysValues = $this->getAllKeysValue($this->data['ContentVariableTable']['columns']);
        foreach($keysValues as $keysValue) {
            $keysValue['table'] = $this->data['ContentVariableTable']['name'];
            $this->ContentVariable->create();
            $saved = $this->ContentVariable->save($keysValue);
            if ($saved == false) {
                $this->ContentVariable->deleteAll(array('name' => $this->data['ContentVariableTable']['name']));
                throw new Exception("Failed to save keys.");
            }
        }
        return true;
    }


    function deleteTableAndValues($id) 
    {
        $table = $this->read('name', $id);
        $this->ContentVariable->deleteAll(array('table' => $table['ContentVariableTable']['name']), false);
        return $this->delete($id);
    }


}

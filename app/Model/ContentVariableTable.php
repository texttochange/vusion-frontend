<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('ValidationHelper', 'Lib');
App::uses('ContentVariable', 'Model');


class ContentVariableTable extends MongoModel
{
    var $specific = true;
    var $name     = 'ContentVariableTable';
    
    
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
        if (isset($id['id']['database'])) {
            $options = array('database' => $id['id']['database']);
        } else {
            $options = array('database' => $id['database']);
        }
        
        $this->ContentVariable = new ContentVariable($options);
        $this->ValidationHelper = new ValidationHelper(&$this);
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
            $this->removeEmptyCells($this->data['ContentVariableTable']['columns']);
            $this->selectColumnsForKeys($this->data['ContentVariableTable']['columns']);
        }
        return true;
    }

    function removeEmptyCells(&$columns)
    {
        $lastUsedRow = 0;
        $lastUsedCol = 0;
        //First scan the all object
        for ($i=0; $i<count($columns); $i++) {
            if ($columns[$i]['header'] != null) {
                $lastUsedCol = $i;
            }
            for ($j=0; $j<count($columns[$i]['values']); $j++) {
                if ($columns[$i]['values'][$j] != null) {
                    $lastUsedCol = ($lastUsedCol < $i ? $i : $lastUsedCol);
                    $lastUsedRow = ($lastUsedRow < $j ? $j : $lastUsedRow);
                }
            }
        }
        //Second remove what is out
        $totalCol = count($columns);
        for ($i=$lastUsedCol+1; $i<=$totalCol; $i++) {
            unset($columns[$i]);
        }
        for ($i=0; $i<=$lastUsedCol; $i++) {
            $totalRow = count($columns[$i]['values']);
            for ($j=$lastUsedRow+1; $j<=$totalRow; $j++) {
                unset($columns[$i]['values'][$j]);
            }
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
            if (!isset($rowKeys)) {
                $rowKeys = array_fill(0, count($column['values']) - 1, array());
                $allKeys = array();
            }
            if ($column['type'] == 'key') {
                for ($i = 0; $i < count($column['values']); $i++) {
                    $rowKeys[$i][] = $column['values'][$i];
                }
            } 
            if ($column['type'] == 'contentvariable') {
                for ($i = 0; $i < count($rowKeys); $i++) {
                    $keys = $rowKeys[$i];
                    $keys[] = $column['header'];
                    $allKeys[] = array(
                        'keys' => $keys,
                        'value' => $column['values'][$i]);
                }
            }
        }
        return $allKeys;
    }

    function updateTableFromKeysValue($contentVariable) 
    {
        if (!isset($contentVariable['ContentVariable']['table'])) {
            return true;
        }
        $contentVariableTable = $this->find(
            'first', 
            array('conditions' => array('name' => $contentVariable['ContentVariable']['table'])));
        if (!isset($contentVariableTable)) {
            return false;
        }
        
        $indexes = array_keys(array_fill(0,count($contentVariableTable['ContentVariableTable']['columns'][0]['values']), '0'));
        for ($i = 0; $i < count($contentVariable['ContentVariable']['keys']); $i++) {
            $key = $contentVariable['ContentVariable']['keys'][$i]['key'];
            if ($contentVariableTable['ContentVariableTable']['columns'][$i]['type'] == 'key') {
                $keyIndexesCurrentColumn = array_keys($contentVariableTable['ContentVariableTable']['columns'][$i]['values'], $key);
                $indexes = array_intersect($keyIndexesCurrentColumn, $indexes);
                if (count($indexes) == 0) {
                    return false;
                }
            } else {
                if (count($indexes) != 1) {
                    return false;
                }
                $index = array_values($indexes);
                for ($j = $i; $j < count($contentVariableTable['ContentVariableTable']['columns']); $j++) {
                    if ($contentVariableTable['ContentVariableTable']['columns'][$j]['header'] == $key) {
                        $contentVariableTable['ContentVariableTable']['columns'][$j]['values'][$index[0]] = $contentVariable['ContentVariable']['value'];
                        $this->id = $contentVariableTable['ContentVariableTable']['_id'];
                        return $this->save($contentVariableTable, array('skipKeysValues' => true));
                    }
                }
            }
        }
        return false;
    }

    
    function hasDupes($array){
        return count($array) !== count(array_unique($array));
    }


    function beforeSave($option = array())
    {
        if (isset($option['skipKeysValues'])) {
            return true;
        }
        $keysValues = $this->getAllKeysValue($this->data['ContentVariableTable']['columns']);
        //Need to remove all ContentVariable keys/value of this table before saving them again
        $this->ContentVariable->deleteAll(array('table' => $this->data['ContentVariableTable']['name']), false);
        foreach($keysValues as $keysValue) {
            $keysValue['table'] = $this->data['ContentVariableTable']['name'];
            $this->ContentVariable->create();
            $saved = $this->ContentVariable->save($keysValue);
            if ($saved == false) {
                //A failure in saving 1 contentvarible, all content varible of this table are removed... dangerous state.
                $this->ContentVariable->deleteAll(array('table' => $this->data['ContentVariableTable']['name']), false);
                $errors = array_values($this->ContentVariable->validationErrors);
                throw new Exception(__("Failed to save keys/value from table: %s", $errors[0]));
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

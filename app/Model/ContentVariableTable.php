<?php
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('ValidationHelper', 'Lib');
App::uses('ContentVariable', 'Model');


class ContentVariableTable extends ProgramSpecificMongoModel
{
    
    var $name = 'ContentVariableTable';
    
    
    function getModelVersion()
    {
        return '2';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'name',
            'columns',
            'column-key-selection',
            );
    }
    
    
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => 'notempty',
                'message' => 'The table name cannot be empty.'
                ),
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
                'message' => null,
                ),
            'sameNumberOfValues' => array(
                'rule' => array('sameNumberOfValues'),
                'message' => 'All column should have the same number of values'
                ),
            'atLeastOneColumn' => array(
                'rule' => 'atLeastOneColumn',
                'message' => 'The table should at least have a first column.'
                ),
            'uniqueKeys' => array(
                'rule' => 'uniqueKeys',
                'message' => null
                ),
            'maxKeys' => array(
                'rule' => 'maxKeys',
                'message' => 'There are too many keys.',
                )
            ),
        'column-key-selection' => array(
            'validValue' => array(
                'rule' => array('inList', array('auto', 'first', 'first-two')),
                'message' => null
                ),
            'enoughColumns' => array(
                'rule' => array('enoughColumns'),
                'message' => 'The table should have at least the 2 first columns.'
                ),
            ),
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
        $this->ValidationHelper = new ValidationHelper($this);
    }    
    
    
    public function initializeDynamicTable($forceNew=false) 
    {
        parent::initializeDynamicTable();
        $this->ContentVariable = ProgramSpecificMongoModel::init(
            'ContentVariable', $this->databaseName);
    }
    
    
    function validColumns($check)
    {
        if (!is_array($check['columns'])) {
            return false;
        }
        $valueValidationErrors = array();
        for($i = 0; $i < count($check['columns']); $i++) {
            $valueValidationErrors[$i] = $this->ValidationHelper->runValidationRules($check['columns'][$i], $this->validateColumn);
        }
        $valueValidationErrors = $this->arrayDelete($valueValidationErrors);
        if ($valueValidationErrors != array()) {
            return $valueValidationErrors;
        }
        return true;
    }
    
    function enoughColumns($check)
    {
        if ($check['column-key-selection'] == 'first-two') {
            if (count($this->data['ContentVariableTable']['columns']) == 1) {
                return __('The table should have at least the 2 first columns.');
            }
        } 
        return true;
    }

    
    function arrayDelete($array) {
        foreach($array as $key => $value) {
            if ($value == 'true') {
                unset($array[$key]);
            }
        }
        return $array;
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
        $index = 0;
        $valueValidationErrors = array();
        foreach ($values as $element) {
            if (!preg_match($regex[0][$type], $element)) {
                if ($type == 'key') {
                    $valueValidationErrors[$index] = VusionConst::CONTENT_VARIABLE_KEY_FAIL_MESSAGE;
                } else if ($type == 'contentvariable') {
                    $valueValidationErrors[$index] = VusionConst::CONTENT_VARIABLE_VALUE_FAIL_MESSAGE;
                } else {
                    return false;
                }
            }
            $index++;
        }
        if ($valueValidationErrors != array()) {
            ##Return in an array to trick the ValidationHelper
            return array($valueValidationErrors);
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
                return __('The table cannot have duplicate headers "%s".', $check['columns'][$i]['header']);
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
    
    
    function maxKeys($check)
    {
        $keyOnly = function($column) {
            return ($column["type"] == "key");
        };
        if (count(array_filter($check['columns'], $keyOnly)) > 2) {
            return __("A maximum of 2 column can be keys, this table needs %s keys.", count(array_filter($check['columns'], $keyOnly)));
        }
        return true;
    }
    
    
    function atLeastOneColumn($check) 
    {
        if (count($check['columns']) == 0) {
            return false;
        }
        return true;
    }
    
    
    function uniqueKeys($check) 
    {
        $data = $this->data['ContentVariableTable'];
        $keysValues = $this->getAllKeysValue($check['columns']);
        for($i = 0; $i < count($keysValues); $i++) {
            if ($this->id) {
                $conditions = array(
                    'keys' => $keysValues[$i]['keys'],
                    'table' => array('$ne' => $this->id)
                    );
            } else {
                $conditions = array('keys' => $keysValues[$i]['keys']);
            }
            $cursor = $this->ContentVariable->find('fromKeys', array('conditions' => $conditions));
            if (count($cursor) > 0) {
                $contentVariable = $cursor[0]['ContentVariable']; 
                if (!isset($contentVariable['table'])) {
                    return __("The keys %s is already used by a keys/value.", implode('.', $keysValues[$i]['keys']));
                } else {
                    $contentVariableTable = $this->find('first', array('conditions' => array('_id' => new MongoId($contentVariable['table']))));
                    return __("The keys %s is already used by the table %s.", implode('.', $keysValues[$i]['keys']), $contentVariableTable['ContentVariableTable']['name']);
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
    
    
    public function beforeValidate($options = array())
    {
        parent::beforeValidate();

        $this->_setDefault('name', null);
        $this->_setDefault('columns', array());
        $this->_setDefault('column-key-selection', 'auto');

        $this->removeEmptyCells($this->data['ContentVariableTable']['columns']);
        $this->setKeyColumns();
        $this->setDefaultColumnValidation();
        return true;
    }

    
    function setDefaultColumnValidation($defaultValue=null)
    {
        foreach($this->data['ContentVariableTable']['columns'] as &$column) {
            if (!isset($column['validation'])) {
                $column['validation'] = null;
            }
        }
    }


    function removeEmptyCells(&$columns)
    {
        $lastUsedRow = -1;
        $lastUsedCol = -1;
        //First scan the all object
        for ($i = 0; $i < count($columns); $i++) {
            if ($columns[$i]['header'] != null) {
                $lastUsedCol = $i;
            }
            for ($j = 0; $j < count($columns[$i]['values']); $j++) {
                if ($columns[$i]['values'][$j] != null) {
                    $lastUsedCol = ($lastUsedCol < $i ? $i : $lastUsedCol);
                    $lastUsedRow = ($lastUsedRow < $j ? $j : $lastUsedRow);
                }
            }
        }
        //Second remove what is out
        $totalCol = count($columns);
        for ($i = $lastUsedCol+1; $i <= $totalCol; $i++) {
            unset($columns[$i]);
        }
        if (isset($columns[0])) {
            for ($i = 0; $i <= $lastUsedCol; $i++) {
                if ($lastUsedRow == -1) {
                    $columns[$i]['values'] = array();
                    continue;
                }
                $totalRow = count($columns[$i]['values']);
                for ($j = $lastUsedRow + 1; $j <= $totalRow; $j++) {
                    unset($columns[$i]['values'][$j]);
                }
            }
        }
    }
    

    function setKeyColumns()
    {
        switch ($this->data['ContentVariableTable']['column-key-selection']){
            case 'first':
                $this->setTypeColumns(0);
            break;
            case 'first-two':
                $this->setTypeColumns(1);
            break;
            default:
                $this->autoSelectKeyColumns();
            break;
        }
    }


    function setTypeColumns($lastKeyIndex) 
    {
        $counter = 0;
        foreach ($this->data['ContentVariableTable']['columns'] as &$column) {
            if ($counter <= $lastKeyIndex) {
                $column['type'] = 'key';
            } else {
                $column['type'] = 'contentvariable';
            }
            $counter++;
        }
    }

    
    function autoSelectKeyColumns() 
    {
        $keys = array();
        $hasUniqueKeys = false; 
        foreach ($this->data['ContentVariableTable']['columns'] as &$column) {
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
    }
    

    function hasKeyHeader($id, $header)
    {
        $cvt = $this->read(Null, $id);
        if (!isset($cvt['ContentVariableTable'])) {
            return false;
        }
        foreach ($cvt['ContentVariableTable']['columns'] as $column) {
            if ($column['header'] == $header && $column['type'] == 'key') {
                    return true;
            }
        }
        return false;
    }


    function getKeyHeaders($id)
    {
        $cvt = $this->read(Null, $id);
        if (!isset($cvt['ContentVariableTable'])) {
            return null;
        }
        $keyHeaders = array();
        foreach ($cvt['ContentVariableTable']['columns'] as $column) {
            if ($column['type'] != 'key') {
                continue;
            }
            $keyHeaders[] = $column['header'];
        }
        return $keyHeaders; 
    }


    function getAllKeysValue($columns) 
    {
        $allKeys = array();
        foreach ($columns as $column) {
            if (!isset($rowKeys)) {
                if (count($column['values']) == 0) {
                    continue;
                }
                $rowKeys = array_fill(0, count($column['values']), array());
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
            array('conditions' => array('_id' => new MongoId($contentVariable['ContentVariable']['table']))));
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
    
    
    function hasDupes($array)
    {
        return count($array) !== count(array_unique($array));
    }
    
    
    function beforeSave($option = array())
    {
        $this->data['ContentVariableTable']['modified'] = new MongoDate(strtotime('now'));
        return true;
    }
    
    
    function afterSave($created, $option = array())
    {
        if (isset($option['skipKeysValues'])) {
            return true;
        }
        $contentVariables = $this->getAllKeysValue($this->data['ContentVariableTable']['columns']); 
        $currentContentVariables = array();
        foreach ($contentVariables as $contentVariable) {
            $this->ContentVariable->create();
            $previousContentVariable = $this->ContentVariable->find('fromKeys', array('conditions' => array('keys' => $contentVariable['keys'])));
            if (isset($previousContentVariable[0]['ContentVariable'])) {
                ## it's an update
                $this->ContentVariable->id = $previousContentVariable[0]['ContentVariable']['_id'].'';
                ## Update don't return the id
                $currentContentVariables[] = $previousContentVariable[0]['ContentVariable']['_id'];
            } 
            $contentVariable['keys'] = $this->ContentVariable->setListKeys($contentVariable['keys']);
            $contentVariable['table'] = $this->id;
            $saved = $this->ContentVariable->save($contentVariable, false);
            ## save return the id
            if (isset($saved['ContentVariable']['_id'])) {
                $currentContentVariables[] = $saved['ContentVariable']['_id'].'';
            }
        }
        #remove not current ones
        $this->ContentVariable->deleteAll(
            array('table' => $this->id, 'id' => array('$nin' => $currentContentVariables)), false);
        return true;
    }
    
    
    function deleteTableAndValues($id) 
    {
        $this->ContentVariable->deleteAll(array('table' => $id), false);
        return $this->delete($id);
    }
    

    function exportFileGenerator($id, $fileFullPath)
    {  
        $handle = fopen($fileFullPath, "w");
        
        $contentVariableTable        = $this->read('columns', $id);
        $contentVariableTableColumns = $contentVariableTable['ContentVariableTable']['columns'];
        
        foreach ($contentVariableTableColumns as $contentVariableTableColumn) {
            $headers[]      = $contentVariableTableColumn['header'];
            $columnValues[] = $contentVariableTableColumn['values'];
        }
        fputcsv($handle, $headers, ',', '"');
        
        //model deosnt allow for null values, so each data set has equal number of elements
        $countCols = count($columnValues[0]);
        
        for ($x =0; $x < $countCols; $x++) {
            $line= array();
            $columnIndex = 0;
            foreach ($columnValues as $columnValue) {
                $line[] = $columnValues[$columnIndex][$x];
                $columnIndex++;
            }
            
            fputcsv($handle, $line, ',' , '"');
        }
    }
    
    
}

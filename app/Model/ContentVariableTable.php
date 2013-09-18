<?php
App::uses('MongoModel', 'Model');
App::uses('VusionConst', 'Lib');
App::uses('ValidationHelper', 'Lib');

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
            'notempty'
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
                )
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
        'values' => array(
            'validValues' => array(
                'rule' => array('validColumnValues', VusionConst::CONTENT_VARIABLE_KEY_REGEX),
                'message' => null
                ),
            ),
        'validation' => array(
            'validValue' => array(
                'rule' => array('validRegex'),
                'message' => 'The validation can only be a valid regular expression.'
                ),
            ),
        );
    

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


   function validColumnValues($check, $regex)
   {
       $list = array_values($check);
       foreach ($list[0] as $element) {
           if (!preg_match($regex[0], $element)) {
               return __("The key %s can only be made of letter, digit and space.", $element);
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

   
   public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->ValidationHelper = new ValidationHelper(&$this);
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
                for ($i = 0; $i <= count($column['values']) - 1; $i++) {
                    $keys[$i] .= '.'. $column['values'][$i];
                }
            }
            if (!$this->hasDupes($keys)) {
                $hasUniqueKeys = true;
            }
        }
        return $columns;
    }


    function hasDupes($array){
        return count($array) !== count(array_unique($array));
    }


}

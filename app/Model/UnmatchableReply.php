<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('FilterException', 'Lib');
/**
 * UnmatchableReply Model
 *
 */
class UnmatchableReply extends MongoModel
{

    var $specific    = true;
    var $name        = 'UnmatchableReply';
    var $useDbConfig = 'mongo';
    var $useTable    = 'unmatchable_reply';
    
    
    function getModelVersion()
    {
        return '1';
    }
   
    
    public function __construct($id = false, $table = null, $ds = null)
    {
            parent::__construct($id, $table, $ds);
            
            $this->dialogueHelper = new DialogueHelper();
    }


    function getRequiredFields($objectType=null)
    {
        return array(
            'participant-phone',
            'to',
            'message-content',
            'timestamp');
    }
    
    
    public $filterFields = array(
        'country' => array(
            'label' => 'country',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'country'))),
        'shortcode' => array(
            'label' => 'shortcode',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'text'))),
        'from-phone' => array(
            'label' => 'from number',
            'operators' => array(
                 'start-with' => array(
                    'label' => 'starts with',
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'label' => 'equal to',
                    'parameter-type' => 'text'),
                'start-with-any' => array(
                    'label' => 'starts with any of',
                    'parameter-type' => 'text'))),
        'to-phone' => array(
            'label' => 'to number',
            'operators' => array(
                'start-with' => array(
                    'label' => 'starts with',
                    'parameter-type' => 'text'),
                'equal-to' => array(
                    'label' => 'equal to',
                    'parameter-type' => 'text'))),
        'date' => array(
            'label' => 'date',
            'operators' => array(
                'from' => array(
                    'label' => 'since',
                    'parameter-type' => 'date'),
                'to' => array(
                    'label' => 'until',
                    'parameter-type' => 'date'))),
        'message-content' =>  array( 
            'label' => 'message content',
            'operators' => array(
                'equal-to' => array(
                    'label' => 'equals',
                    'parameter-type' => 'text'),
                'contain' => array(
                    'label' => 'contains',
                    'parameter-type' => 'text'),
                'has-keyword' => array(
                    'label' => 'has keyword',
                    'parameter-type' => 'text'))), 
    );

    public $filterOperatorOptions = array(
        'all' => 'all',
        'any' => 'any'
        );

    
    public function validateFilter($filterParam)
    {
        if (!isset($filterParam[1])) {
            throw new FilterException("Field is missing.");
        }

        if (!isset($this->filterFields[$filterParam[1]])) {
            throw new FilterException("Field '".$filterParam[1]."' is not supported.");
        }

        if (!isset($filterParam[2])) {
            throw new FilterException("Operator is missing for field '".$filterParam[1]."'.");
        }
        
        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]])) {
            throw new FilterException("Operator '".$filterParam[2]."' not supported for field '".$filterParam[1]."'.");
        }

        if (!isset($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'])) {
            throw new FilterException("Operator type missing '".$filterParam[2]."'.");
        }
        
        if ($this->filterFields[$filterParam[1]]['operators'][$filterParam[2]]['parameter-type'] != 'none' && !isset($filterParam[3])) {
            throw new FilterException("Parameter is missing for field '".$filterParam[1]."'.");
        }
    }


    public function fromFilterToQueryConditions($filter, $countryPrefixes = array()) {
        $conditions = array();

        foreach($filter['filter_param'] as $filterParam) {
        
            $condition = null;
            
            $this->validateFilter($filterParam);
            
            if ($filterParam[1] == 'country') {
                $countryPrefix = $countryPrefixes[$filterParam[3]];
                if ($filterParam[2] == 'is') {
                    $condition['participant-phone'] = new MongoRegex("/^(\\+)?".$countryPrefix."/");
                }
            } elseif ($filterParam[1] == 'shortcode') {
                if ($filterParam[2] == 'is') {
                    $condition['$or'] = array(
                        array('participant-phone' => new MongoRegex("/\\d*-".$filterParam[3]."$/")),
                        array('to' => $filterParam[3]));
                }
            } elseif ($filterParam[1] == 'date') {
                if ($filterParam[2] == 'from') { 
                    $condition['timestamp']['$gt'] = $this->dialogueHelper->ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['timestamp']['$lt'] = $this->dialogueHelper->ConvertDateFormat($filterParam[3]);
                }
            } elseif ($filterParam[1] == 'from-phone') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['participant-phone'] = $filterParam[3];                   
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['participant-phone'] = new MongoRegex("/^\\".$filterParam[3]."/");
                } elseif ($filterParam[2] == 'start-with-any') {
                    $phoneNumbers = explode(",", str_replace(" ", "", $filterParam[3]));
                    if ($phoneNumbers) {
                        if (count($phoneNumbers) > 1) {
                            $or = array();
                            foreach ($phoneNumbers as $phoneNumber) {
                                $regex = new MongoRegex("/^\\".$phoneNumber."/");
                                $or[] = array('participant-phone' => $regex);
                            }
                            $condition['$or'] = $or;
                        } else {
                            $condition['participant-phone'] = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                        }
                    }   
                }
            } elseif ($filterParam[1] == 'to-phone') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['to'] = $filterParam[3];                   
                } elseif ($filterParam[2] == 'start-with') {
                    $condition['to'] = new MongoRegex("/^\\".$filterParam[3]."/");
                } 
            } elseif ($filterParam[1] == 'message-content') {
                if ($filterParam[2] == 'equal-to') {
                    $condition['message-content'] = $filterParam[3];
                } elseif ($filterParam[2] == 'contain') {
                     $condition['message-content'] = new MongoRegex("/".$filterParam[3]."/i");
                } elseif ($filterParam[2] == 'has-keyword') {
                    $condition['message-content'] = new MongoRegex("/^".$filterParam[3]."($| )/i");
                }
            } 
            
            if ($filter['filter_operator'] == "all") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$and'])) {
                    $conditions = array('$and' => array($conditions, $condition));
                } else {
                    array_push($conditions['$and'], $condition);
                }
            }  elseif ($filter['filter_operator'] == "any") {
                if (count($conditions) == 0) {
                    $conditions = $condition;
                } elseif (!isset($conditions['$or'])) {
                    $conditions = array('$or' => array($conditions, $condition));
                } else {
                    array_push($conditions['$or'], $condition);
                }
            }
        }
        return $conditions;
    }
    

}

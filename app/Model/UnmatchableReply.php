<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('FilterException', 'Lib');


class UnmatchableReply extends MongoModel
{
    var $name     = 'UnmatchableReply';
    var $useTable = 'unmatchable_reply';
    
    var $countriesByPrefixes = null;
    
    
    function getModelVersion()
    {
        return '1';
    }
    
    
    function getRequiredFields($objectType=null)
    {
        return array(
            'participant-phone',
            'to',
            'message-content',
            'timestamp');
    }
    
    
    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        
        $this->Behaviors->load('CachingCount', array(
            'redis' => Configure::read('vusion.redis'),
            'redisPrefix' => Configure::read('vusion.redisPrefix'),
            'cacheCountExpire' => Configure::read('vusion.cacheCountExpire')));
        $this->Behaviors->load('FilterMongo');
        
        $fileName = WWW_ROOT . Configure::read('vusion.countriesPrefixesFile');
        $this->countriesByPrefixes = DialogueHelper::loadPrefixesByCountries($fileName);
    }
    
    
    public function paginateCount($conditions, $recursive, $extra)
    {
        try{
            if (isset($extra['maxLimit'])) {
                $maxPaginationCount = 40;
            } else {
                $maxPaginationCount = $extra['maxLimit'];
            }
            
            $result = $this->count($conditions, $maxPaginationCount);
            if ($result == $maxPaginationCount) {
                return 'many';
            } else {
                return $result; 
            }            
        } catch (MongoCursorTimeoutException $e) {
            return 'many';
        }
    }
    
    
    public $filterFields = array(
        'shortcode' => array(
            'label' => 'shortcode',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'text'))),
        'country' => array(
            'label' => 'country',
            'operators' => array(
                'is' => array(
                    'label' => 'is',
                    'parameter-type' => 'country'))),
        
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
    
    
    public function fromFilterToQueryCondition($filterParam)
    {
        $condition = array();
        
        if ($filterParam[1] == 'country') {
            $countryPrefix = $this->countriesByPrefixes[$filterParam[3]];
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
                $condition['timestamp']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
            } elseif ($filterParam[2] == 'to') {
                $condition['timestamp']['$lt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
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
        
        return $condition;
    }
    
    
}

<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');


class CreditLog extends MongoModel
{

    var $specific    = true;
    var $name        = 'CreditLog';
    var $useDBConfig = 'mongo';
    var $useTable    = 'credit_logs';


    function getModelVersion()
    {
        return '1';
    }


    function getRequiredFields($objectType) 
    {
        $fields = array();

        $creditLogFields = array(
            'date',
            'code',
            'incoming',
            'outgoing');

        $programCreditLogFields = array(
            'program-database');

        $garbageCreditLogFields = array(
            'program-database');

        switch($objectType) {
        case 'program-credit-log':
            $fields = array_merge($creditLogFields, $programCreditLogFields);
            break;
        case 'garbage-credit-log':
            $fields = array_merge($creditLogFields, $garbageCreditLogFields);
            break;
        }
        return $fields;
    }

    public function calculateProgramShortcodeCredits($databases, $shortcodes, $conditions=array())
    {
        $conditions += array('$or' => array(
		    array('program-database' => array('$in' => $databases)),
		    array(
		        'object-type' => 'garbage-credit-log',
		        'code' => array('$in' => $shortcodes))));

		return $this->calculateCredits($conditions);
    }


    public function calculateCredits($conditions=array())
    {
        $reduce = new MongoCode(
            "function(obj, prev){
                prev.incoming += obj.incoming;
                prev.outgoing += obj.outgoing;
                if ('outgoing-ack' in obj) {
                    prev['outgoing-ack'] += obj['outgoing-ack']; 
                }
                if ('outgoing-nack' in obj) {
                    prev['outgoing-nack'] += obj['outgoing-nack']; 
                }
                if ('outgoing-delivered' in obj) {
                    prev['outgoing-delivered'] += obj['outgoing-delivered']; 
                }
                if ('outgoing-failed' in obj) {
                    prev['outgoing-failed'] += obj['outgoing-failed']; 
                }
			}");

		$key = array(
		    'object-type' => true,
		    'code' => true,
		    'program-database' => true,
		    );

		$initial = array(
		    'incoming' => 0,
		    'outgoing' => 0,
		    'outgoing-ack' => 0,
		    'outgoing-nack' => 0,
		    'outgoing-failed' => 0,
		    'outgoing-delivered' =>0);

        $query = array(
				'key' => $key,
				'initial' => $initial,
				'reduce' => $reduce,
				'options' => array(
				    'condition' => $conditions
				    )
				);
		$mongo = $this->getDataSource();
		$groupResults = $mongo->group($this, $query);
		if (!isset($groupResults['retval'])) {
		    return null;
		}

		return array_map("CreditLog::cleanResult", $groupResults['retval']);
    }

    public static function cleanResult($result)
    {
        if ($result['object-type'] === 'garbage-credit-log') {
            unset($result['program-database']);
        }
        return $result;
    }
    

    public static function searchCreditLog($credits, $field, $value)
    {
        $results = array();
        foreach($credits as $key => $credit) {
            if (isset($credit[$field]) && ($credit[$field] === $value)) {
                $results[] = $credit;
            }
        }
        return $results;
    }

    
    public static function filterPrefixedCodeByPrefix($prefixedCodes, $prefix) 
    {
        $result = array();
        $regex = '/^('.$prefix.'\-|\+'.$prefix.')/';
        foreach ($prefixedCodes as $prefixedCode) {
            if (preg_match($regex, $prefixedCode)) {
                $result[] = $prefixedCode;
            }
        }
        return $result;
    }


    public function calculateCreditPerCountry($conditions, $countriesByPrefixes) {
        $perCountry = array();
        $credits = $this->calculateCredits($conditions);
        $prefixedCodes = Set::classicExtract($credits, '{n}.code');
        $prefixedCodes = array_unique($prefixedCodes);
        $prefixes = array();
        foreach ($prefixedCodes as $prefixedCode) {
            $prefix = DialogueHelper::fromPrefixedCodeToPrefix($prefixedCode, $countriesByPrefixes);
            $prefixes[] = $prefix;         
        }
        $prefixes = array_unique($prefixes);
        foreach ($prefixes as $prefix) {
             $country = array(
                'country' => $countriesByPrefixes[$prefix],
                'prefix' => $prefix,
                'codes' => array());
            $countryCodes = CreditLog::filterPrefixedCodeByPrefix($prefixedCodes, $prefix);
            foreach ($countryCodes as $countryCode) {
                $shortcodeCredits = CreditLog::searchCreditLog($credits, 'code', $countryCode);
                $programShortcodeCredits = CreditLog::searchCreditLog($shortcodeCredits, 'object-type', 'program-credit-log');
                $garbageShortcodeCredit = CreditLog::searchCreditLog($shortcodeCredits, 'object-type', 'garbage-credit-log');
                $country['codes'][] = array(
                    'code'=> $countryCode,
                    'programs' => $programShortcodeCredits,
                    'garbage' => ($garbageShortcodeCredit == array()? array() : $garbageShortcodeCredit[0]));                
            }
            $perCountry[] = $country; 
        }
        return $perCountry;
    }
    

    public static function fromFilterToQueryConditions($filter, $conditions = array()) {
        
        if (!isset($filter['filter_param'])) {
            return $conditions;
        }

        foreach ($filter['filter_param'] as $filterParam) {
            
            $condition = null;
                        
            if ($filterParam[1] == 'date') {
                if ($filterParam[2] == 'from') { 
                    $condition['date']['$gt'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
                } elseif ($filterParam[2] == 'to') {
                    $condition['date']['$lte'] = DialogueHelper::ConvertDateFormat($filterParam[3]);
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
            } elseif ($filter['filter_operator'] == "any") {
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
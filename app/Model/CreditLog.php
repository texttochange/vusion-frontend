<?php
App::uses('MongoModel', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('VusionException', 'Lib');


class CreditLog extends MongoModel
{

    var $name        = 'CreditLog';
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

        $deletedProgramCreditLogFields = array(
            'program-name');

        $garbageCreditLogFields = array();

        switch($objectType) {
        case 'program-credit-log':
            $fields = array_merge($creditLogFields, $programCreditLogFields);
            break;
        case 'deleted-program-credit-log':
            $fields = array_merge($creditLogFields, $deletedProgramCreditLogFields);
            break;
        case 'garbage-credit-log':
            $fields = array_merge($creditLogFields, $garbageCreditLogFields);
            break;
        }
        return $fields;
    }

    var $optionalFields = array(
        'outgoing-acked',
        'outgoing-nacked',
        'outgoing-pending',
        'outgoing-delivered',
        'outgoing-failed');


    //Overwrite to allow the optional fields
    public function checkFields($object)
    {
        if (isset($object['object-type'])) {
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields($object['object-type']));
        } else {
            $toCheck = array_merge($this->defaultFields, $this->getRequiredFields());
        }

        foreach ($object as $field => $value) {
            if (!in_array($field, $toCheck)) {
                if (!in_array($field, $this->optionalFields)) {
                    unset($object[$field]);
                }
            }
        }

        foreach ($toCheck as $field) {
            if (!isset($object[$field])) {
                $object[$field] = null;
            }
        };

        return $object;
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
                if ('outgoing-pending' in obj) {
                    prev['outgoing-pending'] += obj['outgoing-pending']; 
                }
                if ('outgoing-acked' in obj) {
                    prev['outgoing-acked'] += obj['outgoing-acked']; 
                }
                if ('outgoing-nacked' in obj) {
                    prev['outgoing-nacked'] += obj['outgoing-nacked']; 
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
		    'program-name' => true,
		    );

		$initial = array(
		    'incoming' => 0,
		    'outgoing' => 0,
		    'outgoing-pending' => 0,
		    'outgoing-acked' => 0,
		    'outgoing-nacked' => 0,
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
            unset($result['program-name']);
        } else if ($result['object-type'] === 'program-credit-log') {
            unset($result['program-name']);
        } else if ($result['object-type'] === 'deleted-program-credit-log') {
            unset($result['program-database']);
        }
        return $result;
    }
    

    public static function searchCreditLog($credits, $field, $value)
    {
        $results = array();
        foreach($credits as $key => $credit) {
            if (isset($credit[$field]) && in_array($credit[$field], $value)) {
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
        if ($credits == null) {
            return array();
        }
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
                'codes' => array(),
                'incoming' => 0,
                'outgoing' => 0);
            $countryCodes = CreditLog::filterPrefixedCodeByPrefix($prefixedCodes, $prefix);
            foreach ($countryCodes as $countryCode) {
                $code = array(
                    'code' => $countryCode,
                    'programs' => array(),
                    'garbage' => null,
                    'incoming' => 0,
                    'outgoing' => 0);
                $codeCredits = CreditLog::searchCreditLog($credits, 'code', array($countryCode));
                $code['programs'] = CreditLog::searchCreditLog($codeCredits, 'object-type', array('program-credit-log', 'deleted-program-credit-log'));
                $code['garbage'] = CreditLog::searchCreditLog($codeCredits, 'object-type', array('garbage-credit-log'));
                if ($code['garbage'] != array()) {
                    $code['garbage'] = $code['garbage'][0];
                } 
                //Sum at code level
                $code['incoming'] = (int)Set::apply('/incoming', $codeCredits, 'array_sum');
                $code['outgoing'] = (int)Set::apply('/outgoing', $codeCredits, 'array_sum');
                //Sum at country level
                $country['codes'][] = $code;
                $country['incoming'] += $code['incoming'];
                $country['outgoing'] += $code['outgoing'];
            }
            $perCountry[] = $country; 
        }
        return $perCountry;
    }
    
    public function deletingProgram($programName, $programDatabase) 
    {
        $conditions = array('program-database' => $programDatabase);
        $updateData = array(
            '$set'=> array(
                'object-type' => 'deleted-program-credit-log',
                'program-name' => $programName),
            '$unset' => array('program-database' => ''));
        $result = $this->updateAll($updateData, $conditions);
    }
    

    public static function fromTimeframeParametersToQueryConditions($timeframeParameters, $conditions = array()) {
        
        $condition = null;
  
        if (isset($timeframeParameters['date-from']) && $timeframeParameters['date-from'] != '') {
            $condition['date']['$gte'] = DialogueHelper::ConvertDateFormat($timeframeParameters['date-from']);
        }
        if (isset($timeframeParameters['date-to']) && $timeframeParameters['date-to'] != '') {
            $condition['date']['$lt'] = DialogueHelper::ConvertDateFormat($timeframeParameters['date-to']);
        }
        if (isset($timeframeParameters['predefined-timeframe']) && $timeframeParameters['predefined-timeframe'] != '') {
            if ($timeframeParameters['predefined-timeframe'] == 'last-month') {
                $dateFrom = date('Y-m-t', strtotime("first day of previous month") ) ;
                $dateTo = date('Y-m-01') ;
                $condition['date']['$gte'] = $dateFrom;
                $condition['date']['$lt'] = $dateTo; 
            } else if ($timeframeParameters['predefined-timeframe'] == 'current-month') {
                $dateFrom = date('Y-m-01');
                $condition['date']['$gte'] = $dateFrom;
            } else if ($timeframeParameters['predefined-timeframe'] == 'today') {
                $dateFrom = date('Y-m-d');
                $condition['date']['$gte'] = $dateFrom;
            } else if ($timeframeParameters['predefined-timeframe'] == 'yesterday') {
                $dateFrom = date('Y-m-d', time() - 60 * 60 * 24);
                $dateTo = date('Y-m-d');
                $condition['date']['$gte'] = $dateFrom;
                $condition['date']['$lt'] = $dateTo;
            } else {
                throw new VusionException(__("Predefined timeframe %s not supported", $timeframeParameters['predefined-timeframe']));
            }
        }
            
        if (count($conditions) == 0) {
            $conditions = $condition;
        } elseif (!isset($conditions['$and'])) {
            $conditions = array('$and' => array($conditions, $condition));
        } else {
            array_push($conditions['$and'], $condition);
        }
        return $conditions;
    } 

} 
<?php
App::uses('MongoModel', 'Model');

class Script extends MongoModel
{

    var $specific    = true;
    var $name        = 'Script';
    var $useDbConfig = 'mongo';
    
    public $findMethods = array(
        'draft' => true,
        'countDraft' => true,
        'active' => true,
        'countActive' => true,
        'count' => true,
        'keyword' => true
        );


    protected function _findActive($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['order']['created'] = 'desc';
            $query['conditions']['Script.activated'] = 1;
            return $query;
        }
        return $results;
    }


    protected function _findCountActive($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['fields'] = 'count';
            $query['conditions']['Script.activated'] = 1;
            return $query;
        }
        return $results;
    }


    protected function _findCountDraft($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['fields'] = 'count';
            $query['conditions']['Script.activated'] = 0;
            return $query;
        }
        return $results;
    }


    protected function _findDraft($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['conditions']['Script.activated'] = 0;
            return $query;
        }
        return $results;
    }


    protected function _findKeyword($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['order']['created'] = 'desc';
            $query['limit'] = 1;
            $query['conditions']['Script.activated'] = 1;
            return $query;
        }
        if (isset($results[0]) and isset($results[0]['Script']['script']['dialogues'])) {
            foreach ($results[0]['Script']['script']['dialogues'] as $dialogue) {
                foreach ($dialogue['interactions'] as $interaction) {
                    if ($interaction['type-interaction']=='question-answer'
                    	    and strtolower($interaction['keyword']) == strtolower($query['keyword']))
                        return $results;
                }
            }
        }
        return array();
    }

    
    private function _validateDate($date)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/', $date, $parts) == true) {
            $time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);
          
            $input_time = strtotime($date);
            if ($input_time === false) return false;
            
            return $input_time == $time;
        } else {
            return false;
        }
    }

    //TODO to remove when client side javascrit is managing the format
    private function _validateDateFromForm($date)
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/', $date, $parts) == true) {
            /*$time = gmmktime($parts[4], $parts[5], 0, $parts[2], $parts[1], $parts[3]);
          
            $input_time = strtotime($date);
            if ($input_time === false) return false;
            
            return $input_time == $time;*/
            return true;
        } else {
            return false;
        }
    }


    private function _convertDateFormat($date)
    {
        return DateTime::createFromFormat('d/m/Y H:i', $date)->format('Y-m-d\TH:i:s');
    }


    private function _recurseScriptDateConverter(&$currentLayer)
    {

    	if (!is_array($currentLayer)) {
    	    return true;
    	}

        foreach ($currentLayer as $key => &$value) {
            if (!is_int($key) && ($key == 'date-time') && !$this->_validateDate($value)) {
                if ($this->_validateDateFromForm($value)) {
                    $value = $this->_convertDateFormat($value);
                } else {
                    return false;
                }
            }
            elseif (is_array($value)) {
                if (!$this->_recurseScriptDateConverter($value)) {
                   return false;
                }
            }
        }
        return true;
    }


    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }


    public function beforeValidate()
    {
    	/**By default a script in not activated*/
        if (!(isset($this->data['Script']['activated']))) {
            $this->data['Script']['activated'] = 0;
        }
        
        if (!isset($this->data['Script']['script'])) {
            return false;
        }

        $this->data['Script']['script'] = $this->object_to_array($this->data['Script']['script']);

        /**Convert all date-time in iso format*/
        $result = $this->_recurseScriptDateConverter($this->data['Script']['script']);
        return $result;
        
    }
    
    /** TODO: the choice of updating or saving should be done here.
    * however it's not working throwing a duplicate key.
    * It seems the beforeSave is too late to have the action change 
    * from saving to updating.
    */
    public function beforeSave()
    {        
        return true;        
    }
    
    public function makeDraftActive()
    {
        $draft = $this->find('draft');
        if ($draft) {
            $draft[0]['Script']['activated'] = 1;
            $this->create();
            $this->id = $draft[0]['Script']['_id'];
            $this->save($draft[0]['Script']);
            return $draft[0]['Script'];
        }
        return false;
    }


}

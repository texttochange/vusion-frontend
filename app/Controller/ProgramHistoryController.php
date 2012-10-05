<?php

App::uses('AppController','Controller');
App::uses('History','Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');

class ProgramHistoryController extends AppController
{

    public $uses    = array('History');
    var $components = array('RequestHandler');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time'
        );

    function constructClasses()
    {
        parent::constructClasses();
        
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->History        = new History($options);
        $this->Dialogue       = new Dialogue($options);
        $this->dialogueHelper = new DialogueHelper();
        
        $filterFields = $this->History->fieldFilters;
        $this->filterFieldOptions = array();
        foreach ($filterFields as $key => $value) {
            $this->filterFieldOptions[$key] = __($value);
        }
        
        $filterTypeConditions = $this->History->typeConditionFilters;
        $this->filterTypeConditionsOptions = array();
        foreach ($filterTypeConditions as $key => $value) {
            $this->filterTypeConditionsOptions[$key] = __($value);
        }
        
        $filterStatusConditions = $this->History->statusConditionFilters;
        $this->filterStatusConditionsOptions = array();
        foreach ($filterStatusConditions as $key => $value) {
            $this->filterStatusConditionsOptions[$key] = __($value);
        }
        
        
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function index()
    {
        $this->set('filterFieldOptions',
            $this->filterFieldOptions);
        $this->set('filterTypeConditionsOptions',
            $this->filterTypeConditionsOptions);
        $this->set('filterStatusConditionsOptions',
            $this->filterStatusConditionsOptions);
        $this->set('filterDialogueConditionsOptions',
            $this->Dialogue->getDialoguesInteractionsContent());
        
        $this->set('programTimezone', $this->Session->read($this->params['program'].'_timezone'));
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

        if ($this->params['ext'] == 'csv' or $this->params['ext'] == 'json') {
            $statuses = $this->History->find('all', array('conditions' => $this->_getConditions(),
                'order'=> $order,
                ));
            $this->set(compact('statuses')); 
        } else {   
            $this->paginate = array(
                'all',
                'conditions' => $this->_getConditions(),
                'order'=> $order,
            );
            
            $statuses = $this->paginate();
            $this->set(compact('statuses'));
        }
    }
    
    
    public function export()
    {    
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
    
        $exportParams = array(
            'fields' => array('participant-phone','message-direction','message-status','message-content','timestamp'),
            'conditions' => $this->_getConditions(),
            'order'=> $order,
        );
        
        $data = $this->History->find('all', $exportParams);
        $this->set(compact('data'));
    }
  
    protected function _getConditions()
    {
        $conditions = array();
        
        $onlyFilterParams = array_intersect_key($this->params['url'], array_flip(array('filter_param')));

        if (!isset($onlyFilterParams['filter_param'])) 
            return $conditions;
        
        foreach($onlyFilterParams['filter_param'] as $onlyFilterParam) {
             print_r($onlyFilterParam);
            if ($onlyFilterParam[1] == 'dialogue') {
                $conditions['dialogue-id'] = $onlyFilterParam[2];
                $conditions['interaction-id'] = $onlyFilterParam[3];
            } elseif ($onlyFilterParam[1] == 'date-from') { 
                $conditions['timestamp']['$gt'] = $this->dialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1] == 'date-to') {
                $conditions['timestamp']['$lt'] = $this->dialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1] == 'participant-phone') {
                $phoneNumbers = explode(",", str_replace(" ", "", $onlyFilterParam[2]));
                if ($phoneNumbers) {
                    if (count($phoneNumbers) > 1) {
                        $or = array();
                        foreach ($phoneNumbers as $phoneNumber) {
                            $regex = new MongoRegex("/^\\".$phoneNumber."/");
                            $or['participant-phone'] = $regex;
                        }
                        $conditions['$or'] = $or;
                    } else {
                        $conditions['participant-phone'] = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                    }
                }
            } elseif ($onlyFilterParam[1]=='non-matching-answers') {
                $conditions['message-direction'] = 'incoming';
                $conditions['matching-answer'] = null;
            } elseif ($onlyFilterParam[1] == 'message-content') {
                $conditions['message-content'] = new MongoRegex("/".$onlyFilterParam[2]."/i");
            } elseif ($onlyFilterParam[1] == 'message-direction' or $onlyFilterParam[1] == 'message-status') {
                $conditions[$onlyFilterParam[1]] = $onlyFilterParam[2];
            } else {
                $this->Session->setFlash(__('The filter "%s" is not supported.',$onlyFilterParam[1]), 
                'default',
                array('class' => "message failure")
                );
            }
        }
        
        /*
        if (preg_grep('/^filter_/', array_keys($this->params['url']))) {
            $or = array();
            $orConditions = array();
            if (isset($this->params['url']['dialogue_id']))
                $conditions['dialogue-id'] = $this->params['url']['dialogue_id'];
            if (isset($this->params['url']['interaction_id']))
                $conditions['interaction-id'] = $this->params['url']['interaction_id'];
            if (isset($this->params['url']['filter_type']))
                $conditions['message-direction'] = $this->params['url']['filter_type'];
            if (isset($this->params['url']['filter_status']))
                $conditions['message-status'] = $this->params['url']['filter_status'];
            if (isset($this->params['url']['filter_from']) && !isset($this->params['url']['filter_to'])) 
                $conditions['timestamp'] = array('$gt'=>$this->dialogueHelper->ConvertDateFormat($this->params['url']['filter_from']));
            if (isset($this->params['url']['filter_to']) && !isset($this->params['url']['filter_from']))
                $conditions['timestamp'] = array('$lt'=>$this->dialogueHelper->ConvertDateFormat($this->params['url']['filter_to']));
            if (isset($this->params['url']['filter_from']) && isset($this->params['url']['filter_to']))
                $conditions['timestamp'] = array(
                    '$gt'=>$this->dialogueHelper->ConvertDateFormat($this->params['url']['filter_from']),
                    '$lt'=>$this->dialogueHelper->ConvertDateFormat($this->params['url']['filter_to'])
                );
            if (isset($this->params['url']['filter_phone'])) {
                $phoneNumbers = explode(",", str_replace(" ", "",$this->params['url']['filter_phone']));
                if (sizeof($phoneNumbers) > 1) {
                    foreach ($phoneNumbers as $phoneNumber) {
                        if (strlen($phoneNumber) >= 12) {
                            $orConditions['participant-phone'] = $phoneNumber;
                            $or[] = $orConditions;
                        } else {
                            $regex = new MongoRegex("/^\\".$phoneNumber."/");
                            $orConditions['participant-phone'] = $regex;
                            $or[] = $orConditions;
                        }
                    }
                    $conditions['$or'] = $or;
                } else {                
                    if (strlen($phoneNumbers[0]) >= 12) {
                        $conditions['participant-phone'] = $phoneNumbers[0];
                    } else {
                        $regex = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                        $conditions['participant-phone'] = $regex;
                    }
                }
            }
            if (isset($this->params['url']['filter_content']))
                $conditions['message-content'] = new MongoRegex("/".$this->params['url']['filter_content']."/i");
        }
        if (isset($this->params['url']['filter'])) {
            if ($this->params['url']['filter']=='non_matching_answers') {
                $conditions['message-direction'] = 'incoming';
                $conditions['matching-answer'] = null;
            } else {
                $this->Session->setFlash(__('The filter "%s" is not supported.',$this->params['url']['filter']), 
                'default',
                array('class' => "message failure")
                );
            }
        }*/
        return $conditions;
    }
    

}

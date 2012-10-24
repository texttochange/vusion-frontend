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
            
            $histories = $this->paginate();
            $dialoguesInteractionsContent = $this->Dialogue->getDialoguesInteractionsContent();
            $statuses = $this->History->addDialogueContent($histories, $dialoguesInteractionsContent);
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
            if ($onlyFilterParam[1] == 'dialogue') {
                $conditions['dialogue-id'] = $onlyFilterParam[2];
                if ($onlyFilterParam[3]!='all')
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
                            $or[] = array('participant-phone' => $regex);
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
        
        return $conditions;
    }
    

}

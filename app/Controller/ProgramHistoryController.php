<?php

App::uses('AppController','Controller');
App::uses('History','Model');
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
        $this->dialogueHelper   = new DialogueHelper();
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function index()
    {
        if ($this->params['ext'] == 'csv' or $this->params['ext'] == 'json') {
            $statuses = $this->History->find('all', array('conditions' => $this->_getConditions()));
            $this->set(compact('statuses')); 
        } else {   
            $this->paginate = array(
                'all',
                'conditions' => $this->_getConditions()
            );
            
            $statuses = $this->paginate();
            $this->set(compact('statuses'));
        }
    }
    
    
    public function export()
    {        
        $exportParams = array(
            'fields' => array('participant-phone','message-type','message-status','message-content','timestamp'),
            'conditions' => $this->_getConditions()
        );
        
        $data = $this->History->find('all', $exportParams);
        $this->set(compact('data'));
    }
    
    
    protected function _getConditions()
    {
        $conditions = array();
        
        if (preg_grep('/^filter_/', array_keys($this->params['url']))) {    
            $or = array();
            $orConditions = array();
            if (isset($this->params['url']['filter_type']))
                $conditions['message-type'] = $this->params['url']['filter_type'];
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
        }
        if (isset($this->params['url']['filter'])) {
            if ($this->params['url']['filter']=='non_matching_answers') {
                $conditions['message-type'] = 'received';
                $conditions['matching-answer'] = null;
            } else {
                $this->Session->setFlash(__('The filter "%s" is not supported.',$this->params['url']['filter']), 
                'default',
                array('class' => "message failure")
                );
            }
        }
        return $conditions;
    }
    

}

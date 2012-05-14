<?php

App::uses('AppController','Controller');
App::uses('History','Model');
App::uses('Script','Model');
App::uses('ScriptHelper', 'Lib');

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
        $this->Script         = new Script($options);
        $this->scriptHelper   = new ScriptHelper();
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function index()
    {
        if (preg_grep('/^filter_/', array_keys($this->params['url']))) {        
            $conditons = array();
            $or = array();
            $orConditons = array();
            if (isset($this->params['url']['filter_type']))
                $conditions['message-type'] = $this->params['url']['filter_type'];
            if (isset($this->params['url']['filter_status']))
                $conditions['message-status'] = $this->params['url']['filter_status'];
            if (isset($this->params['url']['filter_from']) && !isset($this->params['url']['filter_to']))
            $conditions['timestamp'] = array('$gt'=>$this->scriptHelper->ConvertDateFormat($this->params['url']['filter_from'].' 00:00'));
            if (isset($this->params['url']['filter_to']) && !isset($this->params['url']['filter_from']))
                $conditions['timestamp'] = array('$lt'=>$this->scriptHelper->ConvertDateFormat($this->params['url']['filter_to'].' 00:00'));
            if (isset($this->params['url']['filter_from']) && isset($this->params['url']['filter_to']))
                $conditions['timestamp'] = array(
                    '$gt'=>$this->scriptHelper->ConvertDateFormat($this->params['url']['filter_from'].' 00:00'),
                    '$lt'=>$this->scriptHelper->ConvertDateFormat($this->params['url']['filter_to'].' 00:00')
                );
            if (isset($this->params['url']['filter_phone'])) {
                $phoneNumbers = explode(",", str_replace(" ", "",$this->params['url']['filter_phone']));
                if (sizeof($phoneNumbers) > 1) {
                    foreach ($phoneNumbers as $phoneNumber) {
                        if (strlen($phoneNumber) >= 12) {
                            $orConditions['participant-phone'] = $phoneNumber;
                            $or[] = $orConditions;
                        } else {
                            $regex = new MongoRegex("/^".$phoneNumber."/");
                            $orConditions['participant-phone'] = $regex;
                            $or[] = $orConditions;
                        }
                    }
                    $conditions['$or'] = $or;
                } else {                
                    if (strlen($phoneNumbers[0]) >= 12) {
                        $conditions['participant-phone'] = $phoneNumbers[0];
                    } else {
                        $regex = new MongoRegex("/^".$phoneNumbers[0]."/");
                        $conditions['participant-phone'] = $regex;
                    }
                }
            }
            //print_r($conditions);
        
            $this->paginate = array(
                'all',
                'conditions' => $conditions
            );
        }
        
        if (isset($this->params['url']['filter'])) {
            if ($this->params['url']['filter']=='non_matching_answers') { 
                $this->paginate = array(
                    'all',
                    'conditions' => array(
                        'message-type' => 'received',
                        'matching-answer' => null
                        )
                    );
            } else {
                $this->Session->setFlash('The filter "'.$this->params['url']['filter'].'" is not supported.');
            }
        }
        $statuses = $this->paginate();
        $this->set(compact('statuses'));
    }
    
    
    public function export()
    {        
        // Stop Cake from displaying action's execution time
        //Configure::write('debug',0);
        
        $data = $this->History->find('all', array(
            'fields' => array('participant-phone','message-type','message-status','message-content','timestamp')
            ));
        // Make the data available to the view (and the resulting CSV file)
        $this->set(compact('data'));
    }
    

}

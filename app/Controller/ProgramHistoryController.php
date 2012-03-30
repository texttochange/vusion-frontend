<?php

App::uses('AppController','Controller');
App::uses('History','Model');
App::uses('Script','Model');
App::uses('ProgramSetting','Model');

class ProgramHistoryController extends AppController
{

    public $uses    = array('History');
    var $components = array('RequestHandler');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time'
        );


    public function beforeFilter()
    {
        parent::beforeFilter();
        //For initial creation of the admin users uncomment the line below
        //$this->Auth->allow('*');
    }


    public function index()
    {
        $programTimezone = $this->ProgramSetting->find('programSetting', array('key' => 'timezone'));
    	$this->set(compact('programTimezone'));
    	
        if (isset($this->params['url']['non_matching_answers'])) {
            $script = $this->Script->find('active');
            $this->paginate = array(
                'scriptFilter',
                'script' => $script
                );
        }
        $statuses = $this->paginate();
        $this->set(compact('statuses'));
    }


    function constructClasses()
    {
        parent::constructClasses();
        
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->History        = new History($options);
        $this->Script         = new Script($options);
        $this->ProgramSetting = new ProgramSetting($options);
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

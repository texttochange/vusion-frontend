<?php

App::uses('AppController','Controller');
App::uses('History','Model');
App::uses('Script','Model');

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
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function index()
    {
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

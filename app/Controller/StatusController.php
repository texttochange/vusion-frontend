<?php

App::uses('AppController','Controller');
App::uses('ParticipantsState','Model');
App::uses('Script','Model');

class StatusController extends AppController
{

    public $uses    = array('ParticipantsState');
    var $components = array('RequestHandler');
    var $helpers    = array('Js' => array('Jquery'));


    public function beforeFilter()
    {
        parent::beforeFilter();
        //For initial creation of the admin users uncomment the line below
        //$this->Auth->allow('*');
    }


    public function index()
    {
        $statuses = $this->paginate();
        $this->set(compact('statuses'));
    }


    function constructClasses()
    {
        parent::constructClasses();
        
        $options                 = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->ParticipantsState = new ParticipantsState($options);
        $this->Script = new Script($options);
    }
    
    
    public function export()
    {        
        // Stop Cake from displaying action's execution time
        //Configure::write('debug',0);
        
        $data = $this->ParticipantsState->find('all', array(
            'fields' => array('participant-phone','message-type','message-status','message-content','timestamp')
            ));
        // Make the data available to the view (and the resulting CSV file)
        $this->set(compact('data'));
    }
    
    
    public function filter()
    {
        $statuses = $this->paginate();
        $scripts = $this->Script->find('all');
        $newStatuses = array();
        
        foreach ($statuses as $status) {
            foreach ($scripts as $script) {
                foreach ($script['Script']['script']['dialogues'] as $dialogue) {
                    if ($status['ParticipantsState']['dialogue-id']
                            and $status['ParticipantsState']['dialogue-id'] == $dialogue['dialogue-id']) {
                        foreach ($dialogue['interactions'] as $interaction) {
                            if ($status['ParticipantsState']['interaction-id']
                                    and $status['ParticipantsState']['interaction-id'] == $interaction['interaction-id']) {
                                if ($interaction['type-interaction'] == 'question-answer'
                                        and $interaction['type-question'] == 'close-question') {
                                    //print_r($interaction['answers']);
                                    //echo "<br />";
                                    //print_r($status['ParticipantsState']);
                                    //echo "<br /><br />";
                                    foreach ($interaction['answers'] as $answer) {
                                    	$response = $interaction['keyword']." ".$answer['choice'];
                                        if ($status['ParticipantsState']['message-content'] == $response) {
                                            //print_r($status['ParticipantsState']);
                                            //echo "<br />";
                                            $newStatuses[] = $status;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->set(compact('newStatuses'));
        /*
        $this->redirect(
            array(
                'program'=> $this->params['program'],
                'controller' => 'status',
                'action' => 'index',
                '?' => $this->request->data['ParticipantsState']['filter']
            )
        );
        */
    }


}

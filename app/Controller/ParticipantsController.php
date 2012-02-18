<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');
App::uses('ParticipantsState', 'Model');

class ParticipantsController extends AppController 
{


    function constructClasses() 
    {
        parent::constructClasses();
        
        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        
        $this->Participant = new Participant($options);
        $this->ParticipantsState = new ParticipantsState($options);
    }


    function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function index()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $participants = $this->paginate();
        $this->set(compact('programName', 'programUrl', 'participants'));        
    }


    public function add()
    {    
        if ($this->request->is('post')) {
            $this->Participant->create();
            if ($this->Participant->save($this->request->data)) {
                $this->Session->setFlash(__('The participant has been saved.'));
                $this->redirect(array(
                    'program' => $this->params['program'],  
                    'controller' => 'participants',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The participant could not be saved.'));
            }
        }
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $this->set(compact('programName', 'programUrl'));        
    }


    public function edit()
    {
        $id = $this->params['id'];
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Participant->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('program' => $programUrl, 'controller'=>'participants', 'action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->Participant->read(null, $id);
        }
        $this->set(compact('programName', 'programUrl')); 
    }


    public function delete ()
    {
    }

    
    public function view()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl = $this->params['program'];
        $id = $this->params['id'];
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read(null, $id);
        $histories = $this->ParticipantsState->find('participant', array(
                'phone' => $participant['Participant']['phone']
            ));
        $this->set(compact('programName', 
            'programUrl',
            'participant',
            'histories'));
    }


}

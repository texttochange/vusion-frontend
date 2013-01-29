<?php

App::uses('AppController', 'Controller');
App::uses('ProgramSetting', 'Model');
App::uses('Participant', 'Model');
App::uses('Schedule', 'Model');
App::uses('History', 'Model');
App::uses('UnmatchableReply', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('ShortCode', 'Model');


class ProgramsController extends AppController
{

    var $components = array('RequestHandler');
    public $helpers = array('Time', 'Js' => array('Jquery'));    
    var $uses = array('Program', 'Group');
    var $paginate = array(
        'limit' => 10,
        'order' => array(
            'Program.created' => 'desc'
            )
        );

    function constructClasses()
    {
        parent::constructClasses();

        $this->VumiRabbitMQ = new VumiRabbitMQ(
            Configure::read('vusion.rabbitmq')
            );
    }


    protected function _getPrograms()
    {
        $this->Program->recursive = -1;
        if ($this->Group->hasSpecificProgramAccess($this->Session->read('Auth.User.group_id'))) {
           return  $this->Program->find('authorized', array(
               'specific_program_access' => 'true',
               'user_id' => $this->Session->read('Auth.User.id')));

        }
        return $this->Program->find('all');
    }

    protected function _getProgram($programId)
    {
        $this->Program->recursive = -1;
        if ($this->Group->hasSpecificProgramAccess($this->Session->read('Auth.User.group_id'))) {
           return  $this->Program->find('authorized', array(
               'specific_program_access' => 'true',
               'user_id' => $this->Session->read('Auth.User.id'),
               'conditions' => array('id' => $programId)));

        }
        $this->Program->id = $programId;
        return $this->Program->read();
    }

    public function index() 
    {
        $this->Program->recursive = -1;
        if ($this->Group->hasSpecificProgramAccess($this->Session->read('Auth.User.group_id'))) {
           $this->paginate = array(
                'authorized',
                'specific_program_access' => 'true',
                'user_id' => $this->Session->read('Auth.User.id'),
                );
        }
        $programs      =  $this->paginate();
        $isProgramEdit = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                ),
            ), 'controllers/Programs/edit');
        foreach($programs as &$program) {
            $database           = $program['Program']['database'];
            $tempProgramSetting = new ProgramSetting(array('database' => $database));
            $shortcode          = $tempProgramSetting->find('programSetting', array('key'=>'shortcode'));
            if (isset($shortcode[0]['ProgramSetting']['value'])) {
                $this->ShortCode  = new ShortCode(array('database' => 'vusion'));
                $code            = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode[0]['ProgramSetting']['value']));
                $program['Program']['shortcode'] = ($code['ShortCode']['supported-internationally'] ? $code['ShortCode']['shortcode'] : $code['ShortCode']['country']."-".$code['ShortCode']['shortcode']);
            } 
            $tempParticipant                         = new Participant(array('database' => $database));
            $program['Program']['participant-count'] = $tempParticipant->find('count'); 
            $tempHistory                             = new History(array('database' => $database));
            $program['Program']['history-count']     = $tempHistory->find('count');
            $tempSchedule                            = new Schedule(array('database' => $database));
            $program['Program']['schedule-count']    = $tempSchedule->find('count');  
        }
        $tempUnmatchableReply = new UnmatchableReply(array('database'=>'vusion'));
        $this->set('unmatchableReplies', $tempUnmatchableReply->find(
            'all', 
            array('conditions' => array('direction' => 'incoming'), 
                'limit' => 8, 
                'order'=> array('timestamp' => 'DESC'))));
        $this->set(compact('programs', 'isProgramEdit'));
    }


    public function view($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program.'));
        }
        $this->set('program', $this->Program->read(null, $id));
    }


    public function add()
    {
        if ($this->request->is('post')) {
            $this->Program->create();
            if ($this->Program->save($this->request->data)) {
                $this->Session->setFlash(__('The program has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                ##Start the backend
                $this->_startBackendWorker(
                    $this->request->data['Program']['url'],
                    $this->request->data['Program']['database']
                    );
                ##Create necessary folders
                $programDirPath = WWW_ROOT . "files/programs/". $this->request->data['Program']['url'];
                if (!file_exists($programDirPath)) {
                    mkdir($programDirPath);
                    chmod($programDirPath, 0764);
                }
                ##Importing Dialogue and Request from another Program
                if (isset($this->request->data['Program']['import-dialogues-requests-from'])) {
                    $importFromProgramId = $this->request->data['Program']['import-dialogues-requests-from'];
                    $importFromProgram = $this->_getProgram($importFromProgramId);
                    if (isset($importFromProgram)) {
                         $importFromDialogueModel = new Dialogue(array('database' => $importFromProgram['Program']['database']));
                         $dialogues = $importFromDialogueModel->getActiveDialogues();
                         $importToDialogueModel = new Dialogue(array('database' => $this->request->data['Program']['database']));
                         foreach($dialogues as $dialogue){
                             $importToDialogueModel->create();
                             unset($dialogue['Dialogue']['_id']);
                             $importToDialogueModel->save($dialogue['Dialogue']);
                         }
                         $importFromRequestModel = new Request(array('database' => $importFromProgram['Program']['database']));
                         $requests = $importFromRequestModel->find('all');
                         $importToRequestModel = new Request(array('database' => $this->request->data['Program']['database']));
                         foreach($requests as $request){
                             $importToRequestModel->create();
                             unset($request['Request']['_id']);
                             $importToRequestModel->save($request['Request']);
                         }
                    }
                }
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        }
        
        $programs = $this->_getPrograms();
        $programOptions = array();
        foreach($programs as $program) 
            $programOptions[$program['Program']['id']] = $program['Program']['name']; 
        $this->set(compact('programOptions'));
        
    }


    /** 
    * function redirection to allow mocking in the testcases
    */
    protected function _startBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToCreateWorker($workerName,$databaseName);    	 
    }

    protected function _stopBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToRemoveWorker($workerName, $databaseName);    	 
    }


    public function edit($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program.'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Program->save($this->request->data)) {
                $this->Session->setFlash(__('The program has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->Program->read(null, $id);
        }
    }

    public function delete($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program.'));
        }
        $program = $this->Program->read();
        if ($this->Program->deleteProgram()) {
            $this->_stopBackendWorker(
                $program['Program']['url'],
                $program['Program']['database']);
            rmdir(WWW_ROOT . "files/programs/". $program['Program']['url']);
            $this->Session->setFlash(__('Program deleted.'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Program was not deleted.'), 
            'default',
            array('class' => "message failure")
            );
        $this->redirect(array('action' => 'index'));
    }


}

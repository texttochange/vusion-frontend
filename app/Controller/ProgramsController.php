<?php
App::uses('AppController', 'Controller');
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('UnmatchableReply', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('ShortCode', 'Model');
App::uses('CreditLog', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramsController extends AppController
{

    var $uses = array(
        'Program', 
        'Group',
        'ShortCode',
        'CreditLog');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')), 
        'LocalizeUtils', 
        'PhoneNumber', 
        'Paginator' => array(
            'className' => 'ProgramPaginator'), 
        'Stats',
        'UserAccess',
        'Filter');
    var $helpers = array('Time',
        'Js' => array('Jquery'), 
        'PhoneNumber');    
    var $paginate = array(
        'limit' => 10,
        'order' => array(
            'Program.created' => 'desc'
            )
        );
    
    
    function constructClasses()
    {
        parent::constructClasses();        
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    
    
    protected function _getPrograms()
    {
        $this->Program->recursive = -1;
        $user                     = $this->Auth->user();
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
            return  $this->Program->find('authorized', array(
                'specific_program_access' => 'true',
                'user_id' => $user['id']));
            
        }
        return $this->Program->find('all');
    }
    
    
    protected function _getProgram($programId)
    {
        $this->Program->recursive = -1;
        $user = $this->Auth->user();
        $conditions = array('conditions' => array('id' => $programId));
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
            $program = $this->Program->find('authorized', array(
                'specific_program_access' => 'true',
                'user_id' => $user['id']),
                $conditions);
        } else {
            $program = $this->Program->find('first', $conditions);
        }
        if ($program == array()) {
            return null;
        }
        return $program;
    }
    
    
    public function index() 
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
       
        $this->Filter->addDefaultCondition('status', 'is', 'running');
        $conditions = $this->Filter->getConditions($this->Program);
        
        // TODO move in the Program Paginator
        $this->Program->recursive = -1; 
        $user = $this->Auth->user();  
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
            $paginate = array(
                'type' => 'authorized', 
                'specific_program_access' => 'true',
                'user_id' => $user['id'],
                'conditions' => $conditions,
                'order' => array('created' => 'desc'));
        } else {
            $paginate = array(
                'type' => 'all', 
                'conditions' => $conditions,
                'order' => array('created' => 'desc'));
        }
        
        if ($this->Session->read('Auth.User.id') != null) {
            $isProgramEdit = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                    ),
                ), 'controllers/Programs/edit');
            
        }
        
        $tempUnmatchableReply = new UnmatchableReply(array('database'=>'vusion'));
        $unmatchableCondition = $this->UserAccess->getUnmatchableConditions();
        $andCondition         = array('$and' => array($unmatchableCondition, array('direction' => 'incoming')));
        $unmatchedConditon    = (!empty($unmatchableCondition)) ? $andCondition : array('direction' => 'incoming');
        $this->set('unmatchableReplies', $tempUnmatchableReply->find(
            'all', 
            array('conditions' => $unmatchedConditon, 
                'limit' => 8, 
                'order'=> array('timestamp' => 'DESC'))));
        
        // paginate using ProgramPaginator
        $this->paginate = $paginate;
        $programs       = $this->paginate();
        
        $countryIndexedByPrefix = $this->PhoneNumber->getCountriesByPrefixes();
        $this->set(compact('programs', 'isProgramEdit', 'countryIndexedByPrefix'));
    }
    
    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->Program->filterFields);
    }
    
    
    protected function _getFilterParameterOptions()
    {
        $shortcodes = $countries = array();
        $codes      = $this->ShortCode->find('all');
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $shortcodes[] = $code['ShortCode']['shortcode'];
                $countries[]  = $code['ShortCode']['country'];
            }
        }
        sort($countries);
        
        return array(
            'operator' => $this->Program->filterOperatorOptions,
            'shortcode' => (count($shortcodes)>0? array_combine($shortcodes, $shortcodes) : array()),
            'country' => (count($countries)>0? array_combine($countries, $countries) : array()),
            'program-status' => $this->Program->filterProgramStatusOptions
            );
    }
    
    
    public function view($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program.'));
        }
        $program = $this->Program->read(null, $id);
        $this->set('program', $program);
    }
    

    public function _ensureProgramDir($programDirPath)
    {
        if (!file_exists($programDirPath)) {
            mkdir($programDirPath); 
            chgrp($programDirPath, Configure::read('vusion.backendUser'));
            chmod($programDirPath, 0774);
        }
        return true;
    }


    public function add()
    {
        if ($this->request->is('post')) {
            $this->Program->create();
            if ($this->Program->save($this->request->data)) {
                $program = $this->request->data['Program'];
                $requestSuccess = true;
                $eventData = array(            
                    'programDatabaseName' => $program['database'],
                    'programName' => $program['name']);
                $this->UserLogMonitor->setEventData($eventData);
                
                $this->Session->setFlash(__('The program has been saved.'),
                    'default',
                    array('class'=>'message success')
                    );
                //Start the backend
                $this->_startBackendWorker(
                    $this->request->data['Program']['url'],
                    $this->request->data['Program']['database']
                    );
                //Create necessary folders
                $programDirPath = WWW_ROOT . "files/programs/". $this->request->data['Program']['url'];
                $this->_ensureProgramDir($programDirPath);
                if (!empty($this->request->data['Program']['import-dialogues-requests-from'])) {
                    $importFromProgramId = $this->request->data['Program']['import-dialogues-requests-from'];
                    $importFromProgram   = $this->_getProgram($importFromProgramId);
                    if (isset($importFromProgram)) {
                        $importFromDialogueModel = ProgramSpecificMongoModel::init(
                            'Dialogue', $importFromProgram['Program']['database'], true);
                        $dialogues = $importFromDialogueModel->getActiveDialogues();
                        $importToDialogueModel = ProgramSpecificMongoModel::init(
                            'Dialogue', $this->request->data['Program']['database'], true);
                        foreach($dialogues as $dialogue){
                            $importToDialogueModel->create();
                            unset($dialogue['Dialogue']['_id']);
                            $importToDialogueModel->save($dialogue['Dialogue']);
                        }
                        $importFromRequestModel = ProgramSpecificMongoModel::init(
                            'Request', $importFromProgram['Program']['database'], true);
                        $requests = $importFromRequestModel->find('all');
                        $importToRequestModel = ProgramSpecificMongoModel::init(
                            'Request', $this->request->data['Program']['database'], true);
                        foreach($requests as $request){
                            $importToRequestModel->create();
                            unset($request['Request']['_id']);
                            $importToRequestModel->save($request['Request']);
                        }
                    }
                }
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'));
            }
        }
        
        $programs       = $this->_getPrograms();
        $programOptions = array();
        foreach($programs as $program) 
            $programOptions[$program['Program']['id']] = $program['Program']['name']; 
        $this->set(compact('programOptions', 'requestSuccess'));
        
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
        if ($this->request->is('post')) {
            if ($this->Program->save($this->request->data)) {
                $program = $this->request->data['Program'];
                $eventData = array(            
                    'programDatabaseName' => $program['database'],
                    'programName' => $program['name']);
                $this->UserLogMonitor->setEventData($eventData);
                
                $this->Session->setFlash(__('The program has been saved.'),
                    'default',
                    array('class'=>'message success')
                    );
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'));
            }
        }
        $this->request->data = $this->Program->read(null, $id);
        
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
            $this->CreditLog->deletingProgram($program['Program']['name'], $program['Program']['database']);
            rmdir(WWW_ROOT . "files/programs/". $program['Program']['url']);
             $eventData = array(            
                    'programDatabaseName' => $program['Program']['database'],
                    'programName' => $program['Program']['name']);
                $this->UserLogMonitor->setEventData($eventData);   
            
            $this->Session->setFlash(__('Program %s was deleted.', $program['Program']['name']),
                'default', array('class'=>'message success'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Program %s was not deleted.', $program['Program']['name']));
        $this->redirect(array('action' => 'index'));
    }
    
    
    public function archive($id = null)
    {
        $this->Program->id = $id;
        if (!$this->Program->exists()) {
            throw new NotFoundException(__('Invalid program.'));
        }
        if ($this->Program->archive()) {
            $program = $this->Program->read();
            $this->_stopBackendWorker(
                $program['Program']['url'],
                $program['Program']['database']);
            $eventData = array(            
                    'programDatabaseName' => $program['Program']['database'],
                    'programName' => $program['Program']['name']);
                $this->UserLogMonitor->setEventData($eventData);
                
            $this->Session->setFlash(__('This program has been archived. All sending and receiving of message have stopped.'),
                'default', array('class'=>'message success'));
            $this->redirect(array(
                'action' => 'edit/'.$id));
        } else {
            $this->Session->setFlash(__('This program couldn\'t be archived.'));
            $this->redirect(array('action' => 'edit'));
        }
    }
    
    
}

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

    var $components = array('RequestHandler', 'LocalizeUtils');
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

        $this->_instanciateVumiRabbitMQ();
        $this->ShortCode  = new ShortCode(array('database' => 'vusion'));
    }


    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    protected function _getPrograms()
    {
        $this->Program->recursive = -1;
        $user = $this->Auth->user();
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
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
           return  $this->Program->find('authorized', array(
               'specific_program_access' => 'true',
               'user_id' => $user['id'],
               'conditions' => array('id' => $programId)));

        }
        $this->Program->id = $programId;
        return $this->Program->read();
    }

    public function index() 
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $this->Program->recursive = -1;
        
        $user = $this->Auth->user();
        
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
           $this->paginate = array(
                'authorized',
                'specific_program_access' => 'true',
                'user_id' => $user['id'],
                );
        }
        
        $conditions = $this->_getConditions();
        if (isset($conditions)) {
            $nameCondition = $this->_getNameSqlCondition($conditions);
        }
        
        if (isset($nameCondition) and $nameCondition != array()) {
            $this->paginate['conditions'] = $nameCondition;
        }
        
        $programs    =  $this->paginate();
        $allPrograms = $this->Program->find('all');
        
        if (isset($conditions['$or']) and !isset($nameCondition['OR']))
            $programsList =  $allPrograms;
        else
            $programsList =  $programs;

        if ($this->Session->read('Auth.User.id') != null) {
            $isProgramEdit = $this->Acl->check(array(
                'User' => array(
                    'id' => $this->Session->read('Auth.User.id')
                    ),
                ), 'controllers/Programs/edit');
        }
        
        $filteredPrograms = array();

        foreach($programsList as &$program) {
            $database           = $program['Program']['database'];
            $tempProgramSetting = new ProgramSetting(array('database' => $database));
            $shortcode          = $tempProgramSetting->find('programSetting', array('key'=>'shortcode'));
            if (isset($shortcode[0]['ProgramSetting']['value'])) {
                //$this->ShortCode  = new ShortCode(array('database' => 'vusion'));
                $code            = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode[0]['ProgramSetting']['value']));
                $program['Program']['shortcode'] = ($code['ShortCode']['supported-internationally'] ? $code['ShortCode']['shortcode'] : $code['ShortCode']['country']."-".$code['ShortCode']['shortcode']);                
            } 
            $tempParticipant                         = new Participant(array('database' => $database));
            $program['Program']['participant-count'] = $tempParticipant->find('count'); 
            $tempHistory                             = new History(array('database' => $database));
            $program['Program']['history-count']     = $tempHistory->find('count');
            $tempSchedule                            = new Schedule(array('database' => $database));
            $program['Program']['schedule-count']    = $tempSchedule->find('count');
            
            //$filterPrograms = $this->_matchProgramByShortcodeAndCountry($program, $conditions, $code);
            $filterPrograms = $this->Program->matchProgramByShortcodeAndCountry($program, $conditions, $code);
            if (count($filterPrograms)>0) {
                foreach ($filterPrograms as $fProgram) {
                    $filteredPrograms[] = $fProgram;
                }
            }
        }
        
        /*
        foreach($programs as &$program) {
            $program = array_merge($program, $this->_getProgramDetails($program));            
        }*/
        
        if (count($filteredPrograms)>0
            or (isset($conditions) && $nameCondition == array())
            or (isset($conditions['$and']) && $nameCondition != array() && count($filteredPrograms) == 0)) {
            $programsList = $filteredPrograms;
        }
        
        if (isset($conditions['$or']) and !isset($nameCondition['OR']) and $nameCondition != array()) {
            foreach($programs as &$program) {
                $program = array_merge($program, $this->_getProgramDetails($program));            
            }
            foreach ($programsList as $listedProgram) {
                array_push($programs, $listedProgram);
            }
        } else {
            $programs = $programsList;
        }
        
        print_r($this->paginate());
        
        $tempUnmatchableReply = new UnmatchableReply(array('database'=>'vusion'));
        $this->set('unmatchableReplies', $tempUnmatchableReply->find(
            'all', 
            array('conditions' => array('direction' => 'incoming'), 
                'limit' => 8, 
                'order'=> array('timestamp' => 'DESC'))));
        $this->set(compact('programs', 'isProgramEdit'));
    }
    
    /*
    protected function _getProgramDetails($programData)
    {
        $database           = $programData['Program']['database'];
        $tempProgramSetting = new ProgramSetting(array('database' => $database));
        $shortcode          = $tempProgramSetting->find('programSetting', array('key'=>'shortcode'));
        if (isset($shortcode[0]['ProgramSetting']['value'])) {
            //$this->ShortCode  = new ShortCode(array('database' => 'vusion'));
            $code            = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode[0]['ProgramSetting']['value']));
            $programData['Program']['shortcode'] = ($code['ShortCode']['supported-internationally'] ? $code['ShortCode']['shortcode'] : $code['ShortCode']['country']."-".$code['ShortCode']['shortcode']);                
        } 
        $tempParticipant                         = new Participant(array('database' => $database));
        $programData['Program']['participant-count'] = $tempParticipant->find('count'); 
        $tempHistory                             = new History(array('database' => $database));
        $programData['Program']['history-count']     = $tempHistory->find('count');
        $tempSchedule                            = new Schedule(array('database' => $database));
        $programData['Program']['schedule-count']    = $tempSchedule->find('count');
        
        return $programData;
    }
    */
    /*
    protected function _matchProgramByShortcodeAndCountry($program, $conditions, $codes)
    {
        $result = array();
        $countryMatch = false;
        $shortcodeMatch = false;
        foreach ($codes as $code) {
            if (isset($conditions['$and'])) {
                foreach ($conditions['$and'] as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key2 => $value2) {
                            if($key2 == 'country') {
                                if (strtolower($value2) == strtolower($code['country'])) {
                                    $countryMatch = true;
                                }
                            }
                            if($key2 == 'shortcode') {
                                if ($value2 == $code['shortcode']) {
                                    $shortcodeMatch = true;
                                }
                            }

                            if ($shortcodeMatch == true && $countryMatch == true) {
                                array_push($result, $program);
                            }
                        }
                    }
                }
            } elseif (isset($conditions['$or'])) {
                foreach ($conditions['$or'] as $key => $value) {
                    if (is_array($value)) {
                        if (isset($value['country'])) {
                            if (strtolower($value['country']) == strtolower($code['country'])) {
                                array_push($result, $program);                                
                            }
                        }
                        if (isset($value['shortcode'])) {
                            if ($value['shortcode'] == $code['shortcode']) {
                                array_push($result, $program);
                            }
                        }
                    }
                }                
            } else {
                if (isset($conditions['country'])) {
                    if (strtolower($conditions['country']) == strtolower($code['country'])) {
                        array_push($result, $program);
                    }
                } elseif (isset($conditions['shortcode'])) {
                    if ($conditions['shortcode'] == $code['shortcode']) {
                        array_push($result, $program);
                    }
                }
            }
        }
        return $result;
    }*/
    
    
    protected function _getNameSqlCondition($conditions)
    {
        $result = array();
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->_getNameSqlCondition($value));
            } else {
                if ($key == 'name LIKE' or $key == 'name') {
                    array_push($result, $conditions);
                }
            }
        }
        if (count($result) > 1) {
            $newResult['OR'] = $result;
            $result = $newResult;
        }
        return $result;
    }
    
    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->Program->filterFields);
    }


    protected function _getFilterParameterOptions()
    {
        $shortcodes = $this->ShortCode->getShortCodes();
        return array(
            'operator' => $this->Program->filterOperatorOptions,
            'shortcode' => (count($shortcodes)>0? array_combine($shortcodes, $shortcodes) : array()),
            );
    }


    protected function _getConditions()
    {
        $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));

        if (!isset($filter['filter_param'])) 
            return null;

        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->Program->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }     

        $this->set('urlParams', http_build_query($filter));

        return $this->Program->fromFilterToQueryConditions($filter);
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

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

    var $components = array('RequestHandler', 'LocalizeUtils', 'PhoneNumber', 'EmulatePaginator');
    public $helpers = array('Time', 'Js' => array('Jquery'), 'PhoneNumber');    
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
        if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        $this->ShortCode  = new ShortCode($options);
        //$this->_instanciateMongoModel('vusion');
    }


    /*protected function _instanciateMongoModel($vusionDB){
        $this->ShortCode  = new ShortCode(array('database' => $vusionDB));
    }*/


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
        $nameCondition = array();
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
//print_r($programs);
        foreach($programsList as &$program) {
            $programDetails = $this->_getProgramDetails($program);
            
            $program = array_merge($program, $programDetails['program']);
//print_r($programDetails);
            $filterPrograms = $this->Program->matchProgramByShortcodeAndCountry(
                $programDetails['program'],
                $conditions,
                $programDetails['shortcode']);
            if (count($filterPrograms)>0) {
                foreach ($filterPrograms as $fProgram) {//print_r($fProgram);
                    $filteredPrograms[] = $fProgram;
                }
            }
        }
        //print_r($filteredPrograms);
        if (count($filteredPrograms)>0
            or (isset($conditions) && $nameCondition == array())
            or (isset($conditions['$and']) && $nameCondition != array() && count($filteredPrograms) == 0)) {
            $programsList = $filteredPrograms;
        }
        
        if (isset($conditions['$or']) and !isset($nameCondition['OR']) and $nameCondition != array()) {
            foreach($programs as &$program) {
                $details = $this->_getProgramDetails($program);
                $program = array_merge($program, $details['program']);            
            }
            foreach ($programsList as $listedProgram) {
                if (!in_array($listedProgram, $programs))
                    array_push($programs, $listedProgram);
            }
        } else {
            $programs = $programsList;
        }
        //print_r($this->EmulatePaginator->paginate($programs));
        print_r($this->EmulatePaginator->tester($programs));
        //print_r($this->Paginator->Controller);
        $tempUnmatchableReply = new UnmatchableReply(array('database'=>'vusion'));
        $this->set('unmatchableReplies', $tempUnmatchableReply->find(
            'all', 
            array('conditions' => array('direction' => 'incoming'), 
                'limit' => 8, 
                'order'=> array('timestamp' => 'DESC'))));
        
        $this->set(compact('programs', 'isProgramEdit'));
    }
    

    protected function _getProgramDetails($programData)
    {//echo "enter _getProgDetails\n";
        $database           = $programData['Program']['database'];
        $tempProgramSetting = new ProgramSetting(array('database' => $database));
        $shortcode          = $tempProgramSetting->find('programSetting', array('key'=>'shortcode'));
 //echo "database = ".$database."\n";
 //print_r($shortcode);
//print_r($tempProgramSetting->find('all'));
//print_r($this->ShortCode);
        if (isset($shortcode[0]['ProgramSetting']['value'])) {
            $code            = $this->ShortCode->find('prefixShortCode', array('prefixShortCode'=> $shortcode[0]['ProgramSetting']['value']));
            //print_r($code);
            $programData['Program']['shortcode'] = ($code['ShortCode']['supported-internationally'] ? $code['ShortCode']['shortcode'] : $code['ShortCode']['country']."-".$code['ShortCode']['shortcode']);                
        }
//print_r($code);
        if ($this->params['ext']!='json') {
            $tempParticipant                             = new Participant(array('database' => $database));
            $programData['Program']['participant-count'] = $tempParticipant->find('count'); 
            $tempHistory                                 = new History(array('database' => $database));
            $programData['Program']['history-count']     = $tempHistory->find('count');
            $tempSchedule                                = new Schedule(array('database' => $database));
            $programData['Program']['schedule-count']    = $tempSchedule->find('count');
            
            $programDetails = array(
                'program' =>  $programData,
                'shortcode' => (isset($code)) ? $code : array()
                );
        }
        //print_r($programDetails);
        return $programDetails;
    }
    
    
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
        $countriesAndPrefixes = $this->PhoneNumber->getCountriesByPrefixes();
        foreach ($countriesAndPrefixes as $countryAndPrefix) {
            $countries[$countryAndPrefix] = $countryAndPrefix;
        }

        return array(
            'operator' => $this->Program->filterOperatorOptions,
            'shortcode' => (count($shortcodes)>0? array_combine($shortcodes, $shortcodes) : array()),
            'country' => $countries
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

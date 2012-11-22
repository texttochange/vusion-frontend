<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('Dialogue', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramParticipantsController extends AppController
{

    public $uses = array('Participant', 'History');


    function constructClasses() 
    {
        parent::constructClasses();

        $options = array('database' => ($this->Session->read($this->params['program']."_db")));

        $this->Participant       = new Participant($options);
        $this->History           = new History($options);
        $this->Schedule          = new Schedule($options);
        $this->Dialogue          = new Dialogue($options);
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->ProgramSetting = new ProgramSetting($options);
        $this->VumiRabbitMQ      = new VumiRabbitMQ(
            Configure::read('vusion.rabbitmq')
            );
        $this->DialogueHelper = new DialogueHelper();
    }


    function beforeFilter() 
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }

    
    public function index() 
    {
        $this->set('filterFieldOptions', $this->Participant->fieldFilters);
        
        $participants = $this->paginate();
        $this->set(compact('participants')); 
    }


    protected function _notifyUpdateBackendWorker($workerName, $participantPhone)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'participant', $participantPhone);
    }
    
    
    protected function _hasAllProgramSettings()
    {
        $shortCode = $this->ProgramSetting->find('getProgramSetting', array('key'=>'shortcode'));
        $timezone = $this->ProgramSetting->find('getProgramSetting', array('key'=>'timezone'));        
        if ($shortCode and $timezone) {
            return true;
        }
        return false;
    }
    

    public function add() 
    {
        $programUrl = $this->params['program'];
 
        if ($this->request->is('post')) {
            if ($this->_hasAllProgramSettings()) {
                $this->Participant->create();
                if ($this->Participant->save($this->request->data)) {
                    $participant = $this->Participant->read();
                    $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
                    $this->Session->setFlash(__('The participant has been saved.'),
                        'default',
                        array('class'=>'message success')
                        );
                    $this->redirect(array(
                        'program' => $programUrl,  
                        'controller' => 'programParticipants',
                        'action' => 'index'
                        ));
                } else {
                    $this->Session->setFlash(__('The participant could not be saved.'), 
                        'default',
                        array('class' => "message failure")
                        );
                }
            } else 
            $this->Session->setFlash(__('Please set the program settings then try again.'), 
                'default',
                array('class' => "message failure")
                );
        }        
    }

    
    ##we should not be able to edit a phone number
    public function edit() 
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read();
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Participant->save($this->request->data)) {
                $this->Schedule->deleteAll(
                    array('participant-phone' => $participant['Participant']['phone']),
                    false
                    );
                $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
                $this->Session->setFlash(__('The participant has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array('program' => $programUrl, 'action' => 'index'));
            } else {
                $this->Session->setFlash(__('The participant could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->Participant->read(null, $id);
        } 
    }

    
    public function delete() 
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        $currentPage = (isset($this->params['url']['current_page'])) ? $this->params['url']['current_page'] : '1';
        
        if (isset($this->params['url']['include'])) {
            $include = $this->params['url']['include'];
        }

        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant:') . $id);
        }
        $participant = $this->Participant->read();
        if ($this->Participant->delete()) {
            $this->Schedule->deleteAll(
                array('participant-phone' => $participant['Participant']['phone']),
                false);
            if (isset($include) && $include=="history") {
                $this->History->deleteAll(
                    array('participant-phone' => $participant['Participant']['phone']),
                    false);
            }
            $this->Session->setFlash(
                __('Participant and related schedule deleted.'),
                'default',
                array('class'=>'message success')
            );
            $this->redirect(
                array('program' => $programUrl,
                    'action' => 'index',
                    'page' => $currentPage
                    )
                );
        }
        $this->Session->setFlash(__('Participant was not deleted'), 
                'default',
                array('class' => "message failure")
                );
        $this->redirect(
            array(
                'program' => $programUrl,
                'action' => 'index'
                )
            );
    }

    
    public function view() 
    {
        $id = $this->params['id'];

        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read(null, $id);
        $dialoguesInteractionsContent = $this->Dialogue->getDialoguesInteractionsContent();
        $histories   = $this->History->getParticipantHistory(
                                    $participant['Participant']['phone'],
                                    $dialoguesInteractionsContent);

        //TODO: refactor to use similar $dialogueInteractionsContent
        #$activeInteractions   = $this->Dialogue->getActiveInteractions();
        #$activeDialogues = $this->Dialogue->getActiveDialogues();
        $schedules = $this->Schedule->getParticipantSchedules(
                                    $participant['Participant']['phone'],
                                    $dialoguesInteractionsContent);
       
        $this->set(compact('participant','histories', 'schedules'));
    }

    
    public function import()
    {
        require_once 'excel_reader2.php';
        //$data = new Spreadsheet_Excel_Reader("example.xls");

        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl  = $this->params['program'];

        if ($this->request->is('post')) {
            if ($this->_hasAllProgramSettings()) {
                if (!$this->request->data['Import']['file']['error']) {
                    $fileName = $this->request->data['Import']['file']["name"];
                    $ext      = end(explode('.', $fileName));
                    
                    if (!($ext == 'csv') and !($ext == 'xls') and !($ext == 'xlsx')) {
                        $this->Session->setFlash(__('This file format is not supported'), 
                            'default',
                            array('class' => "message failure")
                            );
                        return;
                    }
                    
                    $filePath = WWW_ROOT . "files/" . $programUrl; 
                    
                    if (!file_exists(WWW_ROOT . "files/".$programUrl)) {
                        //echo 'create folder: ' . WWW_ROOT . "files/".$programUrl;
                        mkdir($filePath);
                        chmod($filePath, 0777);
                    }
                    
                    /** in case the file has already been created, 
                    * the chmod function should not be called.
                    */
                    $wasFileAlreadyThere = false;
                    if(file_exists($filePath . DS . $fileName)) {
                        $wasFileAlreadyThere = true;
                    }
                    
                    copy($this->request->data['Import']['file']['tmp_name'],
                        $filePath . DS . $fileName);
                    
                    if(!$wasFileAlreadyThere) {
                        chmod($filePath . DS . $fileName, 0777);
                    }
                    
                    if ($ext == 'csv') {
                        $entries = $this->processCsv($programUrl, $filePath, $fileName);
                    } else if ($ext == 'xls' || $ext == 'xlsx') {
                        $entries = $this->processXls($programUrl, $filePath, $fileName);
                    }
                }
            } else 
            $this->Session->setFlash(__('Please set the program settings then try again.'), 
                'default',
                array('class' => "message failure")
                );
        } 
        $this->set(compact('entries'));
    }

    
    private function processCsv($programUrl, $filePath, $fileName)
    {
        $importedParticipants = fopen($filePath . DS . $fileName,"r");
        $entries              = array();
        $count                = 0;
        $hasHeaders           = false;
        $headers              = array();
        

        while (!feof($importedParticipants)) { 
            $entries[] = fgets($importedParticipants);
            if ($count == 0) {
                   $headers = explode(",", $entries[$count]);
                   if (strcasecmp(trim(trim($headers[0],'"')), 'phone') ==0) {
                       $hasHeaders = true;
                       $count++;
                       continue;
                   } else {
                       if (count($headers) > 1) {
                            $this->Session->setFlash(__("The file cannot be imported. The first line should be label names, the first label must be 'phone'."), 
                                'default',
                                array('class' => "message failure")
                                );
                           return;
                       }
                   }
            }
            if ($entries[$count]) {
                $this->Participant->create();
                $participant          = array();
                $explodeLine          = explode(',', $entries[$count]);
                $participant['phone'] = trim(trim($explodeLine[0]), '"');
                $col = 0;
                foreach ($headers as $label) {
                    $label = trim(trim($label), '"');
                    $value = trim(trim($explodeLine[$col]), '"');
                    if ($value == '') {
                        continue;
                    }
                    if (strtolower($label) != 'phone') {
                        $participant['profile'][] = array(
                            'label' => $label, 
                            'value' => $value,
                            'raw' => null);
                    }
                    $col++;
                }
                $savedParticipant = $this->Participant->save($participant);
                if ($savedParticipant) {
                    $entries[$count] = $savedParticipant['Participant']['phone'].__(", Insert ok");
                    $this->_notifyUpdateBackendWorker($programUrl, $savedParticipant['Participant']['phone']);    
                } else {
                    $entries[$count] = $participant['phone'].", ".$this->Participant->validationErrors['phone'][0]. " line ".($count+1);
                }
                
            }
            $count++; 
        }
        return $entries;
    }

    
    private function processXls($programUrl, $filePath, $fileName)
    {
        $headers = array();
        $data = new Spreadsheet_Excel_Reader($filePath . DS . $fileName);
        $hasLabels = false;
        if (strcasecmp('phone', $data->val(1,'A')) == 0) {
            $hasLabels = true;
            for ( $j = 2; $j <= $data->colcount($sheet_index=0); $j++) {
                if ($data->val(1, $j)==null){
                    break;
                }
                $headers[] = $data->val(1, $j);
            }
        } else {
            if ($data->val(1, 'B')!=null){
                $this->Session->setFlash(__("The file cannot be imported. The first line should be label names, the first label must be 'phone'."), 
                    'default',
                    array('class' => "message failure")
                    );
                return;
            } 
        }
        for ( $i = ($hasLabels) ? 2 : 1; $i <= $data->rowcount($sheet_index=0); $i++) {
            if ($data->val($i,'A')==null){
                break;
            }
            $this->Participant->create();
            $participant['phone'] = $data->val($i,'A');
            $col = 2;
            foreach ($headers as $header) {
                if ($data->val($i,$col)==null) 
                    continue;
                $participant['profile'][] = array(
                    'label' => $header,
                    'value' => $data->val($i,$col),
                    'raw' => null);
                $col++;
            }
            //for view report
            $savedParticipant = $this->Participant->save($participant);
            if ($savedParticipant) {
                $entries[$i] = $savedParticipant['Participant']['phone'] . ", Insert ok"; 
                $this->_notifyUpdateBackendWorker($programUrl, $savedParticipant['Participant']['phone']);    
            } else {
                $entries[$i] = $participant['phone'].", ".$this->Participant->validationErrors['phone'][0]. " line ".$i;
            }
        }
        return $entries;
    }
    
    
}

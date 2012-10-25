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
        $participants = $this->paginate();
        $this->set(compact('participants'));        
    }


    protected function _notifyUpdateBackendWorker($workerName)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName);
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
                    $this->_notifyUpdateBackendWorker($programUrl);
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
                $this->_notifyUpdateBackendWorker($programUrl);
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
                false
                );
            $this->Session->setFlash(
                __('Participant and related schedule deleted.'),
                'default',
                array('class'=>'message success')
            );
            $this->redirect(
                array('program' => $programUrl,
                    'action' => 'index'
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
                        $entries = $this->processCsv($filePath, $fileName);
                    } else if ($ext == 'xls' || $ext == 'xlsx') {
                        $entries = $this->processXls($filePath, $fileName);
                    }
                    $this->_notifyUpdateBackendWorker($programUrl);
                }
            } else 
            $this->Session->setFlash(__('Please set the program settings then try again.'), 
                'default',
                array('class' => "message failure")
                );
        } 
        $this->set(compact('entries'));
    }

    
    private function processCsv($filePath, $fileName)
    {
        $importedParticipants = fopen($filePath . DS . $fileName,"r");
        $entries              = array();
        $count                = 0;
        $headers              = array();
        

        while (!feof($importedParticipants)) { 
            $entries[] = fgets($importedParticipants);
            if ($count == 0) {
                   $headers = explode(",", $entries[$count]); 
            }
            if ($count > 0 && $entries[$count]) {
                $this->Participant->create();
                $participant          = array();
                $explodeLine          = explode(",", $entries[$count]);
                $participant['phone'] = $explodeLine[0];
                $row = 0;
                foreach ($headers as $label) {
                    $label = trim($label);
                    $label = trim($label, '"');
                    if (strtolower($label) != 'phone') {
                        $participant['profile'][] = array(
                            'label' => $label, 
                            'value' => trim(trim($explodeLine[$row]), '"'));
                    }
                    $row++;
                }
                $savedParticipant = $this->Participant->save($participant);
                if ($savedParticipant) {
                    $entries[$count] = $savedParticipant['Participant']['phone'].__(", Insert ok"); 
                } else {
                    $entries[$count] = $participant['phone'].", ".$this->Participant->validationErrors['phone'][0]. " line ".($count+1);
                }
                
            }
            $count++; 
        }
        return $entries;
    }

    
    private function processXls($filePath, $fileName)
    {
        $headers = array();
        $data = new Spreadsheet_Excel_Reader($filePath . DS . $fileName);
        for ( $j = 2; $j <= $data->colcount($sheet_index=0); $j++) {
            $headers[] = $data->val(1, $j);
        }
        for ( $i = 2; $i <= $data->rowcount($sheet_index=0); $i++) {
            $this->Participant->create();
            $participant['phone'] = $data->val($i,'A');
            $col = 2;
            foreach ($headers as $header) {
                $participant['profile'][] = array(
                    'label' => $header,
                    'value' => $data->val($i,$col));
            }
            //for view report
            $savedParticipant = $this->Participant->save($participant);
            if ($savedParticipant) {
                $entries[$i] = $savedParticipant['Participant']['phone'] . ", Insert ok"; 
            } else {
                $entries[$i] = $participant['phone'].", ".$this->Participant->validationErrors['phone'][0]. " line ".$i;
            }
        }
        return $entries;
    }
    
    
}

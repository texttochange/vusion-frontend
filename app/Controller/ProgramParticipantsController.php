<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramParticipantsController extends AppController
{

    public $uses = array('Participant', 'History');
    var $components = array('RequestHandler');
    var $helpers    = array(
        'Js' => array('Jquery')
        );


    function constructClasses() 
    {
        parent::constructClasses();

        $options = array('database' => ($this->Session->read($this->params['program']."_db")));

        $this->Participant       = new Participant($options);
        $this->History           = new History($options);
        $this->Schedule          = new Schedule($options);
        $this->Dialogue          = new Dialogue($options);
        $this->DialogueHelper    = new DialogueHelper();
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
        $dialoguesContent = $this->Dialogue->getDialoguesInteractionsContent();
        $this->set('filterDialogueConditionsOptions', $dialoguesContent);
     
        $paginate = array('all');

        if (isset($this->params['named']['sort'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

        $conditions = $this->_getConditions();
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
  
        $this->paginate = $paginate;
        $participants = $this->paginate();
        $this->set(compact('participants')); 
    }

    public function download()
    {
        $programUrl = $this->params['program'];
        $fileName = $this->params['url']['file'];
        
        $fileFullPath = WWW_ROOT . "files/programs/" . $programUrl . "/" . $fileName; 
            
        if (!file_exists($fileFullPath)) {
            throw new NotFoundException();
        }

        $this->response->header("X-Sendfile: $fileFullPath");
        $this->response->header("Content-type: application/octet-stream");
        $this->response->header('Content-Disposition: attachment; filename="' . basename($fileFullPath) . '"');
        $this->response->send();
    }


    public function export() 
    {
        $programUrl = $this->params['program'];

        $this->set('filterFieldOptions', $this->Participant->fieldFilters);
        $dialoguesContent = $this->Dialogue->getDialoguesInteractionsContent();
        $this->set('filterDialogueConditionsOptions', $dialoguesContent);
     
        $paginate = array(
                    'all', 
                    'limit' => 500);

        if (isset($this->params['named']['sort'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

        $conditions = $this->_getConditions();
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        try{
            ##First a tmp file is created
            $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
            
            ##TODO: the folder creation should be managed at program creation
            if (!file_exists($filePath)) {
                //echo 'create folder: ' . WWW_ROOT . "files/".$programUrl;
                mkdir($filePath);
                chmod($filePath, 0777);
            }

            $programNow = $this->ProgramSetting->getProgramTimeNow();
            $programName = $this->Session->read($programUrl.'_name');
            $fileName = $programName . "_participants_" . $programNow->format("Y-m-d_H-i-s") . ".csv";
            
            $fileFullPath = $filePath . "/" . $fileName;  
            
            $handle = fopen($fileFullPath, "w");
            
            $headers = $this->Participant->getExportHeaders($conditions);
            ##Second we write the headers
            fputcsv($handle, $headers,',' , '"' );

            ##Third we extract the data and copy them in the file
            
            $participantCount = $this->Participant->find('count', array('conditions'=> $conditions));
            $pageCount = intval(ceil($participantCount / $paginate['limit']));
            for($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate = $paginate;
                $participants = $this->paginate();
                foreach($participants as $participant) {
                    $line = array();
                    foreach($headers as $header) {
                        if (in_array($header, array('phone', 'last-optin-date', 'last-optout-date'))) {
                                $line[] = $participant['Participant'][$header];
                        } else if ($header == 'tags') {
                            $line[] = implode(', ', $participant['Participant'][$header]);         
                        } else {
                            $value = $this->_searchProfile($participant['Participant']['profile'], $header);
                            $line[] = $value;
                        }
                    }
                    fputcsv($handle, $line,',' , '"' );
                }
            }
            
            $this->set(compact('fileName'));
        } catch (Exception $e) {
            $this->set('errorMessage', $e->getMessage()); 
        }
    }


    protected function _searchProfile($array, $labelKey)
    {
        $results = array();

        foreach($array as $label) {
            if ($label['label'] == $labelKey)
                return $label['value'];
        }
        
        return null;
    }


    protected function _getConditions()
    {
        $conditions = null;
        
        $onlyFilterParams = array_intersect_key($this->params['url'], array_flip(array('filter_param')));

        if (!isset($onlyFilterParams['filter_param'])) 
            return $conditions;
       
        $urlParams = http_build_query($onlyFilterParams);
        $this->set('urlParams', $urlParams);
        
        foreach($onlyFilterParams['filter_param'] as $onlyFilterParam) {
            if ($onlyFilterParam[1] == 'enrolled' && isset($onlyFilterParam[2])) {
                $conditions['enrolled.dialogue-id'] = $onlyFilterParam[2];
            } elseif ($onlyFilterParam[1] == 'not-enrolled' && isset($onlyFilterParam[2])) {
                $conditions['enrolled.dialogue-id']['$ne'] = $onlyFilterParam[2];
            } elseif ($onlyFilterParam[1] == 'optin') { 
                $conditions['session-id'] = array('$ne' => null);
            } elseif ($onlyFilterParam[1]=='optin-date-from' && isset($onlyFilterParam[2])) {
                $conditions['last-optin-date']['$gt'] = $this->DialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1]=='optin-date-to' && isset($onlyFilterParam[2])) {
                $conditions['last-optin-date']['$lt'] = $this->DialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1] == 'optout') { 
                $conditions['session-id'] = null;
            } elseif ($onlyFilterParam[1]=='optout-date-from' && isset($onlyFilterParam[2])) {
                $conditions['last-optout-date']['$gt'] = $this->DialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1]=='optout-date-to' && isset($onlyFilterParam[2])) {
                $conditions['last-optout-date']['$lt'] = $this->DialogueHelper->ConvertDateFormat($onlyFilterParam[2]);
            } elseif ($onlyFilterParam[1] == 'phone' && isset($onlyFilterParam[2])) {
                $phoneNumbers = explode(",", str_replace(" ", "", $onlyFilterParam[2]));
                if ($phoneNumbers) {
                    if (count($phoneNumbers) > 1) {
                        $or = array();
                        foreach ($phoneNumbers as $phoneNumber) {
                            $regex = new MongoRegex("/^\\".$phoneNumber."/");
                            $or[] = array('phone' => $regex);
                        }
                        $conditions['$or'] = $or;
                    } else {
                        $conditions['phone'] = new MongoRegex("/^\\".$phoneNumbers[0]."/");
                    }
                }
            } elseif ($onlyFilterParam[1]=='tag' && isset($onlyFilterParam[2])) {
                $conditions['tags'] = $onlyFilterParam[2];
            } elseif ($onlyFilterParam[1] == 'label' && isset($onlyFilterParam[2]) && isset($onlyFilterParam[3])) {
                $conditions['profile.label'] = $onlyFilterParam[2];
                $conditions['profile.value'] = $onlyFilterParam[3];
            } else {
                $this->Session->setFlash(__('The parameter(s) for "%s" filtering are missing.',$onlyFilterParam[1]), 
                'default',
                array('class' => "message failure")
                );
            }
        }
        
        return $conditions;
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
    
    
    protected function _getSelectOptions()
    {
        $selectOptions = array();
        $dialogues = $this->Dialogue->getDialoguesInteractionsContent();
        foreach ($dialogues as $key => $value) {
            $selectOptions[$key] = $value['name'];
        }
        return $selectOptions;
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
        $selectOptions = $this->_getSelectOptions();
        $oldEnrolls = array();
        $enrolled = $participant['Participant']['enrolled'];
        foreach ($selectOptions as $key => $option) {
            foreach ($enrolled as $enrolledIn) {
                if ($key == $enrolledIn['dialogue-id'])
                    $oldEnrolls[] = $enrolledIn['dialogue-id'];
            }
        }
        $this->set(compact('oldEnrolls', 'selectOptions'));
    }

    
    public function massDelete() {
        
        $programUrl = $this->params['program'];
     
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array();
        $params = array('fields' => array('phone'));

        $conditions = $this->_getConditions($defaultConditions);
        if ($conditions) {
            $params += array('conditions' => $conditions);
        } else {
            $conditions = true;
        }

        $count = 0;
        $participants = $this->Participant->find('all', $params);
        foreach($participants as $participant) {
             $this->Schedule->deleteAll(
                array('participant-phone' => $participant['Participant']['phone']),
                false);
             $count++;
        };
        $result = $this->Participant->deleteAll(
            $conditions, 
            false);
 
        $this->Session->setFlash(
                __('%s Participants have been deleted.', $count),
                'default',
                array('class'=>'message success')
                );
        
        if (isset($this->viewVars['urlParams'])) {
            $this->redirect(array(  
                'program' => $programUrl,
                'controller' => 'programParticipants',
                'action' => 'index',
                '?' => $this->viewVars['urlParams']));
        } else {
               $this->redirect(array(  
                'program' => $programUrl,
                'controller' => 'programParticipants',
                'action' => 'index'));
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
                    'page' => $currentPage,
                    '?' => $this->params['url'],
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
                'action' => 'index',
                'page' => $currentPage,
                )
            );
    }
    
    
    protected function _getAutoEnrollments($programTime)
    {
        $condition = array('condition' => array('auto-enrollment'=>'all'));
        $autoEnrollDialogues = $this->Dialogue->getActiveDialogues($condition);
        if ($autoEnrollDialogues == null) {
            $enrolled = array();
        } else {
            foreach ($autoEnrollDialogues as $autoEnroll) {
                $enrolled[] = array(
                    'dialogue-id' => $autoEnroll['dialogue-id'],
                    'date-time' => $programTime->format("Y-m-d\TH:i:s")
                    );
            }
        }
        return $enrolled;
    }
    
    
    public function optin()
    {
        $programUrl = $this->params['program'];
        $id = $this->params['id'];

        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read(null, $id);
        if ($this->request->is('post')) {
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            
            $tags = $participant['Participant']['tags'];
            $profile = $participant['Participant']['profile'];
            
            $this->Participant->reset($participant['Participant']);
            
            $participant['Participant']['session-id'] = $this->Participant->gen_uuid();
            $participant['Participant']['last-optin-date'] = $programNow->format("Y-m-d\TH:i:s");
            $participant['Participant']['last-optout-date'] = null;
            $participant['Participant']['enrolled'] = $this->_getAutoEnrollments($programNow);
            $participant['Participant']['tags'] = $tags;
            $participant['Participant']['profile'] = $profile;
            
            if ($this->Participant->save($participant['Participant'])) {
                $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
                $this->Session->setFlash(__('The participant has been optin.'),
                    'default',
                    array('class'=>'message success')
                    );
                $this->redirect(array(
                    'program' => $programUrl,  
                    'controller' => 'programParticipants',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The participant could not be reset.'), 
                    'default',
                    array('class' => "message failure")
                    );
            }
        }
    }
    
    
    public function optout()
    {
        $programUrl = $this->params['program'];
        $id = $this->params['id'];

        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read(null, $id);
        if ($this->request->is('post')) {
            $this->Schedule->deleteAll(
                array('participant-phone' => $participant['Participant']['phone']),
                false);
            
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            
            $participant['Participant']['session-id'] = null;
            $participant['Participant']['last-optout-date'] = $programNow->format("Y-m-d\TH:i:s");
            if ($this->Participant->save($participant['Participant'])) {
                $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
                $this->Session->setFlash(__('The participant has been optout.'),
                    'default',
                    array('class'=>'message success')
                    );
                $this->redirect(array(
                    'program' => $programUrl,  
                    'controller' => 'programParticipants',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The participant could not be reset.'), 
                    'default',
                    array('class' => "message failure")
                    );
            }
        }
    }
    
    
    public function reset()
    {
        $programUrl = $this->params['program'];
        $id = $this->params['id'];

        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read(null, $id);
        if ($this->request->is('post')) {
            $this->Schedule->deleteAll(
                array('participant-phone' => $participant['Participant']['phone']),
                false);
            $programNow = $this->ProgramSetting->getProgramTimeNow();            

            $resetParticipant = $this->Participant->reset($participant['Participant']);            
            $resetParticipant['enrolled'] = $this->_getAutoEnrollments($programNow);
            if ($this->Participant->save($resetParticipant)) {
                $this->_notifyUpdateBackendWorker($programUrl, $resetParticipant['phone']);
                $this->Session->setFlash(__('The participant has been reset.'),
                    'default',
                    array('class'=>'message success')
                    );
                $this->redirect(array(
                    'program' => $programUrl,  
                    'controller' => 'programParticipants',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The participant could not be reset.'), 
                    'default',
                    array('class' => "message failure")
                    );
            }  
        }
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

        $schedules = $this->Schedule->getParticipantSchedules(
                                    $participant['Participant']['phone'],
                                    $dialoguesInteractionsContent);
       
        $this->set(compact('participant','histories', 'schedules'));
    }

    protected function _getTags($tags) 
    {
        $tags = trim(stripcslashes($tags));
        return explode(", ", $tags);
    }

    protected function _validateTag($tag)
    {
        return preg_match("/^[a-zA-Z0-9\s\']*$/", $tag);
    }

    public function import()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl  = $this->params['program'];

        if ($this->request->is('post')) {
            if (!$this->_hasAllProgramSettings()) {
                $this->Session->setFlash(__('Please set the program settings then try again.'), 
                    'default',
                    array('class' => "message failure"));
                return;
            }
            
            if ($this->request->data['Import']['file']['error'] != 0) {
                if ($this->request->data['Import']['file']['error'] == 4) 
                    $message = __("Please select a file.");
                else 
                    $message = __('Error while uploading the file: %s.', $this->request->data['Import']['file']['error']);
                $this->Session->setFlash($message, 
                    'default',
                    array('class' => "message failure"));
                return;
            }

            $tags = array('imported');
            if (isset($this->request->data['Import']['tags'])) {
                $userTags = $this->_getTags($this->request->data['Import']['tags']);
                $userTags = array_filter($userTags);
                if (empty($userTags)) {
                    /*$this->Session->setFlash(__('Error: tag must not be empty.'), 
                        'default',
                        array('class' => "message failure"));
                    return;*/
                    $userTags = array();
                }
                foreach($userTags as $userTag) {
                    if (!$this->_validateTag($userTag)) {
                        $this->Session->setFlash(__('Error a tag is not valide: %s.', $userTag), 
                            'default',
                            array('class' => "message failure"));
                        return;
                    }
                }
                $tags = array_merge($tags, $userTags);
            }
            
            $fileName = $this->request->data['Import']['file']["name"];
            $ext      = end(explode('.', $fileName));
            
            if (!($ext == 'csv') and !($ext == 'xls')) {
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
                $entries = $this->processCsv($programUrl, $filePath, $fileName, $tags);
            } else if ($ext == 'xls') {
                $entries = $this->processXls($programUrl, $filePath, $fileName, $tags);
            }

        }
        $this->set(compact('entries'));
    }

    
    private function processCsv($programUrl, $filePath, $fileName, $tags)
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
                       $headers = array();
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
                $participant['tags'] = $tags;
                $savedParticipant = $this->Participant->save($participant);
                if ($savedParticipant) {
                    $entries[$count] = $savedParticipant['Participant']['phone'].__(", Insert ok");
                    $this->_notifyUpdateBackendWorker($programUrl, $savedParticipant['Participant']['phone']);    
                } else {
                    $entries[$count] = $participant['phone'].", ";
                    foreach ($this->Participant->validationErrors as $key => $error) {
                        $entries[$count] .= $this->Participant->validationErrors[$key][0]. " line ".($count+1)."<br />";
                    }
                }
                
            }
            $count++; 
        }
        return $entries;
    }

    
    private function processXls($programUrl, $filePath, $fileName, $tags)
    {
        require_once 'excel_reader2.php';

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
            $participant          = array();
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
            $participant['tags'] = $tags;
            $savedParticipant = $this->Participant->save($participant);
            if ($savedParticipant) {
                $entries[$i] = $savedParticipant['Participant']['phone'] . ", Insert ok"; 
                $this->_notifyUpdateBackendWorker($programUrl, $savedParticipant['Participant']['phone']);    
            } else {
                $entries[$i] = $participant['phone'].", ";
                foreach ($this->Participant->validationErrors as $key => $error) {
                    $entries[$i] .= $this->Participant->validationErrors[$key][0]. " line ".$i."<br />";
                }
            }
        }
        return $entries;
    }
    
    
}

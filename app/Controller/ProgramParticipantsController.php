<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('Participant','Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('Dialogue', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('VusionException', 'Lib');
App::uses('FilterException', 'Lib');


class ProgramParticipantsController extends BaseProgramSpecificController
{
    
    var $uses = array(
        'Participant',
        'History',
        'Schedule',
        'Dialogue',
        'UnattachedMessage',
        'ProgramSetting');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')), 
        'LocalizeUtils',
        'Filter',
        'UserLogMonitor',
        'Paginator' => array(
            'className' => 'BigCountPaginator'),
        'ProgramAuth',
        'ArchivedProgram');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Paginator' => array(
            'className' => 'BigCountPaginator'));
    

    function constructClasses() 
    {
        parent::constructClasses();
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    public function beforeFilter() 
    {
        parent::beforeFilter();
    }

    
    public function index() 
    {      
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $requestSuccess = true;
        
        $order = array();
        if (isset($this->params['named']['sort']) &&  isset($this->params['named']['direction'])) {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        $conditions = $this->Filter->getConditions($this->Participant, array(), array('Schedule' => $this->Schedule));

        if ($this->params['ext'] === 'csv') {
            $participants = $this->Participant->find(
                'allSafeJoin', 
                array(
                    'conditions' => $conditions,
                    'limit'=> 10000),
                array('order' => $order));
        } else {
            $paginate       = array('allSafeJoin');
            if (isset($order)) {
                $paginate['order'] = $order;
            }
            if ($conditions != null) {
                $paginate['conditions'] = $conditions;
            }
            $this->paginate = $paginate;
            $participants   = $this->paginate('Participant');
        }
        $this->set(compact('participants', 'requestSuccess'));
    }
    
    
    protected function _getFilterFieldOptions()
    {   
        $filters = $this->Participant->getFilters();
        return $this->LocalizeUtils->localizeLabelInArray($filters);
    }
    
    
    protected function _getFilterParameterOptions()
    {        
        return array(
            'operator' => $this->Participant->filterOperatorOptions,
            'dialogue' => $this->Dialogue->getDialoguesInteractionsContent(),
            'tag' => array('_ajax' => 'ready'),
            'label' => array('_ajax' => 'ready'));
    }
    
    
    public function getFilterParameterOptions()
    {
        $requestSuccess = true;
        
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        if (!isset($this->request->query['parameter'])) {
            throw new Exception(__("The required filter parameter option is missing."));
        }
        
        $requestedParameterOption = $this->request->query['parameter'];
        
        switch ($requestedParameterOption) {
        case "tag":
            $results = $this->Participant->getDistinctTags();
            break;
        case "label":
            ## Set a 5 minutes timeout on the mapreduce
            $results = $this->Participant->getDistinctLabels(array(), 5 * 60 * 1000);  
            break;
        default:
            throw new Exception(__("The requested parameter option %s is not supported.", $requestedParameterOption));
        }        
        $this->set(compact('results', 'requestSuccess'));
    }
    
    
    public function download()
    {
        $programUrl   = $this->params['program'];
        $fileName     = $this->params['url']['file'];        
        $fileFullPath = WWW_ROOT . "files/programs/" . $programUrl . "/" . $fileName; 
        
        if (!file_exists($fileFullPath)) {
            throw new NotFoundException();
        }
        
        $this->response->header("X-Sendfile: $fileFullPath");
        $this->response->header("Content-type: application/octet-stream");
        $this->response->header('Content-Disposition: attachment; filename="' . basename($fileFullPath) . '"');
        $this->response->send();
    }
    
    
    public function massTag()
    {       
        $programUrl = $this->params['program'];
        $conditions = $this->Filter->getConditions($this->Participant);
        
        if ($this->request->is('get')) {
            if (!$conditions) {
                $conditions = array();
            }
            if ($this->Participant->addMassTags($this->params['url']['tag'], $conditions)) {
                $this->_notifyBackendMassTag($programUrl, $this->params['url']['tag'], $conditions);
                $this->Session->setFlash(__('The tag %s has been added successfully.', $this->params['url']['tag']),
                    'default', array('class'=>'message success'));
            } else {                
                $this->Session->setFlash(__('The tag %s could not be added.', $tag));
            }           
        }
        
        $redirectUrl = array(  
            'program' => $programUrl,
            'controller' => 'programParticipants',
            'action' => 'index'); 
        if (isset($this->viewVars['urlParams'])) {
            $redirectUrl['?'] = $this->viewVars['urlParams'];
        }
        $this->redirect($redirectUrl);
    }
    
    
    public function massUntag()
    {   
        $programUrl = $this->params['program'];
        $conditions = $this->Filter->getConditions($this->Participant);
        
        if ($this->request->is('get')) {
            if (!$conditions) {
                $conditions = array();
            } 
            if ($this->Participant->deleteMassTags($this->params['url']['tag'], $conditions)) {
                $this->_notifyBackendMassUntag($programUrl, $this->params['url']['tag']);
                $this->Session->setFlash(__('The tag %s has been removed successfully.', $this->params['url']['tag']),
                    'default', array('class'=>'message success'));
            } else {
                $this->Session->setFlash(__('The tag %s could not be removed.', $this->params['url']['tag']));
            }
        } 
        
        $redirectUrl = array(  
            'program' => $programUrl,
            'controller' => 'programParticipants',
            'action' => 'index'); 
        if (isset($this->viewVars['urlParams'])) {
            $redirectUrl['?'] = $this->viewVars['urlParams'];
        }
        $this->redirect($redirectUrl);
    }
    
    
    public function export() 
    {
        $programUrl    = $this->params['program'];
        $requestSucces = false;
        
        $this->set('filterFieldOptions', $this->Participant->fieldFilters);
        $dialoguesContent = $this->Dialogue->getDialoguesInteractionsContent();
        $this->set('filterDialogueConditionsOptions', $dialoguesContent);
        
        $paginate = array(
            'all', 
            'limit' => 500,
            'maxLimit' => 500);
        
        if (isset($this->params['named']['sort'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $conditions = $this->Filter->getConditions($this->Participant);
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        try{
            //First a tmp file is created
            $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
            
            //TODO: the folder creation should be managed at program creation
            if (!file_exists($filePath)) {
                //echo 'create folder: ' . WWW_ROOT . "files/".$programUrl;
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            if ($programNow) {
                $timestamp = $programNow->format("Y-m-d_H-i-s");
            } else {
                $timestamp = '';
            }
            //$programName  = $this->Session->read($programUrl.'_name');
            $programName  = $this->programDetails['name'];
            
            $programNameUnderscore = inflector::slug($programName, '_');
            
            $fileName     = $programNameUnderscore . "_participants_" . $timestamp . ".csv";            
            $fileFullPath = $filePath . "/" . $fileName;
            $handle       = fopen($fileFullPath, "w");            
            $headers      = $this->Participant->getExportHeaders($conditions);
            
            //Second we write the headers
            fputcsv($handle, $headers,',' , '"' );
            
            //Third we extract the data and copy them in the file            
            $participantCount = $this->Participant->find('count', array('conditions'=> $conditions));
            $pageCount        = intval(ceil($participantCount / $paginate['limit']));
            
            for ($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate   = $paginate;
                $participants     = $this->paginate();
                foreach ($participants as $participant) {
                    $line = array();
                    foreach ($headers as $header) {
                        if (in_array($header, array('phone', 'last-optin-date', 'last-optout-date'))) {
                            $line[] = $participant['Participant'][$header];
                        } else if ($header == 'tags') {
                            $line[] = implode(', ', $participant['Participant'][$header]);         
                        } else {
                            $value  = $this->_searchProfile($participant['Participant']['profile'], $header);
                            $line[] = $value;
                        }
                    }
                    fputcsv($handle, $line,',' , '"');
                }
            }
            $requestSuccess = true;
            $this->set(compact('requestSuccess', 'fileName'));
        } catch (Exception $e) {
            $this->Session->setFlash($e->getMessage());
            $this->set(compact('requestSuccess'));
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
    
    
    protected function _notifyUpdateBackendWorker($workerName, $participantPhone)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'participant', $participantPhone);
    }
    
    
    protected function _notifyBackendMassTag($workerName, $tag, $query)
    {
        $this->VumiRabbitMQ->sendMessageMassTag($workerName, $tag, $query);
    }
    
    
    protected function _notifyBackendMassUntag($workerName, $tag)
    {
        $this->VumiRabbitMQ->sendMessageMassUntag($workerName, $tag);
    }
    

    protected function _notifyBackendRunActions($workerName, $runActions)
    {
        $this->VumiRabbitMQ->sendMessageRunActions($workerName, $runActions);
    }
    
    public function add() 
    {
        $programUrl     = $this->params['program'];
        $requestSuccess = false;
        $data           = $this->_ajaxDataPatch();
        
        if ($this->request->is('post')) {
            if (!$this->ProgramSetting->hasRequired()) {
                $this->Session->setFlash(__('Please set the program settings then try again.'));
                return;
            }
            $savedParticipant = null;
            $this->Participant->create();
            if ($savedParticipant = $this->Participant->save($data)) {
                $this->_notifyUpdateBackendWorker($programUrl, $savedParticipant['Participant']['phone']);                
                $requestSuccess = true;
                $this->Session->setFlash(__('The participant has been saved.'),
                    'default', array('class'=>'message success'));
                if (!$this->_isAjax()) {
                    $this->redirect(array(
                        'program' => $programUrl,  
                        'controller' => 'programParticipants',
                        'action' => 'index'));
                }
            } else {
                $this->Session->setFlash(__('The participant could not be saved.'));
            }
            $this->set(compact('requestSuccess', 'savedParticipant'));
        }    
    }
    
    
    protected function _getSelectOptions()
    {
        $selectOptions = array();
        $dialogues     = $this->Dialogue->getDialoguesInteractionsContent();
        foreach ($dialogues as $key => $value) {
            $selectOptions[$key] = $value['name'];
        }
        return $selectOptions;
    }
    
    
    protected function _loadParticipantId($data)
    {
        if ($this->params['id']) { 
            $id = $this->params['id'];
            $this->Participant->id = $id;
            if (!$this->Participant->exists()) {
                throw new NotFoundException(__('Invalid participant'));
            }
            $participant = $this->Participant->read();
        } else { 
            if ($data['Participant']['phone']) {
                $phone = $data['Participant']['phone'];
            } else {
                throw new NotFoundException(__('Invalid participant'));   
            }
            $participant = $this->Participant->find(
                'first',
                array('conditions' => array('phone' => Participant::cleanPhone($phone))));
            if (!$participant) {
                throw new NotFoundException(__('Invalid participant'));   
            }
            $this->Participant->id = $participant['Participant']['_id'];
        }
        return $participant;
    }
    
    protected function _ajaxDataPatch($modelName='Participant')
    {
        $data = $this->data;
        if (!isset($data[$modelName])) {
            $data = array($modelName => $data);
        }
        return $data;
    }
    
    //we should not be able to edit a phone number
    public function edit()   
    {
        $programUrl     = $this->params['program'];
        $requestSuccess = false;
        $id             = null;
        $data           = $this->_ajaxDataPatch();
        
        //Retrieving the participant to edit
        $participant = $this->_loadParticipantId($data);
        
        if ($this->request->is('post')) {
            if ($savedParticipant = $this->Participant->save($data)) {
                $this->set(compact('savedParticipant'));
                $this->Schedule->deleteAll(
                    array('participant-phone' => $participant['Participant']['phone']),
                    false);
                $this->_notifyUpdateBackendWorker($programUrl, $savedParticipant['Participant']['phone']);
                $participant    = $savedParticipant;
                $requestSuccess = true;               
                $this->Session->setFlash(__('The participant has been saved.'),
                    'default', array('class'=>'message success'));
                if (!$this->_isAjax()) {
                    $this->redirect(array('program' => $programUrl, 'action' => 'index'));
                } 
            } else {
                $this->Session->setFlash(__('The participant could not be saved. Please, try again.'));
            }
            $this->set(compact('requestSuccess'));
        } else {
            $this->request->data = $this->Participant->read(null, $id);
        }
        $selectOptions = $this->_getSelectOptions();
        $oldEnrolls    = array();
        $enrolled      = $participant['Participant']['enrolled'];
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
        $params     = array('fields' => array('phone'));
        
        $conditions = $this->Filter->getConditions($this->Participant);
        if ($conditions) {
            $params += array('conditions' => $conditions);
        } else {
            $conditions = true;
        }
        
        $count        = 0;
        $participants = $this->Participant->find('all', $params);
        foreach ($participants as $participant) {
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
        $programUrl  = $this->params['program'];
        $id          = $this->params['id'];
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
            $this->Session->setFlash(__('Participant and related schedule deleted.'),
                'default', array('class'=>'message success'));
            $this->redirect(array(
                'program' => $programUrl,
                'action' => 'index',
                'page' => $currentPage,
                '?' => $this->params['url']));
        }
        $this->Session->setFlash(__('Participant was not deleted.'));
        $this->redirect(array(
            'program' => $programUrl,
            'action' => 'index',
            'page' => $currentPage));
    }
    
    
    protected function _getAutoEnrollments($programTime)
    {
        $autoEnrollDialogues = $this->Dialogue->getActiveDialogues(array('auto-enrollment'=>'all'));
        if ($autoEnrollDialogues == null) {
            $enrolled = array();
        } else {
            foreach ($autoEnrollDialogues as $autoEnroll) {
                $enrolled[] = array(
                    'dialogue-id' => $autoEnroll['Dialogue']['dialogue-id'],
                    'date-time' => $programTime->format("Y-m-d\TH:i:s")
                    );
            }
        }
        return $enrolled;
    }
    
    
    public function optin()
    {
        $programUrl     = $this->params['program'];
        $id             = $this->params['id'];
        $requestSuccess = false;
        
        $data        = $this->_ajaxDataPatch();
        $participant = $this->_loadParticipantId($data);
        
        if ($this->request->is('post')) {
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            
            $tags    = $participant['Participant']['tags'];
            $profile = $participant['Participant']['profile'];
            
            $this->Participant->reset($participant['Participant']);
            
            $participant['Participant']['session-id']       = $this->Participant->gen_uuid();
            $participant['Participant']['last-optin-date']  = $programNow->format("Y-m-d\TH:i:s");
            $participant['Participant']['last-optout-date'] = null;
            $participant['Participant']['enrolled']         = $this->_getAutoEnrollments($programNow);
            $participant['Participant']['tags']             = $tags;
            $participant['Participant']['profile']          = $profile;
            
            if ($this->Participant->save($participant['Participant'])) {
                $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
                $requestSuccess = true;
                $this->Session->setFlash(__('The participant has been optin.'),
                    'default', array('class'=>'message success'));
                if (!$this->_isAjax()) {
                    $this->redirect(array(
                        'program' => $programUrl,  
                        'controller' => 'programParticipants',
                        'action' => 'index'));
                } 
            } else {
                $this->Session->setFlash(__('The participant could not be optin.'));
            }
            $this->set(compact('requestSuccess','participant'));
        }
    }
    
    
    public function optout()
    {
        $programUrl     = $this->params['program'];
        $id             = $this->params['id'];
        $requestSuccess = false;
        
        $data = $this->_ajaxDataPatch();
        //Retrieving the participant to edit
        $participant = $this->_loadParticipantId($data);
        
        if ($this->request->is('post')) {
            $this->Schedule->deleteAll(
                array('participant-phone' => $participant['Participant']['phone']),
                false);
            
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            
            $participant['Participant']['session-id']       = null;
            $participant['Participant']['last-optout-date'] = $programNow->format("Y-m-d\TH:i:s");
            if ($this->Participant->save($participant['Participant'])) {
                $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
                $requestSuccess = true;
                $this->Session->setFlash(__('The participant has been optout.'),
                    'default', array('class'=>'message success'));
                if (!$this->request->is('ajax')) {
                    $this->redirect(array(
                        'program' => $programUrl,  
                        'controller' => 'programParticipants',
                        'action' => 'index'));
                } 
            } else {
                $this->Session->setFlash(__('The participant could not be optout.'));
            }
            $this->set(compact('participant', 'requestSuccess'));
        }
    }
    
    
    public function reset()
    {
        $programUrl     = $this->params['program'];
        $id             = $this->params['id'];
        $requestSuccess = false;
        
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
            
            $resetParticipant             = $this->Participant->reset($participant['Participant']);            
            $resetParticipant['enrolled'] = $this->_getAutoEnrollments($programNow);
            if ($participant = $this->Participant->save($resetParticipant)) {
                $this->_notifyUpdateBackendWorker($programUrl, $resetParticipant['phone']);
                $requestSuccess = true;
                $this->Session->setFlash(__('The participant has been reset.'),
                    'default', array('class'=>'message success'));
                $this->redirect(array(
                    'program' => $programUrl,  
                    'controller' => 'programParticipants',
                    'action' => 'index'));
            } else {
                $this->Session->setFlash(__('The participant could not be reset.'));
            }
            $this->set(compact('participant', 'requestSuccess'));
        }
    }
    
    
    public function view() 
    {
        $id = $this->params['id'];
        
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant                  = $this->Participant->read(null, $id);
        $dialoguesInteractionsContent = $this->Dialogue->getDialoguesInteractionsContent();
        $histories                    = $this->History->getParticipantHistory(
            $participant['Participant']['phone'],
            $dialoguesInteractionsContent
            );
        
        $schedules = $this->Schedule->getParticipantSchedules(
            $participant['Participant']['phone'],
            $dialoguesInteractionsContent
            );
        
        $this->set(compact('participant','histories', 'schedules'));
    }
    
    
    public function import()
    {
        $programName = $this->Session->read($this->params['program'].'_name');
        $programUrl  = $this->params['program'];
        
        if ($this->request->is('post')) {
            if (!$this->ProgramSetting->hasRequired()) {
                $this->Session->setFlash(
                    __('Please set the program settings then try again.'), 
                    'default', array('class' => "message failure")
                    );
                return;
            }
            
            if ($this->request->data['Import']['file']['error'] != 0) {
                if ($this->request->data['Import']['file']['error'] == 4) { 
                    $message = __("Please select a file.");
                } else { 
                    $message = __('Error while uploading the file: %s.', $this->request->data['Import']['file']['error']);
                }
                $this->Session->setFlash($message, 
                    'default', array('class' => "message failure")
                    );
                return;
            }
            
            $tags = null;
            if (isset($this->request->data['Import']['tags'])) {
                $tags = $this->request->data['Import']['tags'];
            }
            
            $replaceTagsAndLabels = false;
            if (isset($this->request->data['Import']['replace-tags-and-labels'])) {
                $replaceTagsAndLabels = true;
            }
            
            $fileName = $this->request->data['Import']['file']['name'];
            
            $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
            
            if (!file_exists(WWW_ROOT . "files/programs/".$programUrl)) {
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            /** in case the file has already been created, 
            * the chmod function should not be called.
            */
            $wasFileAlreadyThere = false;
            if (file_exists($filePath . DS . $fileName)) {
                $wasFileAlreadyThere = true;
            }
            
            copy($this->request->data['Import']['file']['tmp_name'],
                $filePath . DS . $fileName);
            
            if (!$wasFileAlreadyThere) {
                chmod($filePath . DS . $fileName, 0664);
            }
            
            $report = $this->Participant->import(
                $programUrl, 
                $filePath . DS . $fileName, 
                $tags,
                $replaceTagsAndLabels
                );
            if ($report) {
                foreach ($report as $participantReport) {
                    if ($participantReport['saved']) {
                        $this->_notifyUpdateBackendWorker($programUrl, $participantReport['phone']);
                    }    
                }
                $requestSuccess = true;
            } else {
                $this->Session->setFlash(
                    $this->Participant->importErrors[0], 
                    'default', array('class' => "message failure")
                    );
            }
            
            //Remove file at the end of the import
            unlink($filePath . DS . $fileName);
        }
        $this->set(compact('report', 'requestSuccess'));
    }
    
    
    public function paginationCount()
    {
        $requestSuccess = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        $defaultConditions = array();
        $paginationCount   = $this->Participant->count($this->Filter->getConditions($this->Participant, $defaultConditions), null, -1);
        $this->set(compact('requestSuccess', 'paginationCount'));
    }


    public function runActions()
    {
        $programUrl     = $this->params['program'];
        $requestSuccess = true;
        $data           = $this->data;

        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }

        $valid = $this->Participant->validateRunActions($data);
        if ($valid === true) {
            $this->Session->setFlash(__('The runActions succeed.'));
            $this->_notifyBackendRunActions($programUrl, $data);
        } else {
            $this->Session->setFlash(__('The runActions failed.'));
            $requestSuccess = false;
            $this->set('validationErrors', $valid);
        }
        $this->set(compact('requestSuccess'));
    }
    
    
}

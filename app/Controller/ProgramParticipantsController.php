<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('Participant','Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('Dialogue', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('VusionException', 'Lib');
App::uses('FilterException', 'Lib');
App::uses('Export', 'Model');


class ProgramParticipantsController extends BaseProgramSpecificController
{
    
    var $uses = array(
        'Participant',
        'ParticipantStats',
        'History',
        'Schedule',
        'Dialogue',
        'UnattachedMessage',
        'ProgramSetting',
        'Export');
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
        'ArchivedProgram',
        'Mash',
        'Simulator');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Paginator' => array(
            'className' => 'BigCountPaginator'),
        'Csv');
    
    
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
    
    
    public function index() 
    {      
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $requestSuccess = true;
        $order          = null;
        $conditions     = array();
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('last-optin-date' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $conditions = $this->Filter->getConditions(
            $this->Participant,
            array(),
            array('Schedule' => $this->Schedule));
        
        $paginate = array(
            'allSafeJoin',
            'conditions' => $conditions,
            'order' => $order);
        $this->paginate = $paginate;
        $participants   = $this->paginate('Participant');
        $this->set(compact('participants', 'requestSuccess', 'order'));
    }
    
    
    public function listParticipants()
    {
        $requestSuccess = true;
        $conditions     = array();
        $explodeProfile = array();
        
        if (!$this->_isCsv() && !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        $conditions = $this->Filter->getConditions(
            $this->Participant,
            array(),
            array('Schedule' => $this->Schedule));
        
        $participants = $this->Participant->find(
            'allSafeJoin',
            array(
                'conditions' => $conditions,
                'limit'=> 10000));
        
        if (isset($this->params['url']['explode_profile'])) {
            $explodeProfile = explode(',', $this->params['url']['explode_profile']);
        }
        $this->set(compact('participants', 'requestSuccess', 'explodeProfile'));
        $this->render('index');
    }
    
    
    public function listSurveyParticipants()
    {
        $requestSuccess = true;
        $conditions     = array();
        
        if (!$this->_isCsv() && !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        $conditions = $this->Filter->getConditions(
            $this->Participant,
            array(),
            array('Schedule' => $this->Schedule));
        
        $participants = $this->Participant->find(
            'allSafeJoin',
            array(
                'conditions' => $conditions,
                'limit'=> 10000));
        
        $participantSurveyProfile = array();
        $index = 0;
        foreach($participants as $participant) {
            $indexT = 0;
            $participantTags[$indexT] = null;
            //$participantTags =array_splice($participant['Participant']['tags'],  1);
            $participantTags = $participant['Participant']['tags'];
                        
            $participantProfiles = $participant['Participant']['profile'];                        
            $reportId = $this->_searchProfileId($participantProfiles, 'reporterid');
            foreach($participantProfiles as $participantProfile) {
                $participantSurveyProfile[$index]['answer_text'] = null;
                $participantSurveyProfileList[$index] = null;                
                
                if (isset($participantTags[$indexT])) {
                    $participantSurveyProfile[$index]['answer_id'] = $participantTags[$indexT];
                }
                if (substr($participantProfile['label'], 0, 6) == 'Answer') {
                    $participantSurveyProfile[$index]['answer_text'] = $participantProfile['value'];
                } else {
                    $participantSurveyProfile[$index] = array();
                }                
                if (isset($reportId) && $participantProfile['label'] != 'reporterid') {
                    $participantSurveyProfileList[$index]  = array_merge_recursive($participantSurveyProfile[$index], $reportId); 
                }
                $index++;
                $indexT++;
            }
            
        }
        $this->set(compact('participantSurveyProfileList', 'requestSuccess', 'explodeProfile'));
        $this->render('index');
    }    
        
    
    protected function _searchProfileId($array, $labelKey)
    {
        $results = array();        
        foreach($array as $label) {
            if ($label['label'] == $labelKey) {
                $results['report_id'] = $label['value']; 
                if (isset($results)) {
                    return $results;
                }
            }
        } 
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
    
    
    public function exported()
    {
        $programUrl  = $this->programDetails['url'];
        $paginate = array(
            'all',
            'limit' => 100,
            'conditions' => array(
                'database' => $this->programDetails['database'],
                'collection' => 'participants'),
            'order' => array('timestamp' => '-1'));
        $this->paginate = $paginate;
        $files = $this->paginate('Export');
        $this->set(compact('files'));
    }
    
    
    public function export() 
    {
        $order         = null;
        $programUrl    = $this->params['program'];
        $requestSuccess = false;
        
        $this->set('filterFieldOptions', $this->Participant->fieldFilters);
        $dialoguesContent = $this->Dialogue->getDialoguesInteractionsContent();
        $this->set('filterDialogueConditionsOptions', $dialoguesContent);
        
        $conditions = $this->Filter->getConditions(
            $this->Participant,
            array('$or' => array(
                array('simulate' => false),
                array('simulate' => array('$exists' => false)))),
            array('Schedule' => $this->Schedule),
            false);
        
        if (isset($this->params['named']['sort']) &&  isset($this->params['named']['direction'])) {
            //Sorting on the fields which are not index can create buffer error when there are result of more than 33554432 bytes
            //as the ui by default sort on last-optin-date which has no index, the error is triggered 
            //quick solution is to not sort on export participant until we find a more long term solution
            //$order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        } 
        
        $filePath = Program::ensureProgramDir($programUrl);

        $programNow = $this->ProgramSetting->getProgramTimeNow();
        if ($programNow) {
            $timestamp = $programNow->format("Y-m-d_H-i-s");
        } else {
            $timestamp = '';
        }
        $programName  = $this->programDetails['name'];
        $programNameUnderscore = inflector::slug($programName, '_');
        
        $fileName     = $programNameUnderscore . "_participants_" . $timestamp . ".csv";
        $fileFullName = $filePath . DS . $fileName;
        
        $export = array(
            'database' => $this->programDetails['database'],
            'collection' => $this->Participant->table,
            'conditions' => $conditions,
            'filters' => $this->Filter->getFilters(),
            'order' => $order,
            'file-full-name' => $fileFullName);
        if (!$saved_export = $this->Export->save($export)) {
            $this->Session->setFlash(__("Vusion failed to start the export process."));
        } else {
            $this->_notifyBackendExport($saved_export['Export']['_id']);
            $this->Session->setFlash(
                __("Vusion is backing the export file. Your file should appear shortly on this page."),
                'default', array('class'=>'message success'));
            $requestSuccess = True;
        }
        $this->set(compact('requestSuccess'));
        
        $this->redirect(array(
            'program' => $programUrl,
            'action' => 'exported'));
    }
    
    
    public function deleteExport() 
    {
        $id = $this->params['id'];
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Export->id = $id;
        if (!$this->Export->exists()) {
            throw new NotFoundException(__('Invalid Export: %s', $id));
        }
        
        if ($this->Export->delete()) {
            $this->Session->setFlash(__('Export deleted.'),
                'default', array('class'=>'message success'));
        } else {
            $this->Session->setFlash(__('Export cannot be deleted.'));
        }
        
        $this->redirect(array(
            'program' => $this->programDetails['url'],
            'action' => 'exported'));
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
    
    
    protected function _notifyBackendExport($exportId)
    {
        $this->VumiRabbitMQ->sendMessageToExport($exportId);
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
                $this->_notifyUpdateBackendWorker(
                    $programUrl,
                    $savedParticipant['Participant']['phone']);
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
    
    
    public function addSimulated() 
    {
        $programUrl     = $this->params['program'];
        $requestSuccess = false;
        $data           = $this->_ajaxDataPatch();
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the program settings then try again.'));
            return;
        }
        
        if ($this->request->is('post')) {
            $savedParticipant                = null;
            $data['Participant']['simulate'] = true;
            $this->Participant->create();
            
            if ($data['Participant']['join-type'] == 'optin-keyword') {
                $optinMessage = $data['message'];
                $form         = $this->Participant->generateSimulatedPhone();
                $this->_sendSimulateMo($programUrl, $form,  $optinMessage);
                return;
            }
            $data['Participant']['tags'] = Participant::getDefaultImportedTag();
            if ($savedParticipant = $this->Participant->save($data['Participant'])) {
                $this->_notifyUpdateBackendWorker(
                    $programUrl,
                    $savedParticipant['Participant']['phone']);
                $requestSuccess = true;
                $this->Session->setFlash(__('The participant has been saved.'),
                    'default', array('class'=>'message success'));
                if (!$this->_isAjax()) {
                    $this->redirect(array(
                        'program' => $programUrl,  
                        'controller' => 'programParticipants',
                        'action' => 'simulateMo',
                        $savedParticipant['Participant']['_id']));
                }
            } else {
                $this->Session->setFlash(__('The simulate participant could not be saved.'));
            }
            $this->set(compact('requestSuccess', 'savedParticipant'));
        } 
    }
    
    
    protected function _sendSimulateMo($programUrl, $form,  $optinMessage) 
    {
        if ($optinMessage) {
            $this->VumiRabbitMQ->sendMessageToSimulateMO($programUrl, $form,  $optinMessage);
            $this->Session->setFlash(__('Optin message has been sent, this will create a participant if you used the right Optin keyword'),
                'default', array('class'=>'message success'));
            $this->redirect(array(
                'program' => $programUrl,  
                'controller' => 'programParticipants',
                'action' => 'index'));
        } else {
            $this->Session->setFlash(__('The simulate participant could not be saved. Enter Optin message'));
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
        $this->set(compact('oldEnrolls', 'selectOptions', 'participant'));
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
            if (isset($participant['Participant']['simulate']) || (isset($include) && $include=="history")) {
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
        if ($this->request->is('post')) {
            $participant = $this->Participant->read(null, $id);
            $this->Schedule->deleteAll(
                array('participant-phone' => $participant['Participant']['phone']),
                false);
            if ($participant = $this->Participant->reset()) {
                $this->_notifyUpdateBackendWorker($programUrl, $participant['Participant']['phone']);
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
        $requestSuccess = false;
        $id = $this->params['id'];
        
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            $this->set(compact('requestSuccess'));
            throw new NotFoundException(__('Invalid participant'));
        }
        $requestSuccess = true;
        $participant = $this->Participant->read(null, $id);
        $participant = $this->Dialogue->fromDialogueIdToName($participant);
        $historyFrom = (isset($this->request->query['history_from'])? $this->request->query['history_from'] : null);
        $dialoguesInteractionsContent = $this->Dialogue->getDialoguesInteractionsContent();
        $histories                    = $this->History->getParticipantHistory(
            $participant['Participant']['phone'],
            $dialoguesInteractionsContent,
            $historyFrom);

        $schedules = $this->Schedule->getParticipantSchedules(
            $participant['Participant']['phone'],
            $dialoguesInteractionsContent);

        $this->set(compact('participant','histories', 'schedules','requestSuccess'));
    }


    public function importMash()
    {
        $requestSuccess = false;

        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(__('Please set the shortcode to import from Mash.'));
        }
        $importCountries = $this->Country->getNamesByIso($this->programDetails['settings']['international-prefix']);
        $this->set(compact('importCountries'));

        if ($this->request->is('post')) {
            $tags = (isset($this->request->data['Import']['tags']) ? $this->request->data['Import']['tags'] : null);
            $tags = 'mash,' . $tags;
            $importTagsAndLabels = (isset($this->request->data['Import']['replace-tags-and-labels']) ? $this->request->data['Import']['import-tags-and-labels'] : 'keep');
            $countryIso = $this->request->data['Import']['country'];
            if (!isset($importCountries[$countryIso])) {
                $this->Session->setFlash(__("Import not allowed of participant from %s.", $this->Country->fromIsoToName($countryIso)));
                $this->set(compact('requestSuccess'));
                return;
            }

            $participantJsonDecoded = $this->Mash->importParticipants($countryIso);
            if ($participantJsonDecoded == null) {
                if ($participantJsonDecoded === array()) {
                    $this->Session->setFlash(__('The import failed because no participant is available in this country for this program.'));                    
                } else {
                    $this->Session->setFlash(__('The import failed because the Mash server is not responding, please report the issue.'));
                }
            } else {

                $enrolled = null;
                if (isset($this->request->data['Import']['enrolled'])) {
                    $enrolled = $this->request->data['Import']['enrolled'];
                }

                $report = $this->Participant->importJsonDecoded(
                    $this->programDetails['url'],
                    $participantJsonDecoded,
                    $tags,
                    $enrolled,
                    $importTagsAndLabels);
                if ($report) {
                    foreach ($report as $participantReport) {
                        if ($participantReport['saved']) {
                            $this->_notifyUpdateBackendWorker(
                                $this->programDetails['url'],
                                $participantReport['phone']);
                        } else {
                            $this->Session->setFlash(__("Import of some participants failed, see details below."));
                        }
                    }
                    $requestSuccess = true;
                } else{
                    $this->Session->setFlash($this->Participant->importErrors[0]);
                }
            }
        }
        $selectOptions = $this->_getSelectOptions();
        $this->set(compact('report', 'requestSuccess', 'selectOptions'));
    }


    public function importFile()
    {
        $programName           = $this->Session->read($this->params['program'].'_name');
        $programUrl            = $this->params['program'];
        $importMaxParticipants = Configure::read('vusion.importMaxParticipants');
        
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
                } else if ($this->request->data['Import']['file']['error'] == 1) {
                    $message = __("Selected file is too large to import please divide it into 10,000 participants.");
                } else { 
                    $message = __('Error while uploading the file: %s.', $this->request->data['Import']['file']['error']);
                }
                $this->Session->setFlash($message);
                return;
            }
            
            $file      = $this->request->data['Import']['file']['tmp_name'];
            $linecount = 0;
            $handle    = fopen($file, "r");
            while(!feof($handle)){
                $line = fgets($handle);
                $linecount++;
                // Stop iterating when limit is reached
                if ($linecount >= $importMaxParticipants) {
                    $this->Session->setFlash(__('Max limit of 10,000 participants exceeded, please break file into smaller parts'));
                    fclose($handle);
                    return;
                }
            }
            fclose($handle);

            $tags = null;
            if (isset($this->request->data['Import']['tags'])) {
                $tags = $this->request->data['Import']['tags'];
            }
            
            $enrolled = null;
            if (isset($this->request->data['Import']['enrolled'])) {
                $enrolled = $this->request->data['Import']['enrolled'];
            }
            
            $importTagsAndLabels = 'keep';
            if (isset($this->request->data['Import']['import-type'])) {
                $importTagsAndLabels = $this->request->data['Import']['import-type'];
            }
            
            $fileName = $this->request->data['Import']['file']['name'];
            
            $filePath = Program::ensureProgramDirImported($programUrl);
            
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
                $enrolled,
                $importTagsAndLabels
                );

            if ($report) {
                foreach ($report as $participantReport) {
                    if ($participantReport['saved']) {
                        $this->_notifyUpdateBackendWorker($programUrl, $participantReport['phone']);
                    }    
                }
                $requestSuccess = true;
            } else {
                $this->Session->setFlash($this->Participant->importErrors[0]);
            }
            // throw new Exception();
            //Remove file at the end of the import
            unlink($filePath . DS . $fileName);
        }
        $selectOptions = $this->_getSelectOptions();
        $this->set(compact('report', 'requestSuccess', 'selectOptions'));
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
            $this->UserLogMonitor->setEventData($data['phone']);
            $this->_notifyBackendRunActions($programUrl, $data);
        } else {
            $this->Session->setFlash(__('The runActions failed.'));
            $requestSuccess = false;
            $this->set('validationErrors', $valid);
        }
        $this->set(compact('requestSuccess'));
    }
    
    
    public function simulateMo()
    {
        $requestSuccess = true;
        $id                    = $this->params['id'];
        $program               = $this->params['program'];
        $this->Participant->id = $id;
        $data           = $this->_ajaxDataPatch();
        $participant    = $this->_loadParticipantId($data);
       
        if ($this->request->is('post')) {
            $message = trim($this->request->data['message']);
            $from    = $this->request->data['phone'];
            $this->_sendSimulateMoVumiRabbitMQ($program, $from, $message);
        }
        $this->set(compact('requestSuccess', 'participant'));
    }
    
    
    protected function _sendSimulateMoVumiRabbitMQ($program, $from, $message)
    {
        $this->VumiRabbitMQ->sendMessageToSimulateMO($program, $from, $message);
    }


}

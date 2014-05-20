<?php
App::uses('AppController','Controller');
App::uses('Participant','Model');
App::uses('History', 'Model');
App::uses('Schedule', 'Model');
App::uses('Dialogue', 'Model');

App::uses('DialogueHelper', 'Lib');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('VusionException', 'Lib');
App::uses('FilterException', 'Lib');


class ProgramParticipantsController extends AppController
{
    
    var $uses       = array('Participant', 'History');
    var $components = array('RequestHandler', 'LocalizeUtils');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Paginator' => array('className' => 'BigCountPaginator'));
    
    
    function constructClasses() 
    {
        parent::constructClasses();
    }
    
    
    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    function beforeFilter() 
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
        $options = array('database' => ($this->Session->read($this->params['program']."_db")));
        
        $this->Participant       = new Participant($options);
        $this->History           = new History($options);
        $this->Schedule          = new Schedule($options);
        $this->Dialogue          = new Dialogue($options);
        $this->DialogueHelper    = new DialogueHelper();
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->ProgramSetting    = new ProgramSetting($options);
        
        $this->_instanciateVumiRabbitMQ();
        
        $this->DialogueHelper = new DialogueHelper();
    }
    
    
    public function index() 
    {      
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $paginate = array('all');
        
        if (isset($this->params['named']['sort']) &&  isset($this->params['named']['direction'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $conditions = $this->_getConditions();
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        $this->paginate = $paginate;
        $participants   = $this->paginate();
        $this->set(compact('participants'));
    }
    
    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->Participant->filterFields);
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
        $this->set(compact('results'));
        $this->render('ajaxResults');
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
        $conditions = $this->_getConditions();
        
        if ($this->request->is('get')) {
            if (!$conditions) {
                $conditions = array();
            }
            if ($this->Participant->addMassTags($this->params['url']['tag'], $conditions)) {                
                $this->Session->setFlash(__('The MassTag has been added successfully.'),
                    'default',
                    array('class'=>'message success')
                    );
            } else {                
                $this->Session->setFlash(__('The MassTag'.$tag.' could not be added successfully.'), 
                    'default',
                    array('class' => 'message failure'));                            
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
        $conditions = $this->_getConditions();
        
        if ($this->request->is('get')) {
            if (!$conditions) {
                $conditions = array();
            } 
            if ($this->Participant->deleteMassTags($this->params['url']['tag'], $conditions)) {
                $this->Session->setFlash(__('The Tag '.$this->params['url']['tag'].' has been removed successfully.'),
                    'default',
                    array('class'=>'message success')
                    );
            } else {                
                $this->Session->setFlash(__('The Tag'.$this->params['url']['tag'].' could not be removed successfully.'), 
                    'default',
                    array('class' => 'message failure')
                    );                                
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
        $programUrl = $this->params['program'];
        
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
        
        $conditions = $this->_getConditions();
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
            
            $programNow   = $this->ProgramSetting->getProgramTimeNow();
            $programName  = $this->Session->read($programUrl.'_name');
            $fileName     = $programName . "_participants_" . $programNow->format("Y-m-d_H-i-s") . ".csv";            
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
        $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));
        
        if (!isset($filter['filter_param'])) 
            return null;
        
        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->Participant->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }
        
        foreach ($filter['filter_param'] as $key => $filterParam) {
            if (isset($filterParam[3])) {
                if (!$filterParam[3]) {
                    $this->Session->setFlash(__('"%s" Filter ignored due to missing information', $filterParam[1]), 
                        'default',
                        array('class' => "message failure")
                        );
                }
            }
        }
        
        $this->set('urlParams', http_build_query($filter));
        
        return $this->Participant->fromFilterToQueryConditions($filter);
    }
    
    
    protected function _notifyUpdateBackendWorker($workerName, $participantPhone)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'participant', $participantPhone);
    }
    
    
    public function add() 
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            if (!$this->ProgramSetting->hasRequired()) {
                $this->Session->setFlash(
                    __('Please set the program settings then try again.'), 
                    'default', array('class' => "message failure")
                    );
                return;
            }
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
    
    
    //we should not be able to edit a phone number
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
        
        $conditions = $this->_getConditions();
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
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        
        $this->Participant->id = $id;
        if (!$this->Participant->exists()) {
            throw new NotFoundException(__('Invalid participant'));
        }
        $participant = $this->Participant->read(null, $id);
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
        $id         = $this->params['id'];
        
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
            
            $participant['Participant']['session-id']       = null;
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
        $id         = $this->params['id'];
        
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
            } else {
                $this->Session->setFlash(
                    $this->Participant->importErrors[0], 
                    'default', array('class' => "message failure")
                    );
            }
            
            //Remove file at the end of the import
            unlink($filePath . DS . $fileName);
        }
        $this->set(compact('report'));
    }
    
    
    public function paginationCount()
    {
        if ($this->params['ext'] !== 'json') {
            return; 
        }
        $defaultConditions = array();
        $paginationCount   = $this->Participant->count($this->_getConditions($defaultConditions), null, -1);
        $this->set('paginationCount', $paginationCount);
    }
    
    
}

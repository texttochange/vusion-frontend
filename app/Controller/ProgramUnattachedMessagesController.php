<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('Schedule', 'Model');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('ProgramSetting', 'Model');
App::uses('History', 'Model');
App::uses('User', 'Model');
App::uses('PredefinedMessage', 'Model');


class ProgramUnattachedMessagesController extends BaseProgramSpecificController
{
    
    var $uses = array(
        'UnattachedMessage',
        'User',
        'Schedule',
        'Participant',
        'ProgramSetting',
        'History',
        'PredefinedMessage');
    var $components = array(
        'Message',
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'ArchivedProgram');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time',
        'Session');
    
    
    public function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_instanciateVumiRabbitMQ();
    }
    
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    protected function _notifyUpdateBackendWorkerUnattachedMessage($workerName, $unattach_id)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'unattach', $unattach_id);
    }
    
    
    protected function _notifyUpdateBackendWorkerParticipant($workerName, $participantPhone)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'participant', $participantPhone);
    }
    
    
    public function index()
    {
        $paginate = array();
        if (!isset($this->params['named']['sort'])) {
            $order = array('created' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        $paginate = array('order' => $order);
        $findType = 'all';
        if (isset($this->params['named']['type'])) {
            if ($this->params['named']['type'] == 'sent') {
                $findType = 'sent';
            } else if ($this->params['named']['type'] == 'drafted') {
                $findType = 'drafted';
            } else if ($this->params['named']['type'] == 'scheduled') {
                $findType = 'scheduled';
            }
        }
        $paginate['conditions'] = $this->UnattachedMessage->addFindTypeCondition($findType);
        $this->set(compact('findType'));
        $paginate = $paginate;
        
        $this->paginate = $paginate;
        $unattachedMessages = $this->paginate('UnattachedMessage');
        
        foreach($unattachedMessages as &$unattachedMessage)
        {  
            $unattachId = $unattachedMessage['UnattachedMessage']['_id'];
            $status = array();          
            if ($unattachedMessage['UnattachedMessage']['type-schedule'] != 'none') {
                if ($this->UnattachedMessage->isNotPast($unattachedMessage['UnattachedMessage'])) {                 
                    $countSchedule = $this->Schedule->countScheduleFromUnattachedMessage($unattachId);
                    $status['count-schedule'] = $countSchedule;                
                } else if (0 < ($countNoCredit = $this->History->countUnattachedMessages($unattachId, array('no-credit', 'no-credit-timeframe')))){
                    $status['count-no-credit'] = $countNoCredit;   
                } else {               
                    $countSent                 = $this->History->countUnattachedMessages($unattachId);
                    $status['count-sent']      = $countSent;            
                    $countDelivered            = $this->History->countUnattachedMessages($unattachId, 'delivered');
                    $status['count-delivered'] = $countDelivered;
                    $countPending              = $this->History->countUnattachedMessages($unattachId, 'pending');
                    $status['count-pending']   = $countPending;
                    $countFailed               = $this->History->countUnattachedMessages($unattachId, array('failed', 'no-credit', 'no-credit-timeframe', 'missing-data', 'datepassed-marker'));
                    $status['count-failed']    = $countFailed;
                    $countAck                  = $this->History->countUnattachedMessages($unattachId, 'ack');
                    $status['count-ack']       = $countAck; 
                    $countNack                 = $this->History->countUnattachedMessages($unattachId, 'nack');
                    $status['count-nack']      = $countNack; 
                }
                $unattachedMessage['UnattachedMessage'] = array_merge(
                    $status, $unattachedMessage['UnattachedMessage']);
            }
            if (in_array($unattachedMessage['UnattachedMessage']['model-version'], array('1','2','3'))) {
                $unattachedMessage['UnattachedMessage']['created-by'] = __("unknown");
            } else {
                $conditions = array('conditions' => array( 'User.id' => $unattachedMessage['UnattachedMessage']['created-by']));
                
                $user = $this->User->find('first', $conditions);
                $unattachedMessage['UnattachedMessage']['created-by'] = ($user ? $user['User']['username']: __("unknown"));
            }
        }
        $this->set(compact('unattachedMessages'));
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        $requestSuccess = false;
        
        if ($this->request->is('post')) {
            if ($savedUnattachedMessage = $this->saveUnattachedMessage()) {
                $requestSuccess = true;
                $this->set(compact('savedUnattachedMessage'));
                if (!$this->_isAjax()) {
                    $this->redirect(array(
                        'program' => $programUrl,
                        'controller' => 'programUnattachedMessages',
                        'action' => 'index'));
                }
            }
            $this->set(compact('requestSuccess'));
        }
        
        $selectorValues = $this->Participant->getDistinctTagsAndLabels();
        if (count($selectorValues) > 0) {
            $selectors = array_combine($selectorValues, $selectorValues);
        }
        
        $predefinedMessageOptions = $this->_getPredefinedMessageOptions();
        $this->set(compact('selectors', 'predefinedMessageOptions'));
        
    }
    
    
    protected function saveUnattachedMessage()
    {
        $programUrl = $this->params['program'];
        $data       = $this->data;
        if (!isset($data['UnattachedMessage'])) {
            $data = array('UnattachedMessage' => $data);
        }
        
        if (!$this->ProgramSetting->hasRequired()) {
            $this->Session->setFlash(
                __('Please set the program settings then try again.'), 
                'default', array('class' => "message failure"));
            return false;
        }
        $importMessage = '';
        $importReport = null;
        if (isset($data['UnattachedMessage']['send-to-type'])
            &&  $data['UnattachedMessage']['send-to-type'] == 'phone') {
        $importReport = null;
        if (isset($data['UnattachedMessage']['file'])) {
            $importReport = $this->importParticipants();    
        } else if (isset($data['UnattachedMessage']['send-to-phone'])) {
            if (!is_array($data['UnattachedMessage']['send-to-phone'])) {
                $data['UnattachedMessage']['send-to-phone'] = array($data['UnattachedMessage']['send-to-phone']);
            }
            $importReport = $this->importParticipantsFromList($data);
            $data['UnattachedMessage']['send-to-phone'] = array();
        }
        if ($importReport) {
            $importFailed = array_filter($importReport, function($participantReport) { 
                    return (!$participantReport['saved'] && !$participantReport['exist-before']);
            });
            $imported = array_filter($importReport, function($participantReport) { 
                    return ($participantReport['saved']);
            });
            $participants = array_filter($importReport, function($participantReport) { 
                    return ($participantReport['saved'] || $participantReport['exist-before']);
            });
            foreach($participants as $participantReport) {
                $data['UnattachedMessage']['send-to-phone'][] = $participantReport['phone'];
            }
        }
            } 
            if ($this->UnattachedMessage->id == null) {
                $this->UnattachedMessage->create();
                $user = $this->Auth->user();
                $data['UnattachedMessage']['created-by'] = $user['id'];
            }
            if ($savedUnattached = $this->UnattachedMessage->save($data)) {
                if ($savedUnattached['UnattachedMessage']['type-schedule'] != 'none') {
                    $this->_notifyUpdateBackendWorkerUnattachedMessage($programUrl, $this->UnattachedMessage->id);
                } else if (isset($this->UnattachedMessage->id)) {
                    $this->Schedule->deleteAll(array('unattach-id'=> $this->UnattachedMessage->id), false);
                }
                if (isset($importReport)) {
                    if ($importReport) {
                        $importMessage = __(' To be send to %s participants, %s have been imported and %s failed to be imported.',
                            count($participants),
                            count($imported),
                            count($importFailed));
                    } else {
                        $importMessage = $this->Participant->importErrors[0];
                    }
                }
                
                $this->Session->setFlash(__('The message has been saved.' . $importMessage),
                    'default', array('class'=>'message success'));
            } else {
                if (isset($importReport)) {
                    if ($importReport) {
                        $importMessage = __(' %s participant(s) have been imported and %s failed to be imported.',
                            count($imported),
                            count($importFailed));
                    } else {
                        if (isset($this->Participant->importError[0])) {
                            $importMessage = $this->Participant->importErrors;
                        }
                    }
                }
                $this->Session->setFlash(__('The Message could not be saved.' . $importMessage));
            }
            return $savedUnattached;
    }
    
    
    protected function importParticipants()
    {    
        $programUrl = $this->params['program'];
        
        if ($this->data['UnattachedMessage']['file']['error'] != 0) {
            if ($this->data['UnattachedMessage']['file']['error'] == 4) {
                $this->importErrors = __('Please select a file.');
            } else { 
                $this->importErrors = __('Error while uploading the file: %s.', $this->data['Import']['file']['error']);
            }
            return false;
        }
        
        $fileName = $this->data['UnattachedMessage']['file']['name'];
        $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
        
        copy($this->data['UnattachedMessage']['file']['tmp_name'], $filePath . DS . $fileName);
        chmod($filePath . DS . $fileName, 0664);
        
        $report = $this->Participant->import($programUrl, $filePath . DS . $fileName);
        if ($report) {
            foreach($report as $participantReport) {
                if ($participantReport['saved']) {
                    $this->_notifyUpdateBackendWorkerParticipant($programUrl, $participantReport['phone']);
                }    
            }
        } else {
            $this->importErrors = $this->Participant->importErrors[0];
        }
        ##Remove file at the end of the import
        unlink($filePath . DS . $fileName);
        return $report;
    }
    
    protected function importParticipantsFromList($data) {
        $programUrl = $this->params['program'];
        $report = array();
        foreach ($data['UnattachedMessage']['send-to-phone'] as $participantPhone) {
            $phone = $this->Participant->cleanPhone($participantPhone);
            $report[] = $this->Participant->saveParticipantWithReport(
                array('phone' => $phone), false);
        }
        if ($report) {
            foreach($report as $participantReport) {
                if ($participantReport['saved']) {
                    $this->_notifyUpdateBackendWorkerParticipant($programUrl, $participantReport['phone']);
                }    
            }
        } else {
            $this->importErrors = $this->Participant->importErrors[0];
        }
        return $report;
    }
    
    
    public function edit()
    {
        $programUrl     = $this->params['program'];
        $id             = $this->params['id'];
        $requestSuccess = false;
        
        $this->UnattachedMessage->id = $id;
        
        if (!$this->UnattachedMessage->exists()) {
            throw new NotFoundException(__('Invalid Message.'));
        }
        
        $this->UnattachedMessage->read();
        if ($this->request->is('post')) {
            if ($savedUnattachedMessage = $this->saveUnattachedMessage()) {
                $requestSuccess = true;
                $this->set(compact('savedUnattachedMessage'));
                if (!$this->_isAjax()) {
                    $this->redirect(array(
                        'program' => $programUrl,
                        'controller' => 'programUnattachedMessages',
                        'action' => 'index'));
                }
            }
            $this->set(compact('requestSuccess'));
        } else {
            $this->data = $this->UnattachedMessage->read(null, $id);
            if ($this->data['UnattachedMessage']['type-schedule'] != 'none') {
                $now = new DateTime('now');    
                $programTimezone = $this->ProgramSetting->find('getProgramSetting', array('key' => 'timezone'));
                date_timezone_set($now,timezone_open($programTimezone));      
                $messageDate = new DateTime($this->data['UnattachedMessage']['fixed-time'], new DateTimeZone($programTimezone));
                if ($now > $messageDate){   
                    throw new MethodNotAllowedException(__('Cannot edit a passed Separate Message.'));
                }
                $this->request->data['UnattachedMessage']['fixed-time'] = $messageDate->format('d/m/Y H:i');
            }
            if ($this->data['UnattachedMessage']['model-version'] != $this->UnattachedMessage->getModelVersion()) {
                $this->Session->setFlash(__('Due to internal Vusion update, please save this Separate Message again.'), 
                    'default', array('class' => "message warning"));
            }
        }
        
        $selectorValues = $this->Participant->getDistinctTagsAndLabels();
        if (count($selectorValues) > 0) {
            $selectors = array_combine($selectorValues, $selectorValues);
        }
        
        $predefinedMessageOptions = $this->_getPredefinedMessageOptions();
        $this->set(compact('selectors', 'predefinedMessageOptions'));        
    }
    
    
    protected function _getPredefinedMessageOptions()
    {
        $predefinedMessages = $this->PredefinedMessage->find('all');
        $predefinedMessageOptions = array();
        foreach ($predefinedMessages as $predefinedMessage) {
            $predefinedMessageOptions[] = array(
                'id' => $predefinedMessage['PredefinedMessage']['_id'],
                'name' => $predefinedMessage['PredefinedMessage']['name'],
                'content' => $predefinedMessage['PredefinedMessage']['content']
                );
        }
        return $predefinedMessageOptions;
    }
    
    
    public function delete($id = null)
    {
        $id         = $this->params['id'];
        $programUrl = $this->params['program'];
        
        $this->UnattachedMessage->id = $id;
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->UnattachedMessage->exists()) {
            throw new NotFoundException(__('Invalid Message.'));
        }
        
        if ($this->UnattachedMessage->delete()) {
            $this->Schedule->deleteAll(array('unattach-id'=> $id), false);
            $this->Session->setFlash(__('Message deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(array(
                'program' => $programUrl,
                'controller' => 'programUnattachedMessages',
                'action' => 'index'));
        }
        $this->Session->setFlash(__('Message was not deleted.'));
    }
    
    
}

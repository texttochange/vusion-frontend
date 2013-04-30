<?php
App::uses('AppController', 'Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('Schedule', 'Model');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('DialogueHelper', 'Lib');
App::uses('ProgramSetting', 'Model');
App::uses('History', 'Model');
App::uses('User', 'Model');

class ProgramUnattachedMessagesController extends AppController
{

    var $helpers = array('Js' => array('Jquery'), 'Time');
    
    public $uses = array('UnattachedMessage', 'User');


    public function beforeFilter()
    {
        parent::beforeFilter();
    }


    public function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => ($this->Session->read($this->params['program'].'_db'))
            );
        
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->Schedule          = new Schedule($options);
        $this->Participant       = new Participant($options);
        $this->ProgramSetting    = new ProgramSetting($options);
        $this->DialogueHelper    = new DialogueHelper();
        $this->History           = new History($options);
        $this->_instanciateVumiRabbitMQ();
    }

    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }

    protected function _notifyUpdateBackendWorker($workerName, $unattach_id)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'unattach', $unattach_id);
    }

    public function index()
    {
        $unattachedMessages = $this->paginate();
        $user = $this->User->find('all', array('conditions' => array('id'=>$unattachedMessages[0]['UnattachedMessage']['created-by'])));
        print_r($user);
        foreach($unattachedMessages as &$unattachedMessage)
        {  
            //$user = $this->User->find('all', array('conditions' => array('id'=>$unattachedMessage['UnattachedMessage']['created-by'])));
            $unattachId = $unattachedMessage['UnattachedMessage']['_id'];
            $status = array();
            if ($this->UnattachedMessage->isNotPast($unattachedMessage['UnattachedMessage'])) {                 
                $countSchedule = $this->Schedule->countScheduleFromUnattachedMessage($unattachId);
                $status['count-schedule'] = $countSchedule;                
            } else {               
                $countSent = $this->History->countUnattachedMessages($unattachId);
                $status['count-sent'] = $countSent;            
                $countDelivered = $this->History->countUnattachedMessages($unattachId, 'delivered');
                $status['count-delivered'] = $countDelivered;
                $countPending = $this->History->countUnattachedMessages($unattachId, 'pending');
                $status['count-pending'] = $countPending;
                $countFailed = $this->History->countUnattachedMessages($unattachId, 'failed');
                $status['count-failed'] = $countFailed;
                $countAck = $this->History->countUnattachedMessages($unattachId, 'ack');
                $status['count-ack'] = $countAck; 
                $countNack = $this->History->countUnattachedMessages($unattachId, 'nack');
                $status['count-nack'] = $countNack; 
            }
            $unattachedMessage['UnattachedMessage'] = array_merge(
                $status, $unattachedMessage['UnattachedMessage']);
            $unattachedMessage['UnattachedMessage']['created-by'] = $user['User']['username'];
        }
        $this->set('unattachedMessages', $unattachedMessages);
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->UnattachedMessage->create('unattached-message');
            $user = $this->Auth->user();
            $this->request->data['UnattachedMessage']['created-by'] = $user['id'];
            if ($this->UnattachedMessage->save($this->request->data)) {
                $this->_notifyUpdateBackendWorker($programUrl, $this->UnattachedMessage->id);
                $this->Session->setFlash(__('The Message has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(
                    array(
                        'program' => $programUrl,
                        'controller' => 'programUnattachedMessages',
                        'action' => 'index'
                        )
                    );
            } else {
                $this->Session->setFlash(__('The Message could not be saved.'));
            }
        }
        
        $selectorValues = $this->Participant->getDistinctTagsAndLabels();
        if (count($selectorValues) > 0) {
            $selectors = array_combine($selectorValues, $selectorValues);
        }
        $this->set(compact('selectors'));        
    }

    
    public function edit()
    {
        $unattachedMessage = $this->params['unattchedMessage'];
        $id                = $this->params['id'];
        $programUrl        = $this->params['program'];
        
        $this->UnattachedMessage->id = $id;

        if (!$this->UnattachedMessage->exists()) {
            throw new NotFoundException(__('Invalid Message.'));
        }

        $this->UnattachedMessage->read();
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->UnattachedMessage->save($this->request->data)) {
                $this->_notifyUpdateBackendWorker($programUrl, $this->UnattachedMessage->id);
                $unattachedMessage = $this->request->data;
                $this->Session->setFlash(
                    __('The Message has been saved.'),
                    'default',
                    array('class'=>'message success')
                    );
                $this->redirect(
                    array(
                        'program' => $programUrl,
                        'controller' => 'programUnattachedMessages',
                        'action' => 'index'
                        )
                    );
            } else {
                $this->Session->setFlash(__('The Message could not be saved.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->UnattachedMessage->read(null, $id);
            $now = new DateTime('now');    
            $programTimezone = $this->Session->read($this->params['program'].'_timezone');
            date_timezone_set($now,timezone_open($programTimezone));      
            $messageDate = new DateTime($this->request->data['UnattachedMessage']['fixed-time'], new DateTimeZone($programTimezone));
            if ($now > $messageDate){   
                throw new MethodNotAllowedException(__('Cannot edit a passed Separate Message.'));
            }
            $this->request->data['UnattachedMessage']['fixed-time'] = $messageDate->format('d/m/Y H:i');
            if ($this->request->data['UnattachedMessage']['model-version'] != $this->UnattachedMessage->getModelVersion()) {
                $this->Session->setFlash(__('Due to internal Vusion update, please to carefuly update this Separate Message.'), 
                'default',
                array('class' => "message warning")
                );
            }
        }

        $selectorValues = $this->Participant->getDistinctTagsAndLabels();
        if (count($selectorValues) > 0) {
            $selectors = array_combine($selectorValues, $selectorValues);
        }
        $this->set(compact('selectors'));

        return $unattachedMessage;
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
            $this->Session->setFlash(
                __('Message deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(
                array(
                    'program' => $programUrl,
                    'controller' => 'programUnattachedMessages',
                    'action' => 'index'
                    )
                );
        }
        $this->Session->setFlash(__('Message was not deleted.'), 
                'default',
                array('class' => "message failure")
                );
    }

    
}

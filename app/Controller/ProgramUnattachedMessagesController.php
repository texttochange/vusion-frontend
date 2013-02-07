<?php

App::uses('AppController', 'Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('Schedule', 'Model');
App::uses('Participant', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('DialogueHelper', 'Lib');
App::uses('ProgramSetting', 'Model');
App::uses('History', 'Model');

class ProgramUnattachedMessagesController extends AppController
{

    var $helpers = array('Js' => array('Jquery'), 'Time');
    
    public $uses = array('UnattachedMessage');


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
        $this->VumiRabbitMQ      = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
        $this->DialogueHelper    = new DialogueHelper();
        $this->History           = new History($options);
    }

    protected function _notifyUpdateBackendWorker($workerName, $unattach_id)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName, 'unattach', $unattach_id);
    }

    public function index()
    {
        $unattachedMessages = $this->paginate();       
        
        foreach($unattachedMessages as $unattachedMessage)
        {             
            $unattachId = $unattachedMessage['UnattachedMessage']['_id'];
            $numberOfAllMessageSent = $this->History->getStatusOfUnattachedMessages($unattachId);
            $unattachedMessage['UnattachedMessage']['number-of-message-sent'] = $numberOfAllMessageSent;            
            $numberOfAllMessageDelivered = $this->History->getStatusOfUnattachedMessages($unattachId, 'delivered');
            $unattachedMessage['UnattachedMessage']['number-of-message-delivered'] = $numberOfAllMessageDelivered;
            $numberOfAllMessagePending = $this->History->getStatusOfUnattachedMessages($unattachId, 'pending');
            $unattachedMessage['UnattachedMessage']['number-of-message-pending'] = $numberOfAllMessagePending;
            $numberOfAllMessageFailed = $this->History->getStatusOfUnattachedMessages($unattachId, 'failed');
            $unattachedMessage['UnattachedMessage']['number-of-message-failed'] = $numberOfAllMessageFailed;            
        }        
        $this->set('unattachedMessages', $unattachedMessages);
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->UnattachedMessage->create('unattached-message');
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
        $selectors = array_combine($selectorValues, $selectorValues);
        $this->set(compact('selectors'));        
    }
/*
    protected function _getSelectors()
    {
        $distinctTagsAndLabels = $this->Participant->getDistinctTagsAndLabels();
        if (count($distinctTagsAndLabels) == 0) {
            return array();
        }
        $selectorTagAndLabels = array_combine($distinctTagsAndLabels, $distinctTagsAndLabels);
        return $selectorTagAndLabels;
    }
*/  
    
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
        $selectors = array_combine($selectorValues, $selectorValues);
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

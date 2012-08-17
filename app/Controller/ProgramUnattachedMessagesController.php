<?php

App::uses('AppController', 'Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('Schedule', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('DialogueHelper', 'Lib');
App::uses('ProgramSetting', 'Model');

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
        $this->ProgramSetting    = new ProgramSetting($options);
        $this->VumiRabbitMQ      = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
        $this->DialogueHelper    = new DialogueHelper();
    }

    protected function _notifyUpdateBackendWorker($workerName)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($workerName);
    }


    public function index()
    {
        $unattachedMessages = $this->paginate();
        $this->set(compact('unattachedMessages'));
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->UnattachedMessage->create();
            if (!isset($this->request->data['UnattachedMessage']['fixed-time'])) {
                $now = new DateTime('now');
                $programSettings = $this->ProgramSetting->getProgramSettings();
                date_timezone_set($now,timezone_open($programSettings['timezone']));
                $this->request->data['UnattachedMessage']['fixed-time'] = $this->DialogueHelper->convertDateFormat($now->modify("+1 minute")->format('d/m/Y H:i'));
            }
            if ($this->UnattachedMessage->save($this->request->data)) {
                $this->_notifyUpdateBackendWorker($programUrl);
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
        if ($this->request->is('post') || $this->request->is('put')) {
            if (!isset($this->request->data['UnattachedMessage']['fixed-time'])) {
                $now = new DateTime('now');
                $programSettings = $this->ProgramSetting->getProgramSettings();
                date_timezone_set($now,timezone_open($programSettings['timezone']));
                $this->request->data['UnattachedMessage']['fixed-time'] = $this->DialogueHelper->convertDateFormat($now->modify("+1 minute")->format('d/m/Y H:i'));
            }
            if ($this->UnattachedMessage->save($this->request->data)) {
                $this->_notifyUpdateBackendWorker($programUrl);
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
        }
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

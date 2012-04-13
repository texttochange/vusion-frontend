<?php

App::uses('AppController', 'Controller');
App::uses('UnattachedMessage', 'Model');
App::uses('Schedule', 'Model');
App::uses('VumiRabbitMQ', 'Lib');

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
        $this->VumiRabbitMQ      = new VumiRabbitMQ();
    }

    protected function _notifyUpdateBackendWorker($worker_name)
    {
        $this->VumiRabbitMQ->sendMessageToUpdateSchedule($worker_name);
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
            if ($this->UnattachedMessage->save($this->request->data)) {
            	$this->_notifyUpdateBackendWorker($programUrl);
                $this->Session->setFlash(__('The Message has been saved.'),
                    'default',
                    array('class'=>'success-message')
                );
                $this->redirect(array(
                    'program' => $programUrl,
                    'controller' => 'programUnattachedMessages',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The Message could not be saved.'));
            }
        }    	    
    }
    
    
    public function edit()
    {
        $unattachedMessage = $this->params['unattchedMessage'];
        $id         = $this->params['id'];
        $programUrl = $this->params['program'];
        
        $this->UnattachedMessage->id = $id;
        
        if (!$this->UnattachedMessage->exists()) {
            throw new NotFoundException(__('Invalid Message'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->UnattachedMessage->save($this->request->data)) {
            	$this->_notifyUpdateBackendWorker($programUrl);
                $unattachedMessage = $this->request->data;
                $this->Session->setFlash(__('The Message has been saved.'),
                    'default',
                    array('class'=>'success-message')
                );
                $this->redirect(array(
                    'program' => $programUrl,
                    'controller' => 'programUnattachedMessages',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The Message could not be saved.'));
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
            throw new NotFoundException(__('Invalid Message'));
        }
        
        if ($this->UnattachedMessage->delete()) {
            $this->Schedule->deleteAll(array('unattach-id'=> $id), false);
            $this->Session->setFlash(__('Message deleted'),
                'default',
                array('class'=>'success-message')
            );
            $this->redirect(array(
               'program' => $programUrl,
               'controller' => 'programUnattachedMessages',
               'action' => 'index'
               ));
        }
        $this->Session->setFlash(__('Message was not deleted'));

    }

    
}

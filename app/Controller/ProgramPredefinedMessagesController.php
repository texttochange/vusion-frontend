<?php

App::uses('AppController', 'Controller');
App::uses('PredefinedMessage', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramPredefinedMessagesController extends AppController
{
    public $uses = array('PredefinedMessage');
    
    function constructClasses()
    {
        parent::constructClasses();
        
        $options = array(
            'database' => ($this->Session->read($this->params['program'].'_db'))
            );
        
        $this->PredefinedMessage = new PredefinedMessage($options);
        $this->_instanciateVumiRabbitMQ();
        
    }


    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    public function index()
    {
        $predefinedMessages = $this->paginate();
        $this->set(compact('predefinedMessages', $predefinedMessages));
    }
    
    
    public function add()
    {
        $programUrl = $this->params['program'];
        
        if ($this->request->is('post')) {
            $this->PredefinedMessage->create();
            if ($this->PredefinedMessage->save($this->request->data)) {
                $this->Session->setFlash(__('The predefined message has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programPredefinedMessages',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The predefined message could not be saved.'), 
                'default',
                array('class' => "message failure")
                );
            }
        }
    }
    
    
    public function edit()
    {
        $programUrl = $this->params['program'];
        $id         = $this->params['id'];
        
        $this->PredefinedMessage->id = $id;
        if (!$this->PredefinedMessage->exists()) {
            throw new NotFoundException(__('Invalid predefined message'));
        }
        $predefinedMessage = $this->PredefinedMessage->read();

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->PredefinedMessage->save($this->request->data)) {
                $this->Session->setFlash(__('The predefined message has been saved.'),
                    'default',
                    array('class'=>'message success')
                );
                $this->redirect(array(
                    'program' => $programUrl, 
                    'controller' => 'programPredefinedMessages',
                    'action' => 'index'
                    ));
            } else {
                $this->Session->setFlash(__('The predefined message could not be saved. Please, try again.'), 
                'default',
                array('class' => "message failure")
                );
            }
        } else {
            $this->request->data = $this->PredefinedMessage->read(null, $id);
        }
    }
    
    
    public function delete()
    {
        $id         = $this->params['id'];
        $programUrl = $this->params['program'];
        
        $this->PredefinedMessage->id = $id;
        
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        
        if (!$this->PredefinedMessage->exists()) {
            throw new NotFoundException(__('Invalid Message.'));
        }
        
        if ($this->PredefinedMessage->delete()) {
            $this->Session->setFlash(
                __('Predefined Message deleted'),
                'default',
                array('class'=>'message success')
                );
            $this->redirect(
                array(
                    'program' => $programUrl,
                    'controller' => 'programPredefinedMessages',
                    'action' => 'index'
                    )
                );
        }
        $this->Session->setFlash(__('Predefined Message was not deleted.'), 
                'default',
                array('class' => "message failure")
                );
    }
}

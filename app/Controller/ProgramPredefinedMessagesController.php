<?php

App::uses('AppController', 'Controller');
App::uses('PredefinedMessage', 'Model');

class ProgramPredefinedMessagesController extends AppController
{
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
    }
    
    
    public function view()
    {
    }
    
    
    public function add()
    {
    }
    
    
    public function edit()
    {
    }
    
    
    public function delete()
    {
    }
}

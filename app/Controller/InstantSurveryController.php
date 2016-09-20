<?php
App::uses('AppController', 'Controller');
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('UnmatchableReply', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('ShortCode', 'Model');
App::uses('CreditLog', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class InstantSurveryController extends AppController
{
    var $uses = array(
        'Program', 
        'Group',
        'ShortCode',
        'ProgramSetting');
    
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'UserAccess',
        'NewProgram');
    
    var $helpers = array('Time',
        'Js' => array('Jquery')); 
    
    
    function constructClasses()
    {
        parent::constructClasses();        
        $this->_instanciateVumiRabbitMQ();
    }  
    
    
    public function addSurvery()
    {
        //$data  = $this->NewProgram->ajaxDatapatch($this->data);        
        
        if ($this->request->is('post')) {
            $savedProgram = null;
            $jsonData = $this->request->data;
            
            // print_r($this->request->data);
            
            $data['Program']['name'] = $jsonData['survey']['uid'];
            $data['Program']['url'] = str_replace('_', '', $jsonData['survey']['uid']);
            $data['Program']['database'] = str_replace('_', '', $jsonData['survey']['uid']);
            
            $this->Program->create();
            if ($savedProgram = $this->Program->save($data)) {
                $requestSuccess = true;
                $eventData = array(
                    'programDatabaseName' => $savedProgram['Program']['database'],
                    'programName' => $savedProgram['Program']['name']);
                $this->UserLogMonitor->setEventData($eventData);
                
                //Start the backend
                $this->_startBackendWorker(
                    $savedProgram['Program']['url'],
                    $savedProgram['Program']['database']);
                
                //Set program setting hardcoded
                $this->_setProgramSettings();
                
                //Set closed questions 
                
                
                
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('requestSuccess','savedProgram'));
    }
    
    
    protected function _setProgramSettings()
    {
        $settings = array (
            'shortcode' => '256-8282',
            'international-prefix' => '256',
            'timezone' => 'Africa/Kampala'
            );        
        
        return $this->ProgramSetting->saveProgramSettings($settings);
    }   
    
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    protected function _startBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToCreateWorker($workerName,$databaseName);         
    }
    
    
    protected function _stopBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToRemoveWorker($workerName, $databaseName);         
    }
    
}

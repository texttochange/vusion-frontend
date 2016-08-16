<?php
App::uses('AppController', 'Controller');
App::uses('ProgramSpecificMongoModel', 'Model');
App::uses('ProgramSetting', 'Model');
App::uses('UnmatchableReply', 'Model');
App::uses('Dialogue', 'Model');
App::uses('Request', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('ShortCode', 'Model');
App::uses('CreditLog', 'Model');
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramRemoteSurveryController extends AppController
{
    var $uses = array(
        'Program', 
        'Group',
        'ShortCode',
        'CreditLog');
    
    function constructClasses()
    {
        parent::constructClasses();        
        $this->_instanciateVumiRabbitMQ();
    }
    
    protected function _instanciateVumiRabbitMQ()
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }
    
    
    public function addSurvery()
    {
        $requestSuccess = false;
        $data           = $this->_ajaxDataPatch();
        if ($this->request->is('post')) {
            $savedProgram = null;
            $this->Program->create();
            if ($savedProgram = $this->Program->save($data)) {
                //print_r($savedProgram);
                $requestSuccess = true;
                $eventData = array(
                    'programDatabaseName' => $savedProgram['Program']['database'],
                    'programName' => $savedProgram['Program']['name']);
                $this->UserLogMonitor->setEventData($eventData);
                
                $this->Session->setFlash(__('The program has been saved.'),
                    'default', array('class'=>'message success'));
               /* //Start the backend
                $this->_startBackendWorker(
                    $savedProgram['Program']['url'],
                    $savedProgram['Program']['database']);*/
            } else {
                $this->Session->setFlash(__('The program could not be saved. Please, try again.'));
            }
        }
        
        $programs       = $this->_getPrograms();
        $programOptions = array();        
        foreach($programs as $program) 
            $programOptions[$savedProgram['Program']['id']] = $savedProgram['Program']['name'];         
        $this->set(compact('programOptions', 'requestSuccess'));
        //$this->set(compact('requestSuccess'));*/
    }
    
    
    protected function _ajaxDataPatch($modelName='Program')
    {
        $data = $this->data;
        if (!isset($data[$modelName])) {
            $data = array($modelName => $data);
        }
        return $data;
    }
    
    
    protected function _startBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ->sendMessageToCreateWorker($workerName,$databaseName);         
    }
    
    
    protected function _getPrograms()
    {
        $this->Program->recursive = -1;
        $user                     = $this->Auth->user();
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
            return  $this->Program->find('authorized', array(
                'specific_program_access' => 'true',
                'user_id' => $user['id']));
            
        }
        return $this->Program->find('all');
    }
    
    
    
}

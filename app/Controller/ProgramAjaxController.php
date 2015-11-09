<?php
App::uses('BaseProgramSpecificController', 'Controller');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramAjaxController extends BaseProgramSpecificController
{
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'Stats');
    
    var $statsTypeToView = array(
        'summary' => 'get_stats',
        'participants' => 'participants_nvd3',
        'history' => 'history_nvd3',
        'schedules' => 'schedules_nvd3',
        'top_dialogues_requests' => 'top_dialogues_requests' );


    public function getStats()
    {        
        $requestSuccess  = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        $dbName = $this->programDetails['database'];
        $statsType = $this->Stats->getStatsType();
        $stats = $this->Stats->getStats($dbName, $statsType);
        $this->set(compact('stats', 'requestSuccess'));
        $view = $this->statsTypeToView[$statsType];
        $this->render($view);
    }
    
        
    public function restartWorker()
    {
        $requestSuccess = true;
        if (!$this->request->is('get') || !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        $programUrl   = $this->params['program'];
        $databaseName = $this->Session->read($programUrl.'_db');
        
        $this->_startBackendWorker(
            $programUrl,
            $databaseName);
        $this->Session->setFlash(__('Worker is starting.'));
        $this->set(compact('requestSuccess'));
    }   
    
    
    protected function _startBackendWorker($workerName, $databaseName)
    {
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
        $this->VumiRabbitMQ->sendMessageToCreateWorker($workerName,$databaseName);         
    }
}

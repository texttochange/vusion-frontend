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
        'ProgramSetting',
        'Dialogue');
    
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
            $data = $this->request;
            print_r(var_dump($data));
            //print_r(json_decode($this->request->data, true));
            $jsonData = $this->request->data;
            $data['Program']['name'] = 'Survery_'.''.$jsonData['id'];
            $data['Program']['url'] = 'survery'.''. $jsonData['id'];
            $data['Program']['database'] = 'survery'.''.$jsonData['id'];
            
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
                
                //Set closed questions dialogue 
                $dialogue['Dialogue'] = array(
                    'name' => $jsonData['id'],
                    'auto-enrollment' => 'all',
                    'interactions' => array(),
                    'activated' => 1
                    );
                
                foreach($jsonData['questions'] as $question){
                    $answers =  '';
                    foreach($question['answers'] as $answer) { 
                        $answers[]= array(
                            'choice' => $this->_cleanString($answer['answer_text']), 
                            'answer-actions' => array(
                                0 => array(
                                    'type-action' => 'tagging', 
                                    'tag' => $answer['id']
                                    ) 
                                )
                            );
                    }
                    $dialogue['Dialogue']['interactions'][] = array(
                        'type-schedule' => 'offset-time',
                        'minutes' => '5',
                        'type-interaction' => 'question-answer',
                        'content' => $question['question_text'],
                        'keyword' => strval($question['id']),
                        'type-question' => 'closed-question',                            
                        'label-for-participant-profiling' => 'Answer'.''.$question['id'],
                        'set-answer-accept-no-space'=> 'answer-accept-no-space',
                        'answers' => $answers,
                        'type-unmatching-feedback' => 'no-unmatching-feedback',
                        );
                    
                }
                
                $this->Dialogue->saveDialogue($dialogue['Dialogue']);
                
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
    
    
    protected function _setDialogueForSurvery()
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
    
    protected function _cleanString($string) {
        $string = str_replace(' ', '-', $string);
        return preg_replace('/[^A-Za-z0-9]/', '', $string);
    }
    
    
}

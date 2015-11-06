<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('History','Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('UnattachedMessage','Model');
App::uses('Request', 'Model');
App::uses('Export', 'Model');
App::uses('VumiRabbitMQ', 'Lib');
App::uses('Participant','Model');


class ProgramHistoryController extends BaseProgramSpecificController
{
    
    var $uses = array(
        'History',
        'Dialogue',
        'UnattachedMessage',
        'ProgramSetting',
        'Request',
        'Export');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'LocalizeUtils',
        'Filter',
        'Paginator' => array(
            'className' => 'BigCountPaginator'),
        'ProgramAuth',
        'ArchivedProgram');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time',
        'Paginator' => array('className' => 'BigCountPaginator'),
        'Csv');
    
    
    function constructClasses()
    {
        parent::constructClasses();
        $this->_instanciateVumiRabbitMQ();
    }


    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }


    protected function _notifyBackendExport($exportId)
    {
        $this->VumiRabbitMQ->sendMessageToExport($exportId);
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    
    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        $this->set('programTimezone', $this->Session->read($this->params['program'].'_timezone'));

        $requestSuccess = true;
        $order          = null;
        $conditions     = array(
            'object-type' => array('$in' => $this->History->messageType));

        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        $conditions = $this->Filter->getConditions($this->History, $conditions);
        $userGroupId = $this->Session->read('Auth.User.Group.id');

        $this->paginate = array(
                'all',
                'conditions' => $conditions,
                'order'=> $order);
        $histories = $this->paginate('History');
        if ($userGroupId == 6) {
            $histories = $this->History->getParticipantLabels($histories);
            $this->set(compact('histories', 'requestSuccess', 'order'));
        } else {
            $this->set(compact('histories', 'requestSuccess', 'order'));
        }
    }

    /*
    public function aggregate()
    {
        $requestSuccess = true;
        $time = $this->ProgramSetting->getProgramTimeNow(); 
        $time->modify('-1 year');
        $histories = $this->History->aggregate(DialogueHelper::fromPhpDateToVusionDate($time));
        $this->set(compact('histories', 'requestSuccess'));
        $this->render('index');
    }

    private function _getByTime() 
    {
        $timeframe = 'week';
        $time = $this->ProgramSetting->getProgramTimeNow(); 
        if (isset($this->params['query']['by'])) {
            if ($this->params['query']['by'] == 'program-start') {
                return null;
            } 
            $timeframe = $this->params['query']['by'];
        }
        $time->modify("-1 $timeframe");
        return DialogueHelper::fromPhpDateToVusionDate($time);
    }

    
    public function mostActive() 
    {
        $requestSuccess = true;
        $byTime = $this->_getByTime();
        $dialogueActivities = $this->History->getMostActive($byTime, 'dialogue-id', 'dialogue-id', 'count');
        $dialogueActivities = $this->Dialogue->fromDialogueIdsToNames($dialogueActivities);
        $requestActivities = $this->History->getMostActive($byTime, 'request-id', 'request-id', 'count');
        $requestActivities = $this->Request->fromRequestIdsToKeywords($requestActivities);

        $this->set(compact(
            'dialogueActivities',
            'requestActivities',
            'requestSuccess'));
    }


    public function getStats()
    {
        $requestSuccess = true;
        $time = $this->ProgramSetting->getProgramTimeNow(); 
        $timeframe = 'week';
        if (isset($this->params['query']['by'])) {
            $timeframe = $this->params['query']['by'];
        }
        $time->modify("-1 $timeframe");
        $histories = $this->History->aggregateNvd3(DialogueHelper::fromPhpDateToVusionDate($time));
        $this->set(compact('histories', 'requestSuccess'));
        $this->render('index');
    }*/

    public function listHistory()
    {
        $requestSuccess = true;
        $order          = null;
        $conditions     = array(
            'object-type' => array('$in' => $this->History->messageType));

        if (!$this->_isCsv() && !$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        }

        $conditions = $this->Filter->getConditions($this->History, $conditions);
        $histories = $this->History->find(
            'all',
            array('conditions' => $conditions,
            array('order' => $order)));
        $this->set(compact('histories', 'requestSuccess'));
        $this->render('index');
    }
    
    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->History->filterFields);
    }
    
    
    protected function _getFilterParameterOptions()
    {
        $dialoguesInteractionsContent = $this->Dialogue->getDialoguesInteractionsContent();
        
        return array(
            'operator' => $this->LocalizeUtils->localizeValueInArray($this->History->filterOperatorOptions),
            'dialogue' => $dialoguesInteractionsContent,
            'request' => $this->Request->getRequestFilterOptions(),
            'message-direction' => $this->LocalizeUtils->localizeValueInArray($this->History->filterMessageDirectionOptions),
            'message-status' => $this->LocalizeUtils->localizeValueInArray($this->History->filterMessageStatusOptions),
            'unattach-message' => $this->UnattachedMessage->getNameIdForFilter()            
            );
        
    }
    
    
    public function download()
    {
        $programUrl = $this->params['program'];
        $fileName   = $this->params['url']['file'];
        
        $fileFullPath = WWW_ROOT . "files/programs/" . $programUrl . "/" . $fileName; 
        
        if (!file_exists($fileFullPath)) {
            throw new NotFoundException();
        }
        
        $this->response->header("X-Sendfile: $fileFullPath");
        $this->response->header("Content-type: application/octet-stream");
        $this->response->header('Content-Disposition: attachment; filename="' . basename($fileFullPath) . '"');
        $this->response->send();
    }


    public function exported()
    {
        $programUrl  = $this->programDetails['url'];
        $paginate = array(
            'all',
            'limit' => 100,
            'conditions' => array(
                'database' => $this->programDetails['database'],
                'collection' => 'history'),
            'order' => array('timestamp' => '-1'));
        $this->paginate = $paginate;
        $files = $this->paginate('Export');
        $this->set(compact('files'));
    }


    public function export()
    {
        $programUrl = $this->params['program'];
        $requestSuccess = false;
        
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());

        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))),
            'participant-phone' => array('$regex' => "^\+"));
        $conditions = $this->Filter->getConditions($this->History, $defaultConditions);

        $order = array();
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

        $filePath = Program::ensureProgramDir($programUrl);
        
        $programNow = $this->ProgramSetting->getProgramTimeNow();
        if ($programNow) {
            $timestamp = $programNow->format("Y-m-d_H-i-s");
        } else {
            $timestamp = '';
        }
        $programName  = $this->programDetails['name'];
        $programNameUnderscore = inflector::slug($programName, '_');
        
        $fileName     = $programNameUnderscore . "_history_" . $timestamp . ".csv";
        $fileFullName = $filePath . DS . $fileName;

        $export = array(
            'database' => $this->programDetails['database'],
            'collection' => $this->History->table,
            'conditions' => $conditions,
            'filters' => $this->Filter->getFilters(),
            'order' => $order,
            'file-full-name' => $fileFullName);
        if (!$saved_export = $this->Export->save($export)) {
            $this->Session->setFlash(__("Vusion failed to start the export process."));
        } else {
            $this->_notifyBackendExport($saved_export['Export']['_id']);
            $this->Session->setFlash(
                __("Vusion is backing the export file. Your file should appear shortly on this page."),
                'default', array('class'=>'message success'));
            $requestSuccess = True;
        }
        $this->set(compact('requestSuccess'));

        $this->redirect(array(
            'program' => $programUrl,
            'action' => 'exported'));
    }


    public function deleteExport() 
    {
        $id = $this->params['id'];
        $requestSuccess = false;

        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->Export->id = $id;
        if (!$this->Export->exists()) {
            throw new NotFoundException(__('Invalid Export: %s', $id));
        }

        if ($this->Export->delete()) {
            $this->Session->setFlash(__('Export deleted.'),
                'default', array('class'=>'message success'));
        } else {
            $this->Session->setFlash(__('Export cannot be deleted.'));
        }

        $this->redirect(array(
            'program' => $this->programDetails['url'],
            'action' => 'exported'));
    }
    
    
    public function delete()
    {
        $programUrl = $this->params['program'];
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));
        
        $conditions = $this->Filter->getConditions($this->History, $defaultConditions);
        $result     = $this->History->deleteAll(
            $conditions, 
            false);
        $this->Session->setFlash(
            __('Histories have been deleted.'),
            'default',
            array('class'=>'message success')
            );
        
        if (isset($this->viewVars['urlParams'])) {
            $this->redirect(array(
                'program' => $programUrl,
                'controller' => 'programHistory',
                'action' => 'index',
                '?' => $this->viewVars['urlParams']));
        } else {
            $this->redirect(array(
                'program' => $programUrl,
                'controller' => 'programHistory',
                'action' => 'index'));
        }                   
    }
    
    
    public function paginationCount()
    {
        $requestSuccess = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        $defaultConditions = array('object-type' => array('$in' => $this->History->messageType));
        $paginationCount = $this->History->count( $this->Filter->getConditions($this->History, $defaultConditions), null, -1);
        $this->set(compact('requestSuccess', 'paginationCount'));
    }
    
    
}

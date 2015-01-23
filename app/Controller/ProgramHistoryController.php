<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('History','Model');
App::uses('Dialogue', 'Model');
App::uses('UnattachedMessage','Model');
App::uses('Request', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class ProgramHistoryController extends BaseProgramSpecificController
{
    
    var $uses = array(
        'History',
        'Dialogue',
        'UnattachedMessage',
        'ProgramSetting',
        'Request');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'LocalizeUtils',
        'Filter',
        'Paginator' => array(
            'className' => 'BigCountPaginator'),
        'ProgramAuth',
        'ArchivedProgram',
        'Export');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time',
        'Paginator' => array('className' => 'BigCountPaginator'));
    
    
    function constructClasses()
    {
        parent::constructClasses();
        $this->_instanciateVumiRabbitMQ();
    }


    protected function _instanciateVumiRabbitMQ(){
        $this->VumiRabbitMQ = new VumiRabbitMQ(Configure::read('vusion.rabbitmq'));
    }


    protected function _notifyBackendExport($database, $collection, $filter, $fileFullName, $redisKey)
    {
        $this->VumiRabbitMQ->sendMessageToExportHistory(
            $database, $collection, $filter, $fileFullName, $redisKey);
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
        $this->paginate = array(
            'all',
            'conditions' => $conditions,
            'order'=> $order);
        $histories = $this->paginate('History');
        $this->set(compact('histories', 'requestSuccess'));
    }


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
        $programUrl            = $this->programDetails['url'];
        $programDirPath        = WWW_ROOT . "files/programs/". $programUrl;
        $fileCurrenltyExported = $this->Export->hasExports($programUrl, 'history'); 
        $exportedFiles         = scandir($programDirPath);
        $exportedHistoryFiles  = array_filter($exportedFiles, function($var) { 
            return strpos($var, 'history');});
        $files = array();
        foreach ($exportedHistoryFiles as $file) {
            $fileFullName = $programDirPath . DS . $file;
            $files[] = array(
                'name' =>  $file,
                'size' => filesize($fileFullName),
                'created' => filemtime($fileFullName));
        }
        $created = array();
        foreach ($files as $key => $row) {
            $created[$key] = $row['created'];
        }
        array_multisort($created, SORT_DESC, $files);
        $this->set(compact('files', 'fileCurrenltyExported'));
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
            array('object-type' => array('$exists' => false))));
        $conditions = $this->Filter->getConditions($this->History, $defaultConditions);

        $filePath = WWW_ROOT . "files/programs/" . $programUrl;
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

        $redisKey = $this->Export->startAnExport($programUrl, 'history');

        $this->_notifyBackendExport(
            $this->programDetails['database'],
            $this->History->table,
            $conditions,
            $fileFullName,
            $redisKey);

        $this->Session->setFlash(
            __("Vusion is backing the export file. Your file should appear shortly on this page."),
            'default', array('class'=>'message success'));

        $requestSuccess = True;
        $this->set(compact('requestSuccess'));

        $this->redirect(array(
            'program' => $programUrl,
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

<?php
App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('User', 'Model');
App::uses('VumiRabbitMQ', 'Lib');


class UnmatchableReplyController extends AppController
{

    var $uses = array(
        'UnmatchableReply', 
        'User');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'LocalizeUtils',
        'PhoneNumber',
        'UserAccess',
        'Filter',
        'Paginator' => array(
            'className' => 'BigCountPaginator'),
        'Export');
    var $helpers = array(
        'Js' => array('Jquery'), 
        'Time', 
        'PhoneNumber',
        'Paginator' => array(
            'className' => 'BigCountPaginator'));
    
    
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
        $this->VumiRabbitMQ->sendMessageToExportUnmatchableReply(
            $database, $collection, $filter, $fileFullName, $redisKey);
    }


    public function index()
    {
        $requestSuccess = true;
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $defaultConditions = $this->UserAccess->getUnmatchableConditions();
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
        
        $this->paginate = array(
            'all',
            'conditions' => $this->Filter->getConditions($this->UnmatchableReply, $defaultConditions, $countryPrefixes),
            'order'=> $order,
            );
        $countriesIndexes   = $this->PhoneNumber->getCountriesByPrefixes();
        $unmatchableReplies = $this->paginate('UnmatchableReply');
        $this->set(compact('requestSuccess', 'unmatchableReplies', 'countriesIndexes'));
    }

    
    protected function _getFilterFieldOptions()
    {   
        return $this->LocalizeUtils->localizeLabelInArray(
            $this->UnmatchableReply->filterFields);
    }
    
    
    protected function _getFilterParameterOptions()
    {
        return array(
            'operator' => $this->UnmatchableReply->filterOperatorOptions,
            'country' => $this->PhoneNumber->getCountries()
            );
    }
    
    
    public function paginationCount()
    {
        $requestSuccess = true;
        if (!$this->_isAjax()) {
            throw new MethodNotAllowedException();
        }
        $defaultConditions = array();
        $paginationCount   = $this->UnmatchableReply->count($this->Filter->getConditions($this->UnmatchableReply, $defaultConditions), null, -1);
        $this->set(compact('requestSuccess', 'paginationCount'));
    }
    
    
    public function download()
    {
        $url          = $this->params['controller'];
        $fileName     = $this->params['url']['file'];        
        $fileFullPath = WWW_ROOT . "files/programs/" . $url . "/" . $fileName; 
        
        if (!file_exists($fileFullPath)) {
            throw new NotFoundException();
        }
        
        $this->response->header("X-Sendfile: $fileFullPath");
        $this->response->header("Content-type: application/octet-stream");
        $this->response->header('Content-Disposition: attachment; filename="' . basename($fileFullPath) . '"');
        $this->response->send();
    }
    
    
    public function export()
    {
        $url            = $this->params['controller'];
        $requestSuccess = false;
        
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());

        $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = $this->UserAccess->getUnmatchableConditions();
        
        $conditions = $this->Filter->getConditions($this->UnmatchableReply, $defaultConditions, $countryPrefixes);

        $filePath = WWW_ROOT . "files/programs/unmatchableReply" ;
        if (!file_exists($filePath)) {
            mkdir($filePath);
        }
        $now      = new DateTime('now');
        $fileName = 'Unmatchable_Reply_' . $now->format("Y-m-d_H-i-s") . ".csv";
        $fileFullName = $filePath . DS . $fileName;

        $redisKey = $this->Export->startAnExport(
            'vusion', 'unmatchable-reply');

        $this->_notifyBackendExport(
            'vusion',
            $this->UnmatchableReply->table,
            $conditions,
            $fileFullName,
            $redisKey);

        $this->Session->setFlash(
            __("Vusion is backing the export file. Your file should appear shortly on this page."),
            'default', array('class'=>'message success'));

        $requestSuccess = True;
        $this->set(compact('requestSuccess'));
        
        $this->redirect(array(
            'action' => 'exported'));
    }


    public function exported()
    {
        $unmatchableReplyDirPath  = WWW_ROOT . "files/programs/unmatchableReply/";
        $fileCurrenltyExported    = $this->Export->hasExports('vusion', 'unmatchableReply');
        if (file_exists($unmatchableReplyDirPath)) {
            $exportedFiles = scandir($unmatchableReplyDirPath);
        } else {
            $exportedFiles = array();
        }
        $exportedUnmatchableReplyFiles = array_filter($exportedFiles, function($var) { 
            return strpos($var, '.csv');});
        $files = array();
        foreach ($exportedUnmatchableReplyFiles as $file) {
            $fileFullName = $unmatchableReplyDirPath . DS . $file;
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

    
    
}

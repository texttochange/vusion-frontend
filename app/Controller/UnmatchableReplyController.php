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
        'User',
        'Export');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'LocalizeUtils',
        'Country',
        'UserAccess',
        'Filter',
        'Paginator' => array(
            'className' => 'BigCountPaginator'));
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


    protected function _notifyBackendExport($exportId)
    {
        $this->VumiRabbitMQ->sendMessageToExport($exportId);
    }


    public function index()
    {
        $order          = null;
        $requestSuccess = true;
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $defaultConditions = $this->UserAccess->getUnmatchableConditions();
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $countryPrefixes = $this->Country->getPrefixesByNames();
        
        $this->paginate = array(
            'all',
            'conditions' => $this->Filter->getConditions($this->UnmatchableReply, $defaultConditions, $countryPrefixes),
            'order'=> $order,
            );
        $countriesIndexes   = $this->Country->getNamesByPrefixes();
        $unmatchableReplies = $this->paginate('UnmatchableReply');
        $this->set(compact('requestSuccess', 'unmatchableReplies', 'countriesIndexes', 'order'));
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
            'country' => $this->Country->getNamesByNames()
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
        $order          = null;
        $url            = $this->params['controller'];
        $requestSuccess = false;
        
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());

        if (isset($this->params['named']['sort']) &&  isset($this->params['named']['direction'])) {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = $this->UserAccess->getUnmatchableConditions();
        $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
        $conditions = $this->Filter->getConditions($this->UnmatchableReply, $defaultConditions, $countryPrefixes);

        $filePath = WWW_ROOT . "files/programs/unmatchableReply" ;
        if (!file_exists($filePath)) {
            mkdir($filePath);
        }
        $now      = new DateTime('now');
        $fileName = 'Unmatchable_Reply_' . $now->format("Y-m-d_H-i-s") . ".csv";
        $fileFullName = $filePath . DS . $fileName;

        $export = array(
            'database' => 'vusion',
            'collection' => $this->UnmatchableReply->table,
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
        
        $this->redirect(array('action' => 'exported'));
    }


    public function exported()
    {
        $programUrl  = $this->programDetails['url'];
        $paginate = array(
            'all',
            'limit' => 100,
            'conditions' => array(
                'database' => 'vusion',
                'collection' => 'unmatchable_reply'),
            'order' => array('timestamp' => '-1'));
        $this->paginate = $paginate;
        $files = $this->paginate('Export');
        $this->set(compact('files'));
    }


    public function deleteExport() 
    {
        $id = $this->params['named']['id'];
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

        $this->redirect(array('action' => 'exported'));
    }
    
    
}

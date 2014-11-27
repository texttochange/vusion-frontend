<?php
App::uses('BaseProgramSpecificController','Controller');
App::uses('History','Model');
App::uses('Dialogue', 'Model');
App::uses('UnattachedMessage','Model');
App::uses('Request', 'Model');


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
        'ArchivedProgram');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time',
        'Paginator' => array('className' => 'BigCountPaginator'));
    
    
    function constructClasses()
    {
        parent::constructClasses();
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
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        } else {
            $order = null;
        }
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('object-type' => array('$in' => $this->History->messageType));
        
        if ($this->params['ext'] === 'csv' || $this->_isAjax()) {
            $statuses = $this->History->find(
                'all', 
                array('conditions' => $this->Filter->getConditions($this->History, $defaultConditions)),
                array('order' => $order));
        } else {   
            $this->paginate = array(
                'all',
                'conditions' => $this->Filter->getConditions($this->History, $defaultConditions),
                'order'=> $order);            
            $statuses = $this->paginate('History');
        }
        $this->set(compact('statuses', 'requestSuccess'));
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
    
    
    public function export()
    {
        $programUrl = $this->params['program'];
        $requestSuccess = false;
        
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $paginate = array(
            'all',
            'limit' => 500,
            'maxLimit' => 500);
        
        if (!isset($this->params['named']['sort'])) {
            $paginate['order'] = array('timestamp' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));
        
        $conditions = $this->Filter->getConditions($this->History, $defaultConditions);
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        try {
            //First a tmp file is created
            $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
            
            if (!file_exists($filePath)) {
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            $programNow  = $this->ProgramSetting->getProgramTimeNow();
            $programName = $this->Session->read($programUrl.'_name');
            
            $programNameUnderscore = inflector::slug($programName, '_');
            
            $fileName     = $programNameUnderscore . "_history_" . $programNow->format("Y-m-d_H-i-s") . ".csv";            
            $fileFullPath = $filePath . "/" . $fileName;
            $handle       = fopen($fileFullPath, "w");
            
            $headers = array('participant-phone','message-direction','message-status','message-content','timestamp');
            //write the headers
            fputcsv($handle, $headers,',','"');
            
            //Now extract the data and copy it into the file
            
            $historyCount = $this->History->find('count', array('conditions'=> $conditions));
            $pageCount    = intval(ceil($historyCount / $paginate['limit']));
            for($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate = $paginate;
                $statuses = $this->paginate('History');
                foreach($statuses as $status) {
                    $line = array();
                    foreach($headers as $header) {
                        if (isset($status['History'][$header])) {
                            $line[] = $status['History'][$header];
                        } else {
                            $line[] = "";
                        }
                    }
                    fputcsv($handle, $line,',' , '"' );
                }
            }
            $requestSuccess = true;
            $this->set(compact('requestSuccess', 'fileName'));
        } catch (Exception $e) {
            $this->Session->setFlash($e->getMessage());
            $this->set(compact('requestSuccess'));
        }
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

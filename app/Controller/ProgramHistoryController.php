<?php
App::uses('AppController','Controller');
App::uses('History','Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('UnattachedMessage','Model');
App::uses('Request', 'Model');


class ProgramHistoryController extends AppController
{
    
    var $uses       = array('History');
    var $components = array('RequestHandler', 'LocalizeUtils');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time',
        'Paginator' => array('className' => 'BigPaginator')
        );
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
        $options                 = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->History           = new History($options);
        $this->Dialogue          = new Dialogue($options);
        $this->DialogueHelper    = new DialogueHelper();
        $this->UnattachedMessage = new UnattachedMessage($options);
        $this->ProgramSetting    = new ProgramSetting($options);
        $this->Request           = new Request($options);
    }
    
    
    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        $this->set('programTimezone', $this->Session->read($this->params['program'].'_timezone'));
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('object-type' => array('$in' => $this->History->messageType));
        
        if ($this->params['ext'] == 'csv' or $this->params['ext'] == 'json') {
            $statuses = $this->History->find(
                'all', 
                array('conditions' => $this->_getConditions($defaultConditions)),
                array('order'=> $order));
            $this->set(compact('statuses')); 
        } else {   
            $this->paginate = array(
                'all',
                'conditions' => $this->_getConditions($defaultConditions),
                'order'=> $order);
            
            $statuses = $this->paginate();
            $this->set(compact('statuses'));
        }
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
        $fileName = $this->params['url']['file'];
        
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
        
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $paginate = array(
            'all',
            'limit' => 500,
            'maxLimit' => 500);
        
        if (!isset($this->params['named']['sort'])) {
            $paginate['order'] = array('timestamp' => 'desc');
        } else {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));
        
        $conditions = $this->_getConditions($defaultConditions);
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        try {
            ##First a tmp file is created
            $filePath = WWW_ROOT . "files/programs/" . $programUrl; 
            
            if (!file_exists($filePath)) {
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            $programNow = $this->ProgramSetting->getProgramTimeNow();
            $programName = $this->Session->read($programUrl.'_name');
            $fileName = $programName . "_history_" . $programNow->format("Y-m-d_H-i-s") . ".csv";
            
            $fileFullPath = $filePath . "/" . $fileName;
            
            $handle = fopen($fileFullPath, "w");
            
            $headers = array('participant-phone','message-direction','message-status','message-content','timestamp');
            ##write the headers
            fputcsv($handle, $headers,',','"');
            
            ##Now extract the data and copy it into the file
            
            $historyCount = $this->History->find('count', array('conditions'=> $conditions));
            $pageCount = intval(ceil($historyCount / $paginate['limit']));
            for($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate = $paginate;
                $statuses = $this->paginate();
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
            
            $this->set(compact('fileName'));
        } catch (Exception $e) {
            $this->set('errorMessage', $e->getMessage());
        }
    }
    
    
    protected function _getConditions($defaultConditions)
    {
        $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));
        
        if (!isset($filter['filter_param'])) {
            return $defaultConditions;
        }
        
        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->History->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }     
        
        $this->set('urlParams', http_build_query($filter));
        
        $conditions = $this->History->fromFilterToQueryConditions($filter);
        
        if ($conditions == array()) {
            $conditions = $defaultConditions;
        } else {
            $conditions = array('$and' => array(
                $defaultConditions,
                $conditions));
        }
        
        return $conditions;        
    }
    
    
    public function delete()
    {
        
        $programUrl = $this->params['program'];
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));
        
        $conditions = $this->_getConditions($defaultConditions);
        $result = $this->History->deleteAll(
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
        if ($this->params['ext'] !== 'json') {
            return; 
        }
        $defaultConditions = array('object-type' => array('$in' => $this->History->messageType));
        $paginationCount = $this->History->count(
            $this->_getConditions($defaultConditions),
            null,
            -1);
        $this->set('paginationCount',$paginationCount);
    }


}

<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('User', 'Model');

class UnmatchableReplyController extends AppController
{
    
    var $components = array('RequestHandler',
        'LocalizeUtils',
        'PhoneNumber',
        'ProgramPaginator',
        'UserAccess',
        'Filter');
    var $helpers = array(
        'Js' => array('Jquery'), 
        'Time', 
        'PhoneNumber',
        'Paginator' => array('className' => 'BigCountPaginator'));
  
    
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    
    
    public function constructClasses()
    {
        parent::constructClasses();
        
        if (!Configure::read("mongo_db")) {
            $options = array(
                'database' => 'vusion'
                );
        } else {
            $options = array(
                'database' => Configure::read("mongo_db")
                );
        }
        $this->UnmatchableReply = new UnmatchableReply($options);
        $this->DialogueHelper   = new DialogueHelper();
        $this->User             = ClassRegistry::init('User');
    }
    
    
    public function index()
    {
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
        $unmatchableReplies = $this->paginate();
        $this->set(compact('unmatchableReplies', 'countriesIndexes'));
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
        if ($this->params['ext'] !== 'json') {
            return; 
        }
        $defaultConditions = array();
        $paginationCount   = $this->UnmatchableReply->count($this->Filter->getConditions($this->UnmatchableReply, $defaultConditions), null, -1);
        $this->set('paginationCount', $paginationCount);
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
        $url = $this->params['controller'];
        
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
        
        $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
        
        // Only get messages and avoid other stuff like markers
        $defaultConditions = $this->UserAccess->getUnmatchableConditions();
        
        $conditions = $this->Filter->getConditions($this->UnmatchableReply, $defaultConditions, $countryPrefixes);
        if ($conditions != null) {
            $paginate['conditions'] = $conditions;
        }
        
        try {
            ##First a tmp file is created
            $filePath = WWW_ROOT . "files/programs/" . $url; 
            
            if (!file_exists($filePath)) {
                mkdir($filePath);
                chmod($filePath, 0764);
            }
            
            $now      = new DateTime('now');
            $fileName = $url . "_history_" . $now->format("Y-m-d_H-i-s") . ".csv";
            
            $fileFullPath = $filePath . "/" . $fileName;
            
            $handle = fopen($fileFullPath, "w");
            
            $headers = array('participant-phone','to','message-content','timestamp');
            ##write the headers
            fputcsv($handle, $headers,',','"');
            
            ##Now extract the data and copy it into the file
            
            $unmatchableReplyCount = $this->UnmatchableReply->find('count', array('conditions'=> $conditions));
            $pageCount = intval(ceil($unmatchableReplyCount / $paginate['limit']));
            for($count = 1; $count <= $pageCount; $count++) {
                $paginate['page'] = $count;
                $this->paginate = $paginate;
                $unmatchableReplies = $this->paginate();
                foreach($unmatchableReplies as $unmatchableReply) {
                    $line = array();
                    foreach($headers as $header) {
                        if (isset($unmatchableReply['UnmatchableReply'][$header])) {
                            $line[] = $unmatchableReply['UnmatchableReply'][$header];
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
    

}

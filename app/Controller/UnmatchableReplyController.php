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
    

}

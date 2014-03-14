<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');

class UnmatchableReplyController extends AppController
{
    
    var $components = array('RequestHandler', 'LocalizeUtils', 'PhoneNumber');
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
    }
    
    
    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $this->paginate = array(
            'all',
            'conditions' => $this->_getConditions(),
            'order'=> $order,
            );
        $countriesIndexes = $this->PhoneNumber->getCountriesByPrefixes();
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
    
    
    protected function _getConditions($conditions = null)
    {
        $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));
        $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
        
        if (!isset($filter['filter_param'])) 
            return null;
        
        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->UnmatchableReply->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }     
        
        $this->set('urlParams', http_build_query($filter));
        
        return $this->UnmatchableReply->fromFilterToQueryConditions($filter, $countryPrefixes);
    }
    

    public function paginationCount()
    {
        if ($this->params['ext'] !== 'json') {
            return; 
        }
        $defaultConditions = array();
        $paginationCount = $this->UnmatchableReply->count($this->_getConditions($defaultConditions), null, -1);
        $this->set('paginationCount', $paginationCount);
    }

}

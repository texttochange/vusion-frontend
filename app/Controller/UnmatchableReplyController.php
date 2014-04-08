<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');
App::uses('User', 'Model');

class UnmatchableReplyController extends AppController
{
    
    var $components = array('RequestHandler', 'LocalizeUtils', 'PhoneNumber', 'ProgramPaginator');
    var $helpers = array(
        'Js' => array('Jquery'), 
        'Time', 
        'PhoneNumber',
        'Paginator' => array('className' => 'BigCountPaginator'));
  
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        
        $user = $this->Auth->user();
        if (!$this->User->hasUnmatchableReplyAccess($user['id'])) {
            $this->redirect(array('controller'=>'programs', 'action' => 'index'));
        }
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
        $this->User             = new User();
    }
    
    
    public function index()
    {
        $this->set('filterFieldOptions', $this->_getFilterFieldOptions());
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        
        $defaultConditions = $this->_getUserAccessCondition();
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }
        
        $this->paginate = array(
            'all',
            'conditions' => $this->_getConditions($defaultConditions),
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
    
    
    protected function _getConditions($defaultConditions)
    {
        $filter = array_intersect_key($this->params['url'], array_flip(array('filter_param', 'filter_operator')));
        $countryPrefixes = $this->PhoneNumber->getPrefixesByCountries();
        
        if (!isset($filter['filter_param'])) 
            return $defaultConditions;
        
        if (!isset($filter['filter_operator']) || !in_array($filter['filter_operator'], $this->UnmatchableReply->filterOperatorOptions)) {
            throw new FilterException('Filter operator is missing or not allowed.');
        }     
        
        $this->set('urlParams', http_build_query($filter));
        
        $conditions = $this->UnmatchableReply->fromFilterToQueryConditions($filter, $countryPrefixes);
        
        if ($conditions == array()) {
            $conditions = $defaultConditions;
        } else if ($conditions != array() && $defaultConditions != array()) {
            $conditions = array('$and' => array(
                $defaultConditions,
                $conditions));
        }
        
        return $conditions;
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
    
    protected function _getUserAccessCondition()
    {
        $accessCondition = array();
        
        $user = $this->Auth->user();
        if ($this->User->hasUnmatchableReplyAccess($user['id'])) {
            $programs = $this->Program->find('authorized', array(
                'specific_program_access' => 'true',
                'user_id' => $user['id']));
            $index = 0;
            foreach ($programs as &$program) {
                $program = $this->ProgramPaginator->getProgramDetails($program);
                $shortcodes[$index] = $program['program']['Program']['shortcode'];
                $codes[$index] = substr(strrchr($shortcodes[$index], "-"), 1);
                $index++;
            }
            $accessCondition = array('$or' => array(
                array('participant-phone' => array('$in' => $shortcodes)),
                array('to' => array('$in' => $codes)),
                ));
        }
        return $accessCondition;
    }

}

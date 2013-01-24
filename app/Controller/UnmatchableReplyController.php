<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');
App::uses('DialogueHelper', 'Lib');

class UnmatchableReplyController extends AppController
{

    var $helpers = array('Js' => array('Jquery'), 'Time');


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
        $this->set('filterFieldOptions', $this->UnmatchableReply->filterFields);
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

        $unmatchableReplies = $this->paginate();//print_r($unmatchableReplies);
        $this->set(compact('unmatchableReplies'));
    }


    protected function _getFilterParameterOptions()
    {
        return array(
            'operator' => $this->UnmatchableReply->filterOperatorOptions);
    }

    
    protected function _getConditions($conditions = null)
    {
        
        $onlyFilterParams = array_intersect_key($this->params['url'], array_flip(array('filter_param')));

        if (!isset($onlyFilterParams['filter_param'])) 
            return $conditions;

         if (!isset($this->params['url']['filter_operator'])) {
            $this->Session->setFlash(
                __('The filter operator is missing.'), 
                'default',
                array('class' => "message failure"));
            return null;
        }

        $filterOperator = $this->params['url']['filter_operator'];
        if (!in_array($filterOperator, array('all', 'any'))) {
                $this->Session->setFlash(
                    __("The filter operator \"$filterOperator\" is not allowed, by default using \"all\"."), 
                    'default',
                    array('class' => "message failure"));
                $filterOperator = 'all';
        }
       
        $urlParams = http_build_query($onlyFilterParams);
        $this->set('urlParams', $urlParams);
        
        $conditions = $this->UnmatchableReply->fromFilterToQueryConditions($filterOperator, $onlyFilterParams);
        
        return $conditions;
    }

    
}

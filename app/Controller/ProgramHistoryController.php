<?php

App::uses('AppController','Controller');
App::uses('History','Model');
App::uses('Dialogue', 'Model');
App::uses('DialogueHelper', 'Lib');

class ProgramHistoryController extends AppController
{

    public $uses    = array('History');
    var $components = array('RequestHandler');
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time'
        );

    function constructClasses()
    {
        parent::constructClasses();
        
        $options              = array('database' => ($this->Session->read($this->params['program']."_db")));
        $this->History        = new History($options);
        $this->Dialogue       = new Dialogue($options);
        $this->dialogueHelper = new DialogueHelper();
    }


    public function beforeFilter()
    {
        parent::beforeFilter();
        //$this->Auth->allow('*');
    }


    public function index()
    {
        $this->set('filterFieldOptions', $this->History->filterFields);
        $this->set('filterParameterOptions', $this->_getFilterParameterOptions());
        $this->set('programTimezone', $this->Session->read($this->params['program'].'_timezone'));
        
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));

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


    protected function _getFilterParameterOptions()
    {
        $dialoguesInteractionsContent = $this->Dialogue->getDialoguesInteractionsContent();
  
        return array(
            'dialogue' => $dialoguesInteractionsContent,
            'message-direction' => $this->History->filterMessageDirectionOptions,
            'message-status' => $this->History->filterMessageStatusOptions);
    }

        
    public function export()
    {    
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

        // Only get messages and avoid other stuff like markers
        $defaultConditions = array('$or' => array(
            array('object-type' => array('$in' => $this->History->messageType)),
            array('object-type' => array('$exists' => false))));

        $exportParams = array(
            'fields' => array('participant-phone','message-direction','message-status','message-content','timestamp'),
            'conditions' => $this->_getConditions($defaultConditions),
            'order'=> $order,
        );
        
        $data = $this->History->find('all', $exportParams);
        $this->set(compact('data'));
    }

    protected function _getConditions($conditions)
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
        
        $conditions = $this->History->fromFilterToQueryConditions($filterOperator, $onlyFilterParams);
        
        return $conditions;
    }

    public function delete() {
        
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
                
        $this->redirect(array(
                    'program' => $programUrl,
                    'controller' => 'programHistory',
                    'action' => 'index',
                    '?' => $this->viewVars['urlParams']));
                   
    }
    

}

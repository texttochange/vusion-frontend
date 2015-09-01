<?php
App::uses('AppController','Controller');
App::uses('UserLog','Model');

class UserLogsController extends AppController
{
    var $uses = array(
        'UserLog',
        'User',
        'Program');
    var $components = array(
        'Filter',
        'RequestHandler',
        'LocalizeUtils');
    var $helpers = array(
        'Csv');


    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    

    private function _getOrder()
    {
        if (!isset($this->params['named']['sort'])) {
            return array('timestamp' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            return array($this->params['named']['sort'] => $this->params['named']['direction']);
        } 
        return null;
    }
    

    public function index()
    {   
        $this->_setFilterOptions();
        $paginate = array('all');
        $paginate['conditions'] = $this->Filter->getConditions($this->UserLog);
        $paginate['order'] = $this->_getOrder();
        $this->paginate = $paginate;
        
        $this->set('userLogs', $this->paginate());
    }


    public function exportUserLog()
    {
        $userLogs = $this->UserLog->find('all', array(
            'conditions' => $this->Filter->getConditions($this->UserLog),
            'order' => $this->_getOrder()));
        $this->set(compact('userLogs'));
        $this->render('export');
    }


    protected function _setFilterOptions()
    {
        $this->set('filterFieldOptions', 
            $this->LocalizeUtils->localizeLabelInArray($this->UserLog->filterFields));
        $this->set('filterParameterOptions', array(
            'operator' => $this->UserLog->filterOperatorOptions,
            'user' => $this->User->find('list'),
            'program' => $this->Program->find('listByDatabase')));
    }


}

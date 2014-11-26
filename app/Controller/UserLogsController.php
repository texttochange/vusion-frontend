<?php
App::uses('AppController','Controller');
App::uses('UserLog','Model');

class UserLogsController extends AppController
{
    var $uses = array('UserLog');
    
    
    public function beforeFilter()
    {
        parent::beforeFilter();
    }
    
    
    public function index()
    {        
        $paginate = array('all');
        
        if (!isset($this->params['named']['sort'])) {
            $paginate['order'] = array('timestamp' => 'desc');
        } else if (isset($this->params['named']['direction'])) {
            $paginate['order'] = array($this->params['named']['sort'] => $this->params['named']['direction']);
        } else {
            $paginate['order'] = null;
        }
        
        $this->paginate = $paginate;
        $userLogs       = $this->UserLog->getUserLogs();
        
        $this->set('userLogs', $this->paginate());
    }

}

<?php

App::uses('AppController', 'Controller');
App::uses('UnmatchableReply', 'Model');

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
        
        $options = array(
            'database' => 'vusion'
            );
        
        $this->UnmatchableReply = new UnmatchableReply($options);
    }


    public function index()
    {
        if (!isset($this->params['named']['sort'])) {
            $order = array('timestamp' => 'desc');
        } else {
            $order = array($this->params['named']['sort'] => $this->params['named']['direction']);
        }

         $this->paginate = array(
                'all',
                'order'=> $order,
            );

        $unmatchableReplies = $this->paginate();
        $this->set(compact('unmatchableReplies'));
    }

    
}

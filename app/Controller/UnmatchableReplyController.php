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
        $unmatchableReplies = $this->paginate();
        $this->set(compact('unmatchableReplies'));
    }

    
}

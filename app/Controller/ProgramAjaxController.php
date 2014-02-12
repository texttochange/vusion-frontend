<?php
App::uses('AppController', 'Controller');


class ProgramAjaxController extends AppController
{
    var $components = array(
        'RequestHandler',
        'Stats');
    var $uses = array('Program', 'Group');

    function constructClasses()
    {
        parent::constructClasses();
    }
    
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->RequestHandler->accepts('json');
    }
    
    
    public function getProgramStatsCached()
    { 
        
    }
}

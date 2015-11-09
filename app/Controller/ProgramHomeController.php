<?php
App::uses('BaseProgramSpecificController','Controller');


class ProgramHomeController extends BaseProgramSpecificController
{
    var $uses = array(
        'ParticipantStats',
        'History',
        'Schedule',
        'Dialogue',
        'UnattachedMessage',
        'ProgramSetting');
    var $components = array(
        'RequestHandler' => array(
            'viewClassMap' => array(
                'json' => 'View')),
        'ProgramAuth',
        'ArchivedProgram');
    
    var $helpers    = array(
        'Js' => array('Jquery'),
        'Time');
    
    
    function constructClasses()
    {
        parent::constructClasses();
    }
    
    
    function beforeFilter()
    {
        parent::beforeFilter();
    }    
    

    public function index()
    {
    }
    
    
}

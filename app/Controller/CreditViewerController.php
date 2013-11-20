<?php
App::uses('AppController', 'Controller');
App::uses('Program', 'Model');

class CreditViewerController extends AppController
{
    var $components = array('ProgramPaginator', 'CreditManager');
    
    public function beforeFilter()
    {    
        parent::beforeFilter();
    }
    
    
    public function constructClasses()
    {
        parent::constructClasses();
        
        $this->Program = new Program();
    }
    
    
    public function index()
    {
        $programs = $this->Program->find('all');
        foreach ($programs as &$program) {
            $progDetails = $this->ProgramPaginator->getProgramDetails($program);
            $program = array_merge($program, $progDetails['program']);
            $program['Program']['total-credits'] = $this->CreditManager->getCount($program['Program']['database']);
        }
        $programs = $this->ProgramPaginator->paginate($programs);
        $this->set(compact('programs', $programs));
    }
    
    
}

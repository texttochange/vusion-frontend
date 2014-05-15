<?php
App::uses('Component', 'Controller');
//App::uses('Program', 'Model');
//App::uses('Group', 'Model');

class UserAccessComponent extends Component
{
    public $components = array('Auth', 'ProgramPaginator'); 


    public function __construct(ComponentCollection $collection, $settings = array())
    {
        //$settings         = array_merge($this->settings, (array)$settings);
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
        
        $this->Program = ClassRegistry::init('Program');
        $this->Group   = ClassRegistry::init('Group');
    }
    
    
    public function getUnmatchableConditions()
    {
        $conditions = array();
        //print_r($this->Controller);
        
        $user = $this->Auth->user();
        
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
            $programs = $this->Program->find('authorized', array(
                'specific_program_access' => 'true',
                'user_id' => $user['id']));
            $index = 0;
            foreach ($programs as &$program) {
                $program = $this->ProgramPaginator->getProgramDetails($program);
                print_r($program);
                $prefixedCodes[$index] = $program['program']['Program']['shortcode'];
                $codes[$index] = substr(strrchr($prefixedCodes[$index], "-"), 1);
                $index++;
            }
            $conditions = array('$or' => array(
                array('participant-phone' => array('$in' => $prefixedCodes)),
                array('to' => array('$in' => $codes)),
                ));
        }
        return $conditions;
    }
    
    
}

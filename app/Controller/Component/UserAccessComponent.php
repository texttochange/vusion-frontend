<?php
App::uses('Component', 'Controller');

class UserAccessComponent extends Component
{
    public $components = array('Auth', 'ProgramPaginator'); 


    public function __construct(ComponentCollection $collection, $settings = array())
    {
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
        
        $this->Program = ClassRegistry::init('Program');
        $this->Group   = ClassRegistry::init('Group');
    }
    
    
    public function getUnmatchableConditions()
    {
        $conditions = array();        
        $user = $this->Auth->user();
        if ($this->Group->hasSpecificProgramAccess($user['group_id'])) {
            $programs = $this->Program->find('authorized', array(
                'specific_program_access' => 'true',
                'user_id' => $user['id']));
            $index = 0;
            foreach ($programs as &$program) {
                $program = $this->ProgramPaginator->getProgramDetails($program);
                if (isset($program['Program']['prefixed-shortcode'])) {
                   $prefixedCodes[$index] = $program['Program']['prefixed-shortcode'];
                }
                if (isset($program['Program']['shortcode'])) {
                   $codes[$index] = $program['Program']['shortcode'];
                }
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

<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');

class NewProgramComponent extends Component
{
    var $uses = array('Program');   
    
    
    public function ajaxDataPatch($data, $modelName='Program')
    {
        
        if (!isset($data[$modelName])) {
            $data = array($modelName => $data);
        }
        return $data;
    }
    
    
}

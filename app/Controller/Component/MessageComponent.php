<?php

App::uses('Component', 'Controller');

class MessageComponent extends Component 
{
    
    var $localizedValueLabels = array();
    
    public function startup($controller)
    {
        $this->localizedValueLabels = array(
            "name" => __('Name'),
            "characters" => __('characters'),
            "content" => __('Content'),
            "immediately" => __('Immediately'),
            "fixed-time" => __('Fixed time'),
            "save"=> __('Save'),
            "any"=> __('any'),
            "all"=> __('all'),
            );
        $controller->set(
            'messageLabels',
            $this->localizedValueLabels);
    }
}

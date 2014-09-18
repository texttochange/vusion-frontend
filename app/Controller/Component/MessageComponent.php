<?php

App::uses('Component', 'Controller');

class MessageComponent extends Component 
{
    
    var $localizedValueLabels = array();
    
    public function __construct(ComponentCollection $collection, $settings = array())
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
            "Sending the report by email, it might take a few minutes..."=> __('Sending the report by email, it might take a few minutes...'),
            );
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
        $this->Controller->set('messageLabels', $this->localizedValueLabels);
    }
}

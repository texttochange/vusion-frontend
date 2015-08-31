<?php

App::uses('Component', 'Controller');

class SimulatorComponent extends Component 
{
    
    var $localizedValueLabels = array();
    
    public function startup($controller)
    {
        $this->localizedValueLabels = array(
            "Phone" => __('Phone'),
            "Last Optin Date" => __('Last Optin Date'),
            "Last Optout Date" => __('Last Optout Date'),
            "Labels" => __('Labels'),
            "Tags" => __('Tags')
            );
        $controller->set('simulatorLabels', $this->localizedValueLabels);
    }
}

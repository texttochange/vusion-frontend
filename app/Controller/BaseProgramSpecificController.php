<?php
App::uses('AppController','Controller');


class BaseProgramSpecificController extends AppController
{
	var $programDetails    = array();
	var $isProgramSpecific = true;

	public function _initialize($specificDatabase)
    {
        foreach ($this->uses as $modelClass) {
            if (/*property_exists($this->{$modelClass}, 'specific') &&*/ !empty($this->{$modelClass}->specific)) {
                $this->{$modelClass}->setDatabase($specificDatabase);
            }
        }
    }

}
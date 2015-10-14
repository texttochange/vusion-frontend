<?php
App::uses('BaseProgramSpecificController', 'Controller');


class MashComponent extends Component
{

	public function initialize(BaseProgramSpecificController $controller)
	{
		$this->controller = $controller;
	}

	public function importParticipants($country) {
		$importMaxParticipants = Configure::read('vusion.importMaxParticipants');
        require_once('mash-api/lib/MashApi.php');
        $api = new MashApi(
            Configure::read('vusion.mash.apiKey'),
            Configure::read('vusion.mash.url'));
        $participants = $api->getParticipants(
            $country,
            $this->controller->programDetails['url'],
            $importMaxParticipants);
        return $participants;
	}

}
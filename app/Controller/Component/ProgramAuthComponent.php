<?php
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramAuthComponent extends Component
{
	var $components = array('Auth');


	public function startup($controller) 
    {	
        if (!$this->Auth->loggedIn()) {
            throw new ForbiddenException();
        }
        if (empty($controller->params['program'])) {
            throw new NotFoundException(__("Program url is missing."));
        }
        $controller->Program->recursive = -1;
        
        $data = $controller->Program->find('authorized', array(
            'specific_program_access' => $controller->Group->hasSpecificProgramAccess(
                $this->Auth->user('group_id')),
            'user_id' => $this->Auth->user('id'),
            'program_url' => $controller->params['program']
            ));
        if (count($data)==0) {
            throw new NotFoundException( __("Program url not found: %s", $controller->params['program']));
        }

        $programDetails = array();
        foreach (array('name', 'url', 'database', 'status') as $key) {
            $programDetails[$key] = $data[0]['Program'][$key];
        }
        $controller->Session->write($programDetails['url']."_db", $programDetails['database']);
        $controller->Session->write($programDetails['url']."_name", $programDetails['name']);
        
        $programSettingModel = ProgramSpecificMongoModel::init('ProgramSetting', $programDetails['database']);
        $programDetails['settings'] = $programSettingModel->getProgramSettings();
        $controller->programDetails = $programDetails;

        $controller->_initialize($programDetails['database']);

        $controller->set(compact('programDetails'));
        
        //In case of a Json request, no need to set up the variables
        if ($controller->_isAjax() || $controller->params['ext']=='csv') {
            return;
        }
        $currentProgramData = $this->_getCurrentProgramData($programDetails['database']);            
        $programStats       = array('programStats' => $controller->Stats->getProgramStats($programDetails['database'], true));
        $creditStatus       = $controller->CreditManager->getOverview($programDetails['database']);
        $controller->set(compact(
            'currentProgramData',
            'programStats',
            'creditStatus')); 
    }


	protected function _getcurrentProgramData($databaseName)
    {
        $unattachedMessageModel = ProgramSpecificMongoModel::init('UnattachedMessage', $databaseName);
        $predefinedMessageModel = ProgramSpecificMongoModel::init('PredefinedMessage', $databaseName);
        $dialogueModel = ProgramSpecificMongoModel::init('Dialogue', $databaseName);
        $requestModel  = ProgramSpecificMongoModel::init('Request', $databaseName);

        $currentProgramData = array(
            'unattachedMessages' => array(
                'scheduled' => $unattachedMessageModel->find('scheduled'),
                'drafted' => $unattachedMessageModel->find('drafted')),
            'predefinedMessages' => $predefinedMessageModel->find('all'),
            'dialogues' => $dialogueModel->getActiveAndDraft(),
            'requests' => $requestModel->find('all'),
            );
        return $currentProgramData;
    }


}


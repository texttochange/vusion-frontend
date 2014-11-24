<?php
App::uses('ProgramSpecificMongoModel', 'Model');


class ProgramAuthComponent extends Component
{
	var $components = array('Auth');

	public function startup($controller) 
    {	
       if ($this->Auth->loggedIn() && !empty($controller->params['program'])) {
            $controller->Program->recursive = -1;
            
            $data = $controller->Program->find('authorized', array(
                'specific_program_access' => $controller->Group->hasSpecificProgramAccess(
                    $controller->Auth->user('group_id')),
                'user_id' => $controller->Auth->user('id'),
                'program_url' => $controller->params['program']
                ));
            if (count($data)==0) {
                throw new NotFoundException('Could not find this page.');
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
            /*
            if (!$controller->ArchivedProgram->isAllowed()) {
                $controller->_stop();
            }
            */
            //In case of a Json request, no need to set up the variables
            if ($controller->_isAjax() || $controller->params['ext']=='csv') {
                return;
            }
            $currentProgramData = $this->_getCurrentProgramData($programDetails['database']);            
            $programLogsUpdates = $controller->LogManager->getLogs($programDetails['database'], 5);
            $programStats       = array('programStats' => $controller->Stats->getProgramStats($programDetails['database'], true));
            $creditStatus       = $controller->CreditManager->getOverview($programDetails['database']);
            $controller->set(compact(
                'currentProgramData',
                'programLogsUpdates',
                'programStats',
                'creditStatus')); 
        } 
	}

	protected function _getcurrentProgramData($databaseName)
    {
        /*$unattachedMessageModel = new UnattachedMessage(array('database' => $databaseName));
        $predefinedMessageModel = new PredefinedMessage(array('database' => $databaseName));        
        $dialogueModel = new Dialogue(array('database' => $databaseName));
        $requestModel  = new Request(array('database' => $databaseName));*/
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


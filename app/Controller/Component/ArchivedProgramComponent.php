<?php

class ArchivedProgramComponent extends Component
{

	var $components = array('Session');

	public function initialize($controller)
	{
		$this->archivedAuthorization = array(
			'default' => array(
				'POST' => array(
					'add' => __('Adding this is not allowed within an archived program.'),
					'edit' => __('Editing this is not allowed within archived program.'),
					'delete' => __('Deleting this is not allowed within archived program.'))
				),
			'programHome' => array(
				'GET' => array(
					'restartWorker' => __('Restart worker is not allowed in archived program.'))
				),
			'programDialogues' => array(
				'POST' => array(
					'save' => __('Saving a Dialogue is not allowed within archived program.'),
					'delete' => __('Deleting a Dialogue is not allowed within archived program.'),
					'testSendAllMessages' => __('Sending all dialogue messages is not allowed withing a archived program.'),
					'activate' => __('Activation of Dialogue is not allowed within an archived program'),
					'validateKeyword' => __('The validation of keyword is not allowed within archived program.'))
				), 
			'programRequests' => array(
				'POST' => array(
					'save' => __('Saving this is not allowed within archived program.'),
					'delete' => __('Deleting this is not allowed within archived program.'),
					'validateKeyword' => __('The validation of keyword is not allowed within archived program.'))
				), 
			);
	}


	public function startup($controller) 
	{
		if (!$this->isAllowed($controller)) {
        	$controller->_stop();
        }
	}


	public function isAllowed($controller)
	{
		if (!isset($controller->params['program'])) {
			return true;
		}
		if ($controller->programDetails['status'] != 'archived'){
			return true;
		}
		$controllerName = 'default';
		if (isset($this->archivedAuthorization[$controller->request->params['controller']])){
			$controllerName = $controller->request->params['controller'];
		} 
		$method = $controller->request->method();

		$action = 'index';
		if (isset($controller->request->action)) {
			$action = $controller->request->action;
		}
		if (!isset($this->archivedAuthorization[$controllerName][$method][$action])){
			return true;
		} else {
			$this->Session->setFlash($this->archivedAuthorization[$controllerName][$method][$action]);
			if ($controller->request->is('ajax')) {
				$controller->set('requestSuccess', false);
				$controller->render('/Elements/ajax_return', 'ajax');
				$controller->response->send();
			} else {
				$controller->redirect(array(
					'program' => $controller->params['program'],
					'action' => 'index'));
			}
			return false;
		}
		return true;
	}


}
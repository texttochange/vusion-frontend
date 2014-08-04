<?php

class ArchivedProgramComponent extends Component
{

	var $components = array('Session');

	public function initialize($controller)
	{
		$this->Controller = $controller;
		
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


	public function isAllowed()
	{
		if (!isset($this->Controller->params['program'])) {
			return true;
		}
		if ($this->Controller->programDetails['status'] != 'archived'){
			return true;
		}
		$controller = 'default';
		if (isset($this->archivedAuthorization[$this->Controller->request->params['controller']])){
			$controller = $this->Controller->request->params['controller'];
		} 
		$method = $this->Controller->request->method();

		$action = 'index';
		if (isset($this->Controller->request->action)) {
			$action = $this->Controller->request->action;
		}
		if (!isset($this->archivedAuthorization[$controller][$method][$action])){
			return true;
		} else {
			$this->Session->setFlash($this->archivedAuthorization[$controller][$method][$action]);
			if ($this->Controller->request->is('ajax')) {
				$this->Controller->set('ajaxResult', array('status' => 'fail'));
				$this->Controller->render('/Elements/ajax_return', 'ajax');
				$this->Controller->response->send();
			} else {
				$this->Controller->redirect(array(
					'program' => $this->Controller->params['program'],
					'action' => 'index'));
			}
			return false;
		}
		return true;
	}


}
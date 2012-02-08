<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class Script extends MongoModel {

	var $specific = true;	
/**
 * Display field
 *
 * @var string
 */
 	var $name = 'Script';
	var $useDbConfig = 'mongo';
	
	public $findMethods = array(
		'draft' => true,
		'countDraft' => true,
		'active' => true,
		'countActive' => true,
		'count' => true
		);
	
	protected function _findActive($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['order']['created'] = 'desc';
			$query['conditions']['Script.activated'] = 1;
			return $query;
		}
		return $results;
	}
	
	protected function _findCountActive($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['fields'] = 'count';
			$query['conditions']['Script.activated'] = 1;
			return $query;
		}
		return $results;
	}
	
	protected function _findCountDraft($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['fields'] = 'count';
			$query['conditions']['Script.activated'] = 0;
			return $query;
		}
		return $results;
	}
	
	
	protected function _findDraft($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions']['Script.activated'] = 0;
			return $query;
		}
		return $results;
	}
	
	public function beforeValidate(){
		if (!(isset($this->data['Script']['activated']))) {
			$this->data['Script']['activated'] = 0;
		}
		return true;
	}
	
	/** TODO: the choice of updating or saving should be done here.
	* however it's not working throwing a duplicate key.
	* It seems the beforeSave is too late to have the action change 
	* from saving to updating.
	*/
	public function beforeSave(){
			
		return true;		
	}
	
	public function makeDraftActive() {
		$draft = $this->find('draft');
		if ($draft) {
			$draft[0]['Script']['activated'] = 1;
			$this->create();
			$this->id = $draft[0]['Script']['_id'];
			$this->save($draft[0]['Script']);
			return $draft[0]['Script'];
		}
		return false;
	}
	
	
}

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
		'active' => true,
		'draft' => true
		);
	
	public function _findActive($state, $query, $results = array()) {
		if ($state = 'before') {
			$query['conditions'] = array(
				'Script.activated' => true
				);
			return $query;
		}
		return $results;
	}
	
	public function _findDraft($state, $query, $results = array()) {
		if ($state = 'before') {
			$query['conditions'] = array(
				'Script.activated' => false
				);
			return $query;
		}
		return $results;
	}
	
}

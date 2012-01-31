<?php
App::uses('AppModel', 'Model');
/**
 * Program Model
 *
 */
class Program extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
	var $hasAndBelongsToMany = 'User';
	
	public $findMethods = array('authorized' => true);
	
	public function _findAuthorized($state, $query, $results = array()) {
		//print_r($query);
		if ($state == 'before') {
			if ($query['specific_program_access']) {
			$query['joins'] = array(
				array(
					'table' => 'programs_users',
					'alias' => 'ProgramUser',
					'type' => 'LEFT',
					'conditions' => array(
							'Program.id = ProgramUser.program_id'
						)
					)
				);
			$query['conditions'] = array(
				'ProgramUser.user_id' => $query['user_id']
				);
			if (!empty($query['program_url'])) {
				$query['conditions'] = array_merge(
					$query['conditions'],
					array('Program.url' => $query['program_url'])
				); 
			}
			}
			return $query;
		}
		return $results;
	}
	
	/*
	public function beforeFind($query){
		print_r($query);
		if ($query['specific_program_access']) {
			$query['joins'] = array(
				array(
					'table' => 'programs_users',
					'alias' => 'ProgramUser',
					'type' => 'LEFT',
					'conditions' => array(
							'Program.id = ProgramUser.program_id'
						)
					)
				);
			$query['conditions'] = array(
				'ProgramUser.user_id' => $query['user_id']
				);
		}
		return $query;
	}
	*/
}

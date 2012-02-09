<?php
App::uses('MongoModel', 'Model');
/**
 * Program Model
 *
 */
class ParticipantsState extends MongoModel {

	var $specific = true;	

	
/**
 * Display field
 *
 * @var string
 */
 	//var $name = 'ParticipantStat';
	var $useDbConfig = 'mongo';	
	var $useTable = 'status';
	
	public $findMethods = array(
		'participant' => true,
		'count' => true
		);
	
	public function _findParticipant($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = array('participant-phone' => $query['phone']);
			return $query;
		}
		return $results;
	}
	
}

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
	
}

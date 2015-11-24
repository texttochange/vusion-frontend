<?php
App::uses('ProgramSpecificMongoModel', 'Model');


class ParticipantStats extends ProgramSpecificMongoModel
{
	var $useTable = 'participants_stats';

	function getModelVersion()
    {
       return '1';
    }

    function getRequiredFields($objectType=null)
    {
        return array(
        	'value');
    }

}
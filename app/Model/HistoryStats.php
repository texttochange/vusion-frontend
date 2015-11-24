<?php
App::uses('ProgramSpecificMongoModel', 'Model');


class HistoryStats extends ProgramSpecificMongoModel
{

	var $useTable = 'history_stats';

	function getModelVersion()
    {
       return '1';
    }	

    function getRequiredFields($objectType=null)
    {
        return array(
        	'incoming',
            'outgoing');
    }

}
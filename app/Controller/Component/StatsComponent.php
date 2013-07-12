<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');

class StatsComponent extends Component {
	
	public function getRedis()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        return $redis;
    }
	protected function _getProgramStats($program)
	{
		$database           = $program['Program']['database'];
		$tempParticipant = new Participant(array('database' => $database));                
		$activeParticipantCount = $tempParticipant->find(
			'count', array(
				'conditions' => array('session-id' => array(
					'$ne' => null)
					)
				)
			);
		$participantCount = $tempParticipant->find('count'); 
		
		$tempHistory     = new History(array('database' => $database)); 
		$AllReceivedMessagesCount = $tempHistory->find(
			'count',array(
				'conditions' => array('message-direction' => 'incoming'))
			);
		
		$AllSentMessagesCount = $tempHistory->find(
			'count',array(
				'conditions' => array('message-direction' => 'outgoing'))
			);
		$historyCount  = $tempHistory->find(
			'count', array(
				'conditions' => array('object-type' => array('$in' => $tempHistory->messageType))));
		
		$tempSchedule = new Schedule(array('database' => $database));
		$now = new DateTime('now');
		$todayScheduleCount = $tempSchedule->find(
			'count',array(
				'conditions' => array('date-time' => $now)
				));
		$scheduleCount = $tempSchedule->find('count');
		
		$programStats = array(
			'active-participant-count' => $activeParticipantCount,
			'participant-count' => $participantCount,
			'all-received-messages-count'=> $AllReceivedMessagesCount,
			'all-sent-messages-count' => $AllSentMessagesCount,
			'history-count' => $historyCount,
			'today-schedule-count' => $todayScheduleCount,
			'schedule-count' => $scheduleCount);
		
		return $programStats;
	}
	
	public function getProgramStats($program)
	{
		$database           = $program['Program']['database'];
		$redis = $this->getRedis();
		$statsKey = 'vusion:programs:'.$database.':stats';
		
		$stats = $redis->get($statsKey);
		
		if($redis->strlen($statsKey) > 0){
			$programStats = (array)json_decode($stats);
		}else{
			$programStats = $this->_getProgramStats($program);
			$redis->setex($statsKey, 6,json_encode($programStats));
		}
		ie	
		return $program;
	}
}
?>

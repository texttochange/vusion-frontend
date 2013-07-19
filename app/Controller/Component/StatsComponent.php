<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

class StatsComponent extends Component {
	
	
	public function getRedis()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        return $redis;
    }
	protected function _getProgramStats($database)
	{
		$this->ProgramSetting = new ProgramSetting(array('database' => $database));
		$programTimeNow = $this->ProgramSetting->getProgramTimeNow();
		
		$tempParticipant = new Participant(array('database' => $database));                
		$activeParticipantCount = $tempParticipant->find(
			'count', array(
				'conditions' => array('session-id' => array(
					'$ne' => null)
					)
				)
			);
		$participantCount = $tempParticipant->find('count'); 
		
		$tempSchedule = new Schedule(array('database' => $database));
		$programTimeToday = $programTimeNow->modify('+1 day');
		$todayScheduleCount = $tempSchedule->find(
			'count',array(
				'conditions' => array(
					'date-time' => array(
						'$lt' => $programTimeToday->format(DateTime::ISO8601))
					)
				));
		
		$scheduleCount = $tempSchedule->find('count');
		$tempHistory     = new History(array('database' => $database));
		$programTimeForMonth = $programTimeNow->format("Y-m-d\TH:i:s");		
		$first_second = date('Y-m-01\TH:i:s', strtotime($programTimeForMonth));
		$last_second = date('Y-m-t\TH:i:s', strtotime($programTimeForMonth));
		
		$allReceivedMessagesCount = $tempHistory->find(
			'count',array(
				'conditions' => array('message-direction' => 'incoming'))
			);
		
		$currentMonthReceivedMessagesCount = $tempHistory->find(
			'count',array(
				'conditions' => array(
					'timestamp' => array(
						'$gt' => $first_second,
						'$lt' => $last_second
						),
					'message-direction' => 'incoming'
					)
				)
			);
		
		$currentMonthSentMessagesCount = $tempHistory->find(
			'count',array(
				'conditions' => array(
					'timestamp' => array(
						'$gt' => $first_second,
						'$lt' => $last_second
						),
					'message-direction' => 'outgoing'
					)
				)
			);
		
		$totalCurrentMonthMessagesCount = $currentMonthSentMessagesCount + $currentMonthReceivedMessagesCount;
		
		$allSentMessagesCount = $tempHistory->find(
			'count',array(
				'conditions' => array('message-direction' => 'outgoing'))
			);
		$historyCount  = $tempHistory->find(
			'count', array(
				'conditions' => array('object-type' => array('$in' => $tempHistory->messageType))));
		
		$programStats = array(
			'active-participant-count' => $activeParticipantCount,
			'participant-count' => $participantCount,
			'all-received-messages-count'=> $allReceivedMessagesCount,
			'current-month-received-messages-count' => $currentMonthReceivedMessagesCount,
			'all-sent-messages-count' => $allSentMessagesCount,
			'current-month-sent-messages-count' => $currentMonthSentMessagesCount,
			'total-current-month-messages-count' => $totalCurrentMonthMessagesCount,
			'history-count' => $historyCount,
			'today-schedule-count' => $todayScheduleCount,
			'schedule-count' => $scheduleCount,
			'object-type' => 'program-stats',
			'model-version'=> '1');
		return $programStats;
	}
	
	public function getProgramStats($database)
	{
		$redis = $this->getRedis();
		$statsKey = 'vusion:programs:'.$database.':stats';
		$stats = $redis->get($statsKey);
		
		if($redis->strlen($statsKey) > 0){
			$programStats = (array)json_decode($stats);
		}else{
			$programStats = $this->_getProgramStats($database);
			$redis->setex($statsKey, 6,json_encode($programStats));
		}
		return $programStats;
	}
}
?>

<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');

class StatsComponent extends Component {

	public function getProgramStats($program)
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
		
		//$currentMonthReceivedMessagesCount = $tempHistory->find(
		//	'count',array(
		//		'conditions' => array('message-direction' => 'incoming',))
		//	);
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
		//$stats = array(
		$program['Program']['active-participant-count'] = $activeParticipantCount;
		$program['Program']['participant-count'] = $participantCount; 
		$program['Program']['all-received-messages-count'] = $AllReceivedMessagesCount;
		//array_values($currentMonthReceivedMessagesCount),
		$program['Program']['all-sent-messages-count'] = $AllSentMessagesCount;
		$program['Program']['history-count'] = $historyCount;
		$program['Program']['today-schedule-count'] = $todayScheduleCount;
		$program['Program']['schedule-count'] = $scheduleCount;
		return $program;
		
	}
}
?>

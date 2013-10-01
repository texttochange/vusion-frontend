<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

class StatsComponent extends Component 
{
    
    public $Controller = null;
    
    public function initialize(Controller $controller)
    {
        parent::startup($controller);
        $this->Controller = $controller;
        $this->cacheStatsExpire = 60;
        
        if(isset($this->Controller->redis)){
            $this->redis = $this->Controller->redis;
        }else{ 
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1');
        }
        
        if(isset($this->Controller->redisProgramPrefix)){
            $this->redisProgramPrefix = $this->Controller->redisProgramPrefix;
        }else{
            $this->redisProgramPrefix = 'vusion:programs';
        }
    }
    
    protected function _getProgramStats($database)
    {
        $programStats = array(
            'active-participant-count' => 'N/A',
            'participant-count' => 'N/A',
            'all-received-messages-count'=> 'N/A',
            'current-month-received-messages-count' => 'N/A',
            'all-sent-messages-count' => 'N/A',
            'current-month-sent-messages-count' => 'N/A',
            'total-current-month-messages-count' => 'N/A',
            'history-count' => 'N/A',
            'today-schedule-count' => 'N/A',
            'schedule-count' => 'N/A',
            'object-type' => 'program-stats',
            'model-version'=> '1');
        try{
            $this->ProgramSetting = new ProgramSetting(array('database' => $database));
            $programTimeNow = $this->ProgramSetting->getProgramTimeNow();            
            if(empty($programTimeNow)){
                return $programStats;
            }
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
                    'conditions' =>array(
                        '$or' => array(
                            array('object-type' => array('$in' => $tempHistory->messageType)),
                            array('object-type' => array('$exists' => false ))
                            )
                        )));
            
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
        } catch (Exception $e) {
            return $programStats;
        }
    }
    
    protected function _getStatsKey($database)
    {
        return $this->redisProgramPrefix.':'.$database.':stats';
    }
    
    public function getProgramStats($database)
    {
        $statsKey = $this->_getStatsKey($database);
        $stats = $this->redis->get($statsKey);
        
        if($stats != null){
            $programStats = (array)json_decode($stats);
        }else{
            $programStats = $this->_getProgramStats($database);
            $this->redis->setex($statsKey, $this->cacheStatsExpire, json_encode($programStats));
        }
        return $programStats;
    }
}
?>

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
        $this->cacheStatsExpire = 10;
        
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
    
    public function getProgramStat($model, $conditions=array())
    {
        try {            
            return $model->find('count', $conditions);
        } catch (Exception $e) { 
            return 'N/A';
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
        
        $this->ProgramSetting = new ProgramSetting(array('database' => $database));
        $programTimeNow = $this->ProgramSetting->getProgramTimeNow();            
        if(empty($programTimeNow)){
            return $programStats;
        }
        $tempParticipant = new Participant(array('database' => $database));
        $programStats['active-participant-count'] = $this->getProgramStat(
        	$tempParticipant,
        	array(
        		'conditions' => array('session-id' => array(
        			'$ne' => null)
        			)
        		)
        	);
        
        $programStats['participant-count'] = $this->getProgramStat($tempParticipant);
        
        $tempSchedule = new Schedule(array('database' => $database));
        $programTimeToday = $programTimeNow->modify('+1 day');        
        $programStats['today-schedule-count'] = $this->getProgramStat(
            $tempSchedule, 
        	array(
                'conditions' => array(
                    'date-time' => array(
                        '$lt' => $programTimeToday->format(DateTime::ISO8601))
                    )
                )
        	);
        
        $programStats['schedule-count'] = $this->getProgramStat($tempSchedule);
        
        $tempHistory     = new History(array('database' => $database));
        $programTimeForMonth = $programTimeNow->format("Y-m-d\TH:i:s");        
        $first_second = date('Y-m-01\TH:i:s', strtotime($programTimeForMonth));
        $last_second = date('Y-m-t\TH:i:s', strtotime($programTimeForMonth));
        $programStats['all-received-messages-count'] = $this->getProgramStat(
            $tempHistory,
        	array(
                'conditions' => array('message-direction' => 'incoming'))
        	);
        
        $programStats['current-month-received-messages-count'] = $this->getProgramStat(
            $tempHistory,
        	array(
                'conditions' => array(
                    'timestamp' => array(
                        '$gt' => $first_second,
                        '$lt' => $last_second
                        ),
                    'message-direction' => 'incoming'
                    )
                )
        	);
        
        $programStats['current-month-sent-messages-count'] = $this->getProgramStat(
            $tempHistory,
        	array(
                'conditions' => array(
                    'timestamp' => array(
                        '$gt' => $first_second,
                        '$lt' => $last_second
                        ),
                    'message-direction' => 'outgoing'
                    )
                )
        	);
        
        if($programStats['current-month-sent-messages-count'] === 'N/A' || $programStats['current-month-received-messages-count'] === 'N/A' ){
        	$totalCurrentMonthMessagesCount = 'N/A';
        } else {
        	$totalCurrentMonthMessagesCount =  $programStats['current-month-sent-messages-count'] + $programStats['current-month-received-messages-count'];
        }
        $programStats['total-current-month-messages-count'] = $totalCurrentMonthMessagesCount;
        
        $programStats['all-sent-messages-count'] = $this->getProgramStat(
            $tempHistory,
        	array(
                'conditions' => array('message-direction' => 'outgoing'))
        	);
        
        $programStats['history-count'] = $this->getProgramStat(
            $tempHistory,
        	array(
                'conditions' =>array(
                    '$or' => array(
                        array('object-type' => array('$in' => $tempHistory->messageType)),
                        array('object-type' => array('$exists' => false ))
                        )
                    )
                )
        	);  
        
        return $programStats;        
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

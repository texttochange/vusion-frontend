<?php
App::uses('Component', 'Controller');
App::uses('Program', 'Model');
App::uses('ProgramSetting', 'Model');

class StatsComponent extends Component 
{
    
    public $Controller = null;
    
    
    var $localizedValueLabel = array();
    
    public function __construct(ComponentCollection $collection, $settings = array())
    {
        $this->localizedValueLabel = array(
            'scheduled' => __('scheduled'),
            'sent' => __('sent'),
            'received' => __('received'),
            'message(s)' => __('message(s)'),
            'participant(s)' => __('participant(s)'),
            'Participant(s) Optin/Total' => __('Participant(s) Optin/Total'),
            'Message(s)-Total(Current-Month)' => __('Message(s) Total(Current Month)'),
            'Received Total(Current Month)' => __('Received Total(Current Month)'),
            'Sent Total(Current Month)' => __('Sent Total(Current Month)'),
            'Schedule Total(Today)' => __('Schedule Total(Today)'),
            'total message(s)' => __('total message(s)'),
            'sent message(s)' => __('sent message(s)'),
            'schedule(s)' => __('schedule(s)'),
            'Optin/Total participant(s)' => __('Optin/Total participant(s)'),
            'Total(total current month) message(s)' => __('Total(total current month) message(s)'),
            'Total(current month) received' => __('Total(current month) received'),
            'Total(current month) sent' => __('Total(current month) sent'),
            'Total(today) schedule(s)' => __('Total(today) schedule(s)'),            
            'Stats Not Available' => __('Stats Not Available'),
            );
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
    }
    
    
    public function initialize(Controller $controller)
    {
        parent::startup($controller);
        $this->Controller = $controller;
        $this->cacheStatsExpire = Configure::read('vusion.cacheStatsExpire');
        if ($this->cacheStatsExpire == null) {
            //A default value for all
            $this->cacheStatsExpire = array(
                '1000' => '120');
        } 
        
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
        
        $this->Controller->set('statsLabels', $this->localizedValueLabel);
    }
    
    
    public function getProgramStat($model, $conditions=array())
    {
        try {
            return $model->count($conditions);
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
        
        $tempProgramSetting = new ProgramSetting(array('database' => $database));
        $programTimeNow = $tempProgramSetting->getProgramTimeNow();            
        if(empty($programTimeNow)){
            return $programStats;
        }
        $tempParticipant = new Participant(array('database' => $database));
        $programStats['active-participant-count'] = $this->getProgramStat(
            $tempParticipant,
            array('session-id' => array('$ne' => null)));
        
        $programStats['participant-count'] = $this->getProgramStat($tempParticipant);
        
        $tempSchedule = new Schedule(array('database' => $database));
        $programTimeToday = $programTimeNow->modify('+1 day');        
        $programStats['today-schedule-count'] = $this->getProgramStat(
            $tempSchedule, 
            array('date-time' => array('$lt' => $programTimeToday->format(DateTime::ISO8601))));
        
        $programStats['schedule-count'] = $this->getProgramStat($tempSchedule);
        
        $tempHistory     = new History(array('database' => $database));
        $programTimeForMonth = $programTimeNow->format("Y-m-d\TH:i:s");        
        $first_second = date('Y-m-01\TH:i:s', strtotime($programTimeForMonth));
        $last_second = date('Y-m-t\TH:i:s', strtotime($programTimeForMonth));
        $programStats['all-received-messages-count'] = $this->getProgramStat(
            $tempHistory,
            array('message-direction' => 'incoming'));
        
        $programStats['current-month-received-messages-count'] = $this->getProgramStat(
            $tempHistory,
            array(
                'timestamp' => array(
                    '$gt' => $first_second,
                    '$lt' => $last_second),
                'message-direction' => 'incoming'));
        
        $programStats['current-month-sent-messages-count'] = $this->getProgramStat(
            $tempHistory,
            array(
                'timestamp' => array(
                    '$gt' => $first_second,
                    '$lt' => $last_second
                    ),
                'message-direction' => 'outgoing'));
        
        if($programStats['current-month-sent-messages-count'] === 'N/A' || $programStats['current-month-received-messages-count'] === 'N/A' ){
            $totalCurrentMonthMessagesCount = 'N/A';
        } else {
            $totalCurrentMonthMessagesCount =  $programStats['current-month-sent-messages-count'] + $programStats['current-month-received-messages-count'];
        }
        $programStats['total-current-month-messages-count'] = $totalCurrentMonthMessagesCount;
        
        $programStats['all-sent-messages-count'] = $this->getProgramStat(
            $tempHistory,
            array('message-direction' => 'outgoing'));
        
        $programStats['history-count'] = $this->getProgramStat(
            $tempHistory,
            array(
                '$or' => array(
                    array('object-type' => array('$in' => $tempHistory->messageType)),
                    array('object-type' => array('$exists' => false ))
                    )));  
        
        unset($tempProgramSetting);
        unset($tempParticipant);
        unset($tempHistory);
        unset($tempSchedule);
        return $programStats;        
    }
    
    
    protected function _getStatsKey($database)
    {
        return $this->redisProgramPrefix.':'.$database.':stats';
    }
    
    
    public function getProgramStats($database, $onlyCached=false)
    {
        $statsKey = $this->_getStatsKey($database);
        $stats = $this->redis->get($statsKey);
        
        if ($stats != null) {
            return (array)json_decode($stats);
        }
        
        if ($onlyCached) {   
            return  null;
        }
        
        $start = time();
        $programStats = $this->_getProgramStats($database);
        $end = time();
        $duration = $end - $start;
        $expiring = $this->_getTimeToCacheStatsExpire($duration);
        $this->redis->setex($statsKey, $expiring, json_encode($programStats));
        return $programStats;
    }
    
    
    public function _getTimeToCacheStatsExpire($duration) 
    {
        foreach ($this->cacheStatsExpire as $computationDuration => $cacheDuration) {
            if ($duration <= $computationDuration) {
                return $cacheDuration;
            }
        }
        return end($this->cacheStatsExpire);
    }
    
    
}
?>

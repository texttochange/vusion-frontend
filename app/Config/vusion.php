<?php
$config = array(  
    'vusion' => array(
        'rabbitmq' => array(
            'vhost' => '/develop',
            'username' => 'vumi',
            'password' => 'vumi',
            ),
        'redis' => array(
            'host' => 'localhost',
            'port' => '6379'
            ),
        'cacheStatsExpire' => array(
            1 => 30,       #1sec cache 30sec 
            5 => 120,      #5sec cache 3mins
            40 => 600,     #40sec cache 15mins
            90 => 3600,    #90sec cache 1h
            150 => 10800,  #150sec cache 3h
            151 => 21600)  #above 150sec cache 9sh
        )
    );      

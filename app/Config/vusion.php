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
            5 => 240,      #5sec cache 4mins
            40 => 900,     #40sec cache 15mins
            90 => 3600,    #90sec cache 1h
            150 => 10800,  #150sec cache 3h
            151 => 32400  #above 150sec cache 9h
            ),
        'domain' => 'vusion.texttochange.org',
        'captchaSettings' => array(
            'font'            => '../webroot/files/fonts/BIRTH_OF_A_HERO.ttf', 
            'width'           => 120,
            'height'          => 40,
            'characters'      => 6,
            'theme'           => 'default',
            'font_adjustment' => 0.70
            ),
        'captchaThemes'  => array(
            'default' => array(
                'bgcolor'    => array(200,200, 200),
                'txtcolor'   => array(10, 30, 80),
                'noisecolor' => array(60, 90, 120)
                )
            )
        )
    );   

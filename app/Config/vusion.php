<?php
$config = array(  
    'vusion' => array(
        'version' => 'develop',
        'rabbitmq' => array(
            'vhost' => '/develop',
            'username' => 'vumi',
            'password' => 'vumi',
            ),
        'redis' => array(
            'host' => 'localhost',
            'port' => '6379'
            ),
        'redisPrefix' => array(
            'base' => 'vusion',
            'programs' => 'programs'
            ),
        'cacheStatsExpire' => array(
            1 => 30,       #1sec cache 30sec 
            5 => 240,      #5sec cache 4mins
            40 => 900,     #40sec cache 15mins
            90 => 3600,    #90sec cache 1h
            150 => 10800,  #150sec cache 3h
            151 => 32400  #above 150sec cache 9h
            ),
        'cacheCountExpire' => array(
            1 => 1,         #1sec cache 1sec
            5 => 60,        #5sec cache 1min
            10 => 180,      #10sec cache 3min
            30 => 1800,     #30sec cache 30min
            ),
        'domain' => 'localhost:4567',
        'email' => 'admin@vusion.texttochange.org',
        'captcha' => array(
            'settings' => array(
                'font'            => 'BIRTH_OF_A_HERO.ttf', 
                'width'           => 120,
                'height'          => 40,
                'characters'      => 6,
                'theme'           => 'default',
                'font_adjustment' => 0.70
                ),
            'themes'  => array(
                'default' => array(
                    'bgcolor'    => array(200,200, 200),
                    'txtcolor'   => array(10, 30, 80),
                    'noisecolor' => array(60, 90, 120)
                    )
                )
            ),
        'reportIssue' => array(
            'email' => 'vusion-issues@texttochange.com',
            'subjectPrefix' => '[vusion-issues]'
            ),
        'backendUser' => 'supervisordUser',
        'importMaxParticipants' => 10000,
        'countriesPrefixesFile' => 'files/countries/countries.csv',
        'mash' => array(
            'url' => 'http://192.168.50.2:3000/api/v1',
            'apiKey' => 'a9ffa708b138f37afdc041e7a1b52e7175a74a069e274fe800a62ca772c5da4bfcac426db4a1e3fe6bd6c422372cb4c597668c067eac11fa9df25dac83f4ecf3'
            ),
        )
    );   

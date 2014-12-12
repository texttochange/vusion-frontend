<?php

function echoLine($elements) 
{
    $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
    echo implode(",", $quotedElements) . "\n";
}

$headers = array(
    'object-type',
    'participant-phone',
    'participant-session-id',
    'timestamp',
    'message-status',
    'message-content',
    'message-direction',
    'dialogue-id',
    'interaction-id',
    'request-id',
    'unattach-id',
    'matching-answer');
echoLine($headers);

foreach($histories as $row)
{
    $line = array();
    foreach ($headers as $key)
    {
        if (isset($row['History'][$key])) {
            $line[] =  $row['History'][$key]."";
        } else { 
            $line[] = "";
        }
    }
    echoLine($line);
}

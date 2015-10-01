<?php
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
echo $this->Csv->arrayToLine($headers);

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
    echo $this->Csv->arrayToLine($line);
}

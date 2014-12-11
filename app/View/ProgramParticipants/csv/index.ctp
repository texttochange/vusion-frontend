<?php

function echoLine($elements) 
{
    $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
    echo implode(",", $quotedElements) . "\n";
}

$fields = array(
    'phone',
    'session-id',
    'tags',
    'profile');        
echoLine($fields);

foreach($participants as $participant)
{
    $values = array();
    foreach ($fields as $field)
    {
        if ($field == 'tags') {
            $values[] = implode(",", $participant['Participant'][$field]);
        } else if ($field == 'profile') {
            $labels = array_map(
                function($label) { return $label['label'].":".$label['value']; },
                $participant['Participant'][$field]);
            $values[] = implode(",", $labels);
        } else {
            $values[] = $participant['Participant'][$field];
        }
    }
    echoLine($values);
}
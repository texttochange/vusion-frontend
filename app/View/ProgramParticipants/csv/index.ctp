<?php

function echoLine($elements) 
{
    $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
    echo implode(",", $quotedElements) . "\n";
}

function echoOrderedLine($elements, $orderedKey)
{
    $quotedElements = array_map(function($val) { return '"'.$val.'"'; }, $elements);
    $first = true;
    foreach ($orderedKey as $key) {
        if ($first) {
            echo $quotedElements[$key];
            $first = false;
        } else {
            echo "," . $quotedElements[$key];
        }
    }
    echo "\n";
}

$fields = array(
    'phone',
    'session-id',
    'tags',
    'profile');
if (isset($explodeProfile)) {
    $displayedFields = array_merge($fields, $explodeProfile);
} else {
    $displayedFields = $fields;
}
echoLine($displayedFields);

$valuesTemplate = array_fill_keys(array_keys(array_flip($displayedFields)), "");
foreach($participants as $participant)
{
    $values = $valuesTemplate;
    foreach ($fields as $field)
    {
        if ($field == 'tags') {
            $values['tags'] = implode(",", $participant['Participant'][$field]);
        } else if ($field == 'profile') {
            $profileLabels = array();
            $explodedValues = array();
            foreach ($participant['Participant']['profile'] as $label) {
                if (isset($values[$label['label']])) {
                    $values[$label['label']] = $label['value'];
                } else {
                    $profileLabels[] = $label['label'] . ":" . $label['value'];
                }
            }
            $values['profile'] = implode(",", $profileLabels);
        } else {
            $values[$field] = $participant['Participant'][$field];
        }
    }
    echoOrderedLine($values, $displayedFields);
}

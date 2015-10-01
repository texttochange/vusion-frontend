<?php
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
echo $this->Csv->arrayToLine($displayedFields);

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
    echo $this->Csv->dictToLine($values, $displayedFields);
}

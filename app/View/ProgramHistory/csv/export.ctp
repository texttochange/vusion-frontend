<?php

    $fields = array(
        'participant-phone',
        'message-direction',
        'message-status',
        'message-content',
        'timestamp');        

    foreach ($fields as $field)
    {
        echo '"'.$field.'",';
    }
    echo "\n";

    foreach ($data as $history)
    {
        // Loop through every value in a row
        $values = array();
        foreach ($fields as $field)
        {
            if (!isset($history['History'][$field])) {
               $values[] = '""';
               continue;
            }   
            // Apply opening and closing text delimiters to every value
            if ($field == 'timestamp')
                $values[] = '"'.$this->Time->format('d/m/Y H:i:s', $history['History'][$field]).'"';
            else
                $values[] = '"'.$history['History'][$field].'"';
        }
        echo implode(",",$values)."\n";
    }
?>

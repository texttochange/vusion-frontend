<?php

    foreach ($data[0]['ParticipantsState'] as $key => &$value)
    {
        // Generating Headers 
        if($key != '_id') 
            echo "\"".$key."\",";
    }
    
    echo "\n";
    foreach ($data as $row)
    {
        // Loop through every value in a row
        foreach ($row['ParticipantsState'] as $key => &$value)
        {
            // Apply opening and closing text delimiters to every value
            $value = "\"".$value."\"";
        }
        //remove the id from the array
        $id =array_shift($row['ParticipantsState']);
        // Echo all values in a row comma separated
        echo implode(",",$row['ParticipantsState'])."\n";
    }
?>

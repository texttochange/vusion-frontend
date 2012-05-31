<?php

    foreach ($data[0]['History'] as $key => &$value)
    {
        // Generating Headers 
        if($key != '_id') 
            echo "\"".$key."\",";
    }
    
    echo "\n";
    foreach ($data as $row)
    {
        // Loop through every value in a row
        foreach ($row['History'] as $key => &$value)
        {
            // Apply opening and closing text delimiters to every value
            if ($key == 'timestamp')
                $value = "\"".$this->Time->format('d/m/Y H:i:s', $value)."\"";
            else               
                $value = "\"".$value."\"";           
        }
        //remove the id from the array
        $id =array_shift($row['History']);
        // Echo all values in a row comma separated
        echo implode(",",$row['History'])."\n";
    }
?>

<?php

    $headers = array();

    foreach ($statuses as $row)
    {
        foreach ($row['History'] as $key => $value){
            if ( !in_array($key, $headers) ) {
                array_push($headers, $key);
                    echo "\"".$key."\",";
            } 
        } 
    }
    
    echo "\n";
    foreach ($statuses as $row)
    {
        foreach ($headers as $key)
        {
            if (isset($row['History'][$key])) {
                // Apply opening and closing text delimiters to every value
                if ($key == 'timestamp')
                    echo "\"".$this->Time->format('d/m/Y H:i:s', $row['History'][$key])."\",";
                else 
                    echo "\"".$row['History'][$key]."\",";
            } else { 
                echo ",";
            }
        }
        echo "\n";
    }

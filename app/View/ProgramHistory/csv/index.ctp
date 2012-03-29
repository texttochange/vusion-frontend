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
                echo "\"".$row['History'][$key]."\",";
            } else { 
                echo ",";
            }
        }
        echo "\n";
    }

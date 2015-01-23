<?php

class TestHelper 
{


	public static function deleteAllProgramFiles($programUrl) 
    {
        $files = glob(WWW_ROOT . "files/programs/$programUrl/*"); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }
    }


}
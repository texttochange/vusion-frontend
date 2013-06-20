<?php 


class VusionValidation extends Validation {


    public static function customNot($check, $regex=null)
    {
        return !self::custom($check, $regex);
    }


}
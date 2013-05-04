<?php


abstract class VirtualModel
{
    var $data = null;
    var $fields = array();
    var $validationErrors = array();

    public function __construct() 
    {
    }

    public function set($data) 
    {
        $this->data = $data;
        $this->validationErrors = array();        
    }


    public function create()
    {
        $this->validationErrors = array();
    }


    abstract function validates();

}
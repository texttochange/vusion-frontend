<?php
App::uses('Component', 'Controller');

class PhoneNumberComponent extends Component {


    public function __construct($collection, $settings = array())
    {
        parent::__construct($collection, $settings);
    }


    public function getCountriesPrefixes() { 
        $filePath = WWW_ROOT . "files"; 
		$fileName = "countries and codes.csv";
		$importedCountries = fopen($filePath . DS . $fileName,"r");
		$countries=array();
		$count = 0;
		$options = array();
		while(!feof($importedCountries)){
		   $countries[] = fgets($importedCountries);
		   if($count > 0 && $countries[$count]){
               $countries[$count] = str_replace("\n", "", $countries[$count]);
               $explodedLine = explode(",", $countries[$count]);
               $options[trim($explodedLine[0])] = trim($explodedLine[0]);
    	   }
		   $count++;		   
		}
		return $options;
	}


}
<?php
App::uses('Component', 'Controller');
App::uses('VusionException', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('DialogueHelper', 'Lib');


class PhoneNumberComponent extends Component {


    public function startup(Controller $controller)
    {
        parent::startup($controller);
        $this->countriesByPrefixes = null;
    }
        
    
    public function getCountriesByPrefixes()
    { 
        //avoid loading multiple time
        if ($this->countriesByPrefixes != null) {
            return $this->countriesByPrefixes;
        }

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
               $options[trim($explodedLine[1])] = trim($explodedLine[0]);
           }
           $count++;           
        }
        $this->countriesByPrefixes = $options;
        return $options;
    }

    
    public function getPrefixesByCountries() 
    { 
        return array_flip($this->getCountriesByPrefixes());
    }
    
    
    public function getCountries() 
    {
        $countriesPrefixes = $this->getCountriesByPrefixes();
        return array_combine(array_values($countriesPrefixes), array_values($countriesPrefixes));  
    }

    public function fromPrefixedCodeToCountry($prefixedCode)
    {
        $countriesByPrefixes = $this->getCountriesByPrefixes();
        return DialogueHelper::fromPrefixedCodeToCountry($prefixedCode, $countriesByPrefixes);
    }



    
    
}
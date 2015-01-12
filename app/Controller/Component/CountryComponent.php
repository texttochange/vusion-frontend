<?php
App::uses('Component', 'Controller');
App::uses('VusionException', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('DialogueHelper', 'Lib');


class CountryComponent extends Component {

    var $countries = null;


    public function startup(Controller $controller)
    {
        parent::startup($controller);
        $fileName = WWW_ROOT . Configure::read('vusion.countriesPrefixesFile');
        $this->countries = $this->loadCountries($fileName);
    }


    public function loadCountries($filePath) {
        $importedCountries = fopen($filePath,"r");
        $countries=array();
        $headers = fgetcsv($importedCountries);
        $headers = array_map('trim', $headers);
        while(!feof($importedCountries)){
           $country = fgetcsv($importedCountries);
           $countries[] = array_combine($headers, array_map('trim', $country));
        }
        return $countries;
    }

    
    public function getNamesByPrefixes()
    { 
        $namesByPrefixes = array();
        foreach($this->countries as $country) {
            if ($country['Prefix'] === '') {
                continue;
            }
            $namesByPrefixes[$country['Prefix']] = $country['Name'];
        }
        return $namesByPrefixes;
    }

    
    public function getPrefixesByNames()
    { 
        return array_flip($this->getNamesByPrefixes());
    }
    

    public function getNamesByIso()
    {
        $namesByIso = array();
        foreach($this->countries as $country) {
            if ($country['Iso'] === '') {
                continue;
            }
            $namesByIso[$country['Iso']] = $country['Name'];
        }
        return $namesByIso;
    }


    public function getIsoByPrefix()
    {
        $IsoByPrefixes = array();
        foreach($this->countries as $country) {
            if ($country['Prefix'] === '') {
                continue;
            }
            $namesByPrefixes[$country['Prefix']] = $country['Iso'];
        }
        return $namesByPrefixes;
    }

    
    public function getNamesByNames()
    {
        $namesByNames = array();
        foreach($this->countries as $country) {
            if ($country['Iso'] === '') {
                continue;
            }
            $namesByNames[$country['Name']] = $country['Name'];
        }
        return $namesByNames;
    }


    public function fromPrefixedCodeToName($prefixedCode)
    {
        $namesByPrefixes = $this->getNamesByPrefixes();
        return DialogueHelper::fromPrefixedCodeToCountry($prefixedCode, $namesByPrefixes);
    }
    

    public function fromPrefixToIso($prefix)
    {
        $isoByPrefix = $this->getIsoByPrefix();
        return $isoByPrefix[$prefix];
    }

    
}
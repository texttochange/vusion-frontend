<?php
App::uses('Component', 'Controller');
App::uses('VusionException', 'Lib');
App::uses('VusionConst', 'Lib');
App::uses('DialogueHelper', 'Lib');


class CountryComponent extends Component {


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
        $fileName = WWW_ROOT . Configure::read('vusion.countriesPrefixesFile');
        $this->countriesByPrefixes = DialogueHelper::loadCountriesByPrefixes($fileName);
        return $this->countriesByPrefixes;
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
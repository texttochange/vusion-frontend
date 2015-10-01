<?php
App::uses('AppHelper', 'View/Helper');


//As temporary migration RequireJsHelper relies on JsHelper
//Final migration step would remove the usage or JsHelper
class RequireJsHelper extends AppHelper
{
	public $helpers = array(
		'Html', 
		'Js');
	protected $_variables = array();
    protected $_scripts = array();


	public function variable($key, $value) 
	{
		$this->_variables[$key] = $value;
	}


	public function script($moduleId) 
	{
		$this->_scripts[] = $moduleId;		
	}


	public function scripts($moduleIds) 
	{
		$this->_scripts = array_merge($this->_scripts, $moduleIds);
	}


	public function writeBuffer()
	{
		$scriptBlock = '
		    require(["/js/common"], function() {
		        require(["vusion"], function(vusion){
					vusion.setData('.json_encode($this->_variables).');
		               require('.json_encode($this->_scripts).', function(){
                           '. implode("\n", $this->Js->getBuffer($options['clear'])).'
                       });
        		});
			});';
		$script = $this->Html->scriptBlock($scriptBlock);
		return $script;
	}


	public function beforeLayout($layoutFile) {
		$this->Html->script('require', array('inline' => false));
	}

}

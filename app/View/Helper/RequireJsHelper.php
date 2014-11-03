<?php
App::uses('AppHelper', 'View/Helper');

class RequireJsHelper extends AppHelper
{
	public $helpers = array('Html');
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


	public function beforeLayout($layoutFile) {
		$this->Html->script('require', array('inline' => false));
		$scriptBlock = '
		    require(["/js/common"], function() {
		        require(["vusion"], function(vusion){
					vusion.setData('.json_encode($this->_variables).');
		       		require('.json_encode($this->_scripts).', function(){})
				});
			});';
		$this->Html->scriptBlock($scriptBlock, array('inline' => false));
		return $layoutFile;
	}

}

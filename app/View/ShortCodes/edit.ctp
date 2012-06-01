<div class="shortcodes form">
<h3><?php echo __('Edit ShortCode'); ?></h3>
<?php echo $this->Form->create('ShortCode');?>
	<fieldset>
		
		<div class='input text'>
	<?php
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
	
		echo $this->Html->tag('label',__('Country'));
		echo "<br />";
		echo $this->Form->select('country', $options, array('id'=> 'country'));
		$this->Js->get('#country')->event('change', '	       
		       $("#internationalprefix").val(getCountryCodes($("select option:selected").text()));
		       ');
	?>
		</div>
	<?php
		echo $this->Form->input(__('shortcode'));
		echo $this->Form->input(__('internationalprefix'),
				array('id' => 'internationalprefix',
					'label' =>'International Prefix',
					'readonly' => true)
					);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View ShortCodes'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>	
</div>
<?php echo $this->Js->writeBuffer(); ?>

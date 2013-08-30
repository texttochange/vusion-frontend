<div class="shortcodes form users-index program-body">
<h3><?php echo __('Edit ShortCode'); ?></h3>
<?php echo $this->Form->create('ShortCode');?>
	<fieldset>	
	<div class='input text'>
	<?php
	echo $this->Html->tag('label',__('Country'));
	echo "<br />";
	echo $this->Form->select('country', $countryOptions, array('id'=> 'country'));
	$this->Js->get('#country')->event('change', '	       
	    $("#international-prefix").val(getCountryCodes($("#country option:selected").text()));
	    ');
	?>
	</div>
	<?php
	echo $this->Form->input('shortcode', array('label' => __('Shortcode')));
	echo $this->Form->input('international-prefix',
	    array('id' => 'international-prefix',
	        'label' => __('International Prefix'),
	        'readonly' => true)
	    );
	?>
	<div>
	<?php
	echo $this->Html->tag('label',__('Error Template'));
	echo "<br />";
	echo $this->Form->select(
	    'error-template', 
	    $errorTemplateOptions,
	    array(
	        'id' => 'error-template',
	        'empty'=> __('Choose one...'))
	    );
	?>
	</div>
	<div>
	<?php
	echo $this->Form->checkbox('support-customized-id');
	echo $this->Html->tag('label',__('Support Customized Id'));
	?>
	</div>
	<div>
	<?php
	echo $this->Form->checkbox('supported-internationally');
	echo $this->Html->tag('label', __('Supported Internationally'));
	?>
	</div>
	<?php
	$maxCharacterPerSMSClass = null;
	if ($this->Form->isFieldError('max-character-per-sms')) {
	    $maxCharacterPerSMSClass = "error";
	}
	echo "<div class=\"input required $maxCharacterPerSMSClass\">";
	echo $this->Html->tag('label', __('Maximun number of character per SMS'));
	echo "<br />";
	echo $this->Form->select(
	    'max-character-per-sms',
	    $maxCharacterPerSmsOptions,
	    array('empty' => __('Choose one...')));
	if ($this->Form->isFieldError('max-character-per-sms'))
	    echo $this->Form->error('max-character-per-sms');
	echo "</div>";
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('View ShortCodes'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>	
</div>
</div>
<?php echo $this->Js->writeBuffer(); ?>

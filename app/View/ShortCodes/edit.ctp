<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	    <li>
	    <?php
	    echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->params['id']), null, __('Are you sure you want to delete the shortcode "%s"?', $this->params['data']['ShortCode']['shortcode'])); 
	     ?>
	    </li>
		<li><?php echo $this->Html->link(__('ShortCodes List'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
	</ul>	
</div>
</div>

<div class="shortcodes form admin-index">
<div class="table">
<div class="row">
<div class="cell">
<h3><?php echo __('Edit ShortCode'); ?></h3>
<?php echo $this->Form->create('ShortCode',  array('type' => 'post'));?>
	<fieldset>	
	
	<?php
	echo $this->Form->input('country',
	    array('id'=> 'country',
	        'type' => 'text',
	        'readonly' => 'true',
	        'class' => 'readonly-field'));
	echo $this->Form->input('shortcode',
	    array('label' => __('Shortcode'),
	        'readonly' => 'true',
	        'class' => 'readonly-field'));
	echo $this->Form->input('international-prefix',
	    array('id' => 'international-prefix',
	        'label' => __('International Prefix'),
	        'readonly' => true,
	        'class' => 'readonly-field')
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
</div>
</div>
</div>

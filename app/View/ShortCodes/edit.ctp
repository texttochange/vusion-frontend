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
		echo $this->Form->input(__('shortcode'));
		echo $this->Form->input(__('international-prefix'),
				array('id' => 'international-prefix',
					'label' =>'International Prefix',
					'readonly' => true)
					);
	?>
	<div>
	<?php
	    echo $this->Html->tag('label',__('Error Template'));
		echo "<br />";
	    echo $this->Form->select('error-template', $errorTemplateOptions,
	        array('id' => 'error-template',
	            'empty'=> __('Template...')
	            )
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
	    echo $this->Html->tag('label',__('Supported Internationally'));
	?>
	</div>
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

<div class="programsettings form">
<?php echo $this->Form->create('ProgramSettings'); ?>
    <fieldset>
        <legend><?php echo __('Edit Program Settings'); ?></legend>
        <?php
            echo $this->Form->input('country');
        ?>
        <div class='input text'>
        <?php
            echo $this->Html->tag('label',__('Timezone'));
            $timezone_identifiers = DateTimeZone::listIdentifiers();
            $timezone_options = array();
            foreach($timezone_identifiers as $timezone_identifier) {
            $timezone_options[$timezone_identifier] = $timezone_identifier; 
            }
            echo $this->Form->select('timezone', $timezone_options);
            //echo $this->Form->select('timezone', $timezone_identifiers, array('value'=>'412'));
        ?>
        </div>
    </fieldset>
<?php echo $this->Form->end(__('Save'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	    <li><?php echo $this->Html->link(__('Back Homepage'), array('program'=>$programUrl,'controller'=>'home')); ?></li>
	</ul>
</div>

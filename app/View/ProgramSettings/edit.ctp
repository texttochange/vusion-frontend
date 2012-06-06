<div class="programsettings form">
    <ul class="ttc-actions">		
        <li><?php echo $this->Html->tag('div', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
        <?php $this->Js->get('#button-save')->event('click', '$("#ProgramSettingsEditForm").submit()' , true);?>
	</ul>
<H3><?php echo __('Edit Program Settings'); ?></H3>
<?php echo $this->Form->create('ProgramSettings'); ?>
    <fieldset>      
        
        <div class='input text'>
        <?php
            echo $this->Html->tag('label',__('Shortcode'));    
            $shortcode_options = array();
            foreach($shortcodes as $shortcode) {
                $shortcode_options[$shortcode['ShortCode']['shortcode']] = trim($shortcode['ShortCode']['country'])." - ".$shortcode['ShortCode']['shortcode'];
            }
            echo "<br />";
            echo $this->Form->select('shortcode', $shortcode_options, array('id' => 'shortcode'));
            $this->Js->get('#shortcode')->event('change','
            			var countryShortcode = $("#shortcode option:selected").text();
            			var countryname = countryShortcode.slice(0, countryShortcode.lastIndexOf("-")-1);            			
            			$("#international-prefix").val(getCountryCodes(countryname));
            			');
        ?>
        </div>
        <?php
            echo $this->Form->input(__('international-prefix'),
            		array('id' => 'international-prefix',
            		      'label' => 'International Prefix',
            		      'readonly' => true)
            		);
        ?>
        <div class='input text'>
        <?php
            echo $this->Html->tag('label',__('Timezone'));
            $timezone_identifiers = DateTimeZone::listIdentifiers();
            $timezone_options = array();
            foreach($timezone_identifiers as $timezone_identifier) {
            $timezone_options[$timezone_identifier] = $timezone_identifier; 
            }
            echo "<br />";
            echo $this->Form->select('timezone', $timezone_options);
            //echo $this->Form->select('timezone', $timezone_identifiers, array('value'=>'412'));
        ?>
        </div>
        <div>
        <?php 
            echo $this->Form->label(__('Default template for open questions'));
            echo "<br>";
            echo $this->Form->select('default-template-open-question', $openQuestionTemplateOptions, array(
                'empty'=> __('Template...')));
       ?>
        </div>
                <div>
        <?php 
            echo $this->Form->label(__('Default template for closed questions'));
            echo "<br>";
            echo $this->Form->select('default-template-closed-question', $closedQuestionTemplateOptions, array(
                'empty'=> __('Template...')));
       ?>
        </div>
    </fieldset>
<?php echo $this->Form->end(__('Save'));?>
</div>
<?php echo $this->Js->writeBuffer(); ?>

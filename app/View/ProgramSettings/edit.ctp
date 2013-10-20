<div class="programsettings form width-size">
    <ul class="ttc-actions">		
        <li>
        <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save', 'onclick' => 'document.forms[0].submit()')); ?>
        <span class="actions">
        <?php
        echo $this->Html->link( __('Cancel'), 
            array(
                'program' => $programDetails['url'],
                'controller' => 'programHome',
                'action' => 'index'	           
                ));
        ?>
        </span>
        </li>
        <?php $this->Js->get('#button-save')->event('click', '$("#ProgramSettingsEditForm").submit()' , true);?>
	</ul>
<H3><?php echo __('Edit Program Settings'); ?></H3>
  <div class="ttc-display-area display-height-size">
  <?php 
    ## Hack to manully set the validationErrors due to our bad model key/value
    if (isset($validationErrorsArray)) {
        $this->Form->validationErrors['ProgramSetting'] = $validationErrorsArray;
    }
    echo $this->Form->create('ProgramSetting'); ?>
    <fieldset>
        <div class='input text'>
        <?php
            echo $this->Html->tag('label',__('Shortcode'));    
            foreach($shortcodes as $shortcode) {
                if ($shortcode['ShortCode']['supported-internationally']==0) {
                    $countyShortCode = trim($shortcode['ShortCode']['country'])."-".$shortcode['ShortCode']['shortcode'];
                    $prefixShortCode = $shortcode['ShortCode']['international-prefix']."-".$shortcode['ShortCode']['shortcode'];
                } else {
                    $countyShortCode = $shortcode['ShortCode']['shortcode'];
                    $prefixShortCode = $shortcode['ShortCode']['shortcode'];     
                }
                $shortcodeOptions[$prefixShortCode] = $countyShortCode;
                $shortcodeCompact[$prefixShortCode] = $shortcode['ShortCode'];
            }
            echo "<br />";
            echo $this->Form->select('shortcode', $shortcodeOptions, array('id' => 'shortcode'));
            //pack the shortcodes info to be easy to read in JS
            $this->Js->set('shortcodes', $shortcodeCompact);
            $this->Js->get('#shortcode')->event('change','
            			var countryShortcode = $("#shortcode option:selected").val();
            			var countryInternationalPrefix = countryShortcode.slice(0, countryShortcode.lastIndexOf("-"));
                        var prefixShortcode = $("#shortcode").val();	            			
            			if (window.app.shortcodes[prefixShortcode]["supported-internationally"]==0) {
                            $("#international-prefix").val(countryInternationalPrefix);
                        } else {
                            $("#international-prefix").val("all");
                        }
            			if (window.app.shortcodes[prefixShortcode]["support-customized-id"]==1) {
            			    $("#customized-id").prop("disabled", false);
            			} else {
            			    $("#customized-id").prop("disabled", true);
            			    $("#customized-id").val("");
            			}
            			');
        ?>
        </div>
        <?php
            echo $this->Form->input('international-prefix',
            		array('id' => 'international-prefix',
            		      'label' => __('Supported International Prefix(es)'),
            		      'readonly' => 'true',
            		      'style' => 'color:#AAAAAA')
            		);
        ?>
        <div>
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
        </div><div>
        <?php 
            echo $this->Form->label(__('Default template for open questions'));
            echo "<br>";
            echo $this->Form->select('default-template-open-question', $openQuestionTemplateOptions, array(
                'empty'=> __('Template...')));
       ?>
        </div><div>
        <?php 
            echo $this->Form->label(__('Default template for closed questions'));
            echo "<br>";
            echo $this->Form->select('default-template-closed-question', $closedQuestionTemplateOptions, array(
                'empty'=> __('Template...')));
        ?>
        </div><div>
        <?php
	        echo $this->Html->tag('label',__('Default template for unmatching answers'));
	        echo "<br />";
	        echo $this->Form->select('default-template-unmatching-answer', $unmatchingAnswerTemplateOptions,
	            array('empty'=> __('Template...')));
	    ?>
	    </div>
	    <?php
	        if (isset($this->data["ProgramSetting"]["shortcode"])) {
	            $customizedIdDisabled = $shortcodeCompact[$this->data["ProgramSetting"]["shortcode"]]["support-customized-id"] ? false : true;
	        } else {
    	        $customizedIdDisabled = true;
    	    }
            echo $this->Form->input('customized-id',
            		array('id' => 'customized-id',
            		      'label' => __('Customized Id'),
            		      'disabled' => $customizedIdDisabled)
            		);
        ?>
        <div>
        <?php
            echo $this->Form->checkbox('request-and-feedback-prioritized', array('checked' => true));
            echo $this->Html->tag('label',__('Prioritize request responses and feedback messages.'));
        ?>
        </div>
        <div>
        <?php
            echo $this->Form->checkbox('unmatching-answer-remove-reminder');
            echo $this->Html->tag('label',__('Unmacthing answer remove reminders.'));
        ?>
        </div>
        <?php
            echo $this->Form->input(
                'double-matching-answer-feedback', 
                array(
                    'rows' => 3,
                    'label' => __('Double matching answer feedback')));
            echo $this->Form->input(
                'double-optin-error-feedback', 
                array(
                    'rows' => 3,
                    'label' => __('Double optin error feedback')));
            echo $this->Html->tag('div', __('Set SMS Limit'), array('style'=>'margin-bottom:0px'));
            $options = array(
                'none' => __('No limit'),
                'outgoing-only' => __('Count only outgoing'),
                'outgoing-incoming' => __('Count outgoing and incoming'));
            $attributes = array(
                'legend' => false,
                'style' => 'margin-left:5px',
                );
            echo "<div>";
            echo $this->Form->radio(
                'credit-type',
                $options,
                $attributes);
            $displaySmsLimitDetails = 'display:none';
            $disableSmsLimitDetails = true;      
            if (isset($this->Form->data['ProgramSetting']['credit-type'])) {
                if (in_array($this->Form->data['ProgramSetting']['credit-type'], array('outgoing-only','outgoing-incoming'))) {
                    $displaySmsLimitDetails  = '';
                    $disableSmsLimitDetails = false;
                }
            }
            echo "<fieldset id='credit-details' style='$displaySmsLimitDetails'>";
            echo $this->Form->input('credit-number', array(
                'label' => __('Total limit'),
                'disabled' => $disableSmsLimitDetails));
            echo $this->Form->input('credit-from-date', array(
                'label' => __('Limit Count From'),
                'disabled' => $disableSmsLimitDetails));
            echo $this->Form->input('credit-to-date', array(
                'label' => __('Limit Count To'),
                'disabled' => $disableSmsLimitDetails));
            $this->Js->get("[name*='credit-type'][type='radio']")->event(
                'change',
                "if ($(\"[name*='credit-type'][type='radio']:checked\").val() == 'none') {
                    $(\"[name*='credit-']:not([type='radio'])\").val(null).prop('disabled', true);
                    $('#credit-details').hide();
                } else { 
                    $(\"[name*='credit-']:not([type='radio'])\").prop('disabled', false);
                    $('#credit-details').show();
                }");
             $this->Js->get('document')->event(
                 'ready',
                 '$("[name*=\'credit-from-date\']").datepicker();
                 $("[name*=\'credit-to-date\']").datepicker();'
                 );
            echo '</div>';
        ?>
        <div>
        <?php
            echo $this->Form->checkbox(
                'sms-forwarding-allowed',
                array(
                    'value' => 'full',
                    'hiddenField' => 'none'));
            echo $this->Html->tag('label',__('Allow SMS Forwarding.'));
        ?>
        </div>
        </div>
    </fieldset>
  <?php echo $this->Form->end(__('Save'));?>
  </div>
</div>
<?php echo $this->Js->writeBuffer(); ?>

<div class="programsettings form width-size">
<H3><?php echo __('View Program Settings'); ?></H3>
    <dl>
        <?php echo $this->Html->tag('dt',__('Shortcode')); ?>
        <dd><b><?php
            echo (isset($programSettings['shortcode'])) ? $programSettings['shortcode'] : '&nbsp;';
        ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('International prefix'));  ?>
        <dd><b><?php
            echo (isset($programSettings['international-prefix'])) ? $programSettings['international-prefix'] : '&nbsp;';
        ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Timezone'));?>
        <dd><b><?php
            echo (isset($programSettings['timezone'])) ? $programSettings['timezone'] : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Default template for open questions'));?>
        <dd><b><?php
            echo (isset($programSettings['default-template-open-question'])) ? $programSettings['default-template-open-question'] : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Default template for closed questions')); ?>
        <dd><b><?php 
            echo (isset($programSettings['default-template-closed-question'])) ?  $programSettings['default-template-closed-question'] : '&nbsp;';                       
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Default template for unmatching answers')); ?>
        <dd><b><?php 
            echo (isset($programSettings['default-template-unmatching-answer'])) ? $programSettings['default-template-unmatching-answer'] : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Customized Id'));?>
        <dd><b><?php
            echo (isset($programSettings['customized-id'])) ? $programSettings['customized-id'] : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Prioritize request responses and feedback messages.'));?>
        <dd><b><?php
             echo (isset($programSettings['request-and-feedback-prioritized'])) ? __('Yes') : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Unmacthing answer remove reminders.'));?>
        <dd><b><?php
            echo (isset($programSettings['unmatching-answer-remove-reminder'])) ? __('Yes') : '&nbsp';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Double matching answer feedback'));?>
        <dd><b><?php
            echo (isset($programSettings['double-matching-answer-feedback'])) ? $programSettings['double-matching-answer-feedback'] : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Double optin error feedback'));?>
        <dd><b><?php
            echo (isset($programSettings['double-optin-error-feedback'])) ? $programSettings['double-optin-error-feedback'] : '&nbsp;';
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Set SMS lmit'));?>
        <dd><b><?php
            if (!isset($programSettings['credit-type'])) {
                echo '&nbsp';
            } else if ($programSettings['credit-type'] == 'none') {
                echo __('None');
            } else {
                echo __("%s maximum %s from %s to %s", 
                    $programSettings['credit-type'],
                    $programSettings['credit-number'], 
                    $this->Time->format('d/m/Y', $programSettings['credit-from-date']),
                    $this->Time->format('d/m/Y', $programSettings['credit-to-date']));                
            }            
            ?>
        </b></dd>
        <?php echo $this->Html->tag('dt',__('Allow SMS Forwarding'));?>
        <dd><b><?php
            echo (isset($programSettings['sms-forwarding-allowed'])) ? __('Yes') : '&nbsp;';
            ?>
        </b></dd> 
    <dl>        
</div>
<?php echo $this->Js->writeBuffer(); ?>

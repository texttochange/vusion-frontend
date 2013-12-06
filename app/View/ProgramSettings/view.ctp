<div class="programsettings form width-size">
<H3><?php echo __('View Program Settings'); ?></H3>
    <dl>
        <?php echo $this->Html->tag('dt',__('Shortcode')); ?>
        <dd><?php
            echo (isset($programSettings['shortcode'])) ? $programSettings['shortcode'] : '&nbsp;';
        ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('International prefix'));  ?>
        <dd><?php
            echo (isset($programSettings['international-prefix'])) ? $programSettings['international-prefix'] : '&nbsp;';
        ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Timezone'));?>
        <dd><?php
            echo (isset($programSettings['timezone'])) ? $programSettings['timezone'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Default template for open questions'));?>
        <dd><?php
            echo (isset($programSettings['default-template-open-question'])) ? $programSettings['default-template-open-question'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Default template for closed questions')); ?>
        <dd><?php 
            echo (isset($programSettings['default-template-closed-question'])) ?  $programSettings['default-template-closed-question'] : '&nbsp;';                       
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Default template for unmatching answers')); ?>
        <dd><?php 
            echo (isset($programSettings['default-template-unmatching-answer'])) ? $programSettings['default-template-unmatching-answer'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Customized Id'));?>
        <dd><?php
            echo (isset($programSettings['customized-id'])) ? $programSettings['customized-id'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Prioritize request responses and feedback messages.'));?>
        <dd><?php
             echo (isset($programSettings['request-and-feedback-prioritized'])) ? $programSettings['request-and-feedback-prioritized'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Unmacthing answer remove reminders.'));?>
        <dd><?php
            echo (isset($programSettings['unmatching-answer-remove-reminder'])) ? $programSettings['unmatching-answer-remove-reminder'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Double matching answer feedback'));?>
        <dd><?php
            echo (isset($programSettings['double-matching-answer-feedback'])) ? $programSettings['double-matching-answer-feedback'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Double optin error feedback'));?>
        <dd><?php
            echo (isset($programSettings['double-optin-error-feedback'])) ? $programSettings['double-optin-error-feedback'] : '&nbsp;';
            ?>
        </dd>
        <?php echo $this->Html->tag('dt',__('Set SMS Dlmit'));?>
        <dd><?php
            echo (isset($programSettings['credit-type'])) ? $programSettings['credit-type'] : '&nbsp;';
            ?>
        </dd>
    <dl>        
</div>
<?php echo $this->Js->writeBuffer(); ?>

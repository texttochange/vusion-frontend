<div class="programsettings form width-size">
<H3><?php echo __('View Program Settings'); ?></H3>
<dl>
        <?php
            echo $this->Html->tag('dt',__('Shortcode'));
            echo $this->Html->tag('dd', $programSettings['shortcode']);
            echo $this->Html->tag('dt',__('International prefix'));
            echo $this->Html->tag('dd', $programSettings['international-prefix']);
            echo $this->Html->tag('dt',__('Timezone'));
            echo $this->Html->tag('dd', $programSettings['timezone']);
            echo $this->Html->tag('dt',__('Default template for open questions'));
            echo $this->Html->tag('dd', $programSettings['default-template-open-question']);
            echo $this->Html->tag('dt',__('Default template for closed questions'));
            echo $this->Html->tag('dd', $programSettings['default-template-closed-question']);            
        ?>
</dl>      
</div>
<?php echo $this->Js->writeBuffer(); ?>

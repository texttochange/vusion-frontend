<div class="programsettings form">
<H3><?php echo __('View Program Settings'); ?></H3>
<dl>
        <?php
            foreach ($programSettings as $programSetting) { 
                echo $this->Html->tag('dt',__(ucfirst($programSetting['ProgramSetting']['key'])));
                echo $this->Html->tag('dd',__($programSetting['ProgramSetting']['value']));
            }
        ?>
</dl>      
</div>
<?php echo $this->Js->writeBuffer(); ?>


<div class="request form width-size"  >
    <ul class="ttc-actions">
        <li>
        <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button dynamic-form-save')); ?>
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
        <?php 
        $this->Js->get('.dynamic-form-save')->event('click',
		    'formSubmit()' , true); 
		?>
    </ul>
    <h3><?php echo __('Add Request'); ?></h3>
    <div class="ttc-display-area display-height-size">
	    <?php 
	    echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form'));
	    echo "</form>";
	    $this->Js->get("#dynamic-generic-program-form");
	    $this->Js->each('$(this).buildTtcForm("Request", null, "javascript:saveFormOnServer()")', true);
        $this->DynamicOptions->setOptions(
            $currentProgramData, $conditionalActionOptions, $contentVariableTableOptions);
        $this->Js->get('document')->event('ready','addCounter(); ');
	    ?>
	    <br/>
	    <?php  echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button dynamic-form-save')); ?>
	</div>
</div>

<div class="request form">
    <ul class="ttc-actions">
        <li><?php echo $this->Html->tag('div', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
        <?php $this->Js->get('#button-save')->event('click', '
		    disableSaveButtons();
		    $("#dynamic-generic-program-form")[0].submit()' , true);?>
		<?php $this->Js->get('#dynamic-generic-program-form')->event('submit','
		    disableSaveButtons();
		    $("#dynamic-generic-program-form")[0].submit();'); ?>
    </ul>
    <h3><?php echo __('Add Request'); ?></h3>
    <div class="ttc-display-area">
	    <?php 
	    echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form'));
	    $this->Js->get("#dynamic-generic-program-form");
	    $this->Js->each('$(this).buildTtcForm("Request", null, "javascript:saveRequestOnServer()")', true);
	    $dialogueOptions = array();
        foreach($dialogues as $dialogue) {
            if ($dialogue['Active']) {
                $dialogueOptions[] = array(
                    'value' => $dialogue['Active']['dialogue-id'],
                    'html' => $dialogue['Active']['name']
                    );
            }
        }
        $this->Js->set('enrollOptions', $dialogueOptions);
	    ?>
	</div>
</div>

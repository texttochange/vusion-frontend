
<div class="request form width-size"  >
    <ul class="ttc-actions">
        <li>
        <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
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
        <?php $this->Js->get('#button-save')->event('click', '
		    disableSaveButtons();
		    $("#dynamic-generic-program-form").submit()' , true);?>
		<?php $this->Js->get('#dynamic-generic-program-form')->event('submit','
		    disableSaveButtons();'); ?>
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
         $this->Js->get('document')->event('ready','
                addCounter(); ');
	    ?>
	</div>
</div>
<?php echo $this->Js->writeBuffer(); ?>

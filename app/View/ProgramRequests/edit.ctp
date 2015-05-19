<div class="request form width-size">
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
    <h3><?php echo __('Edit Request'); ?></h3>
    <div class="ttc-display-area display-height-size">
	<?php 
        echo $this->Html->tag('form', null, array(' id'=> 'dynamic-generic-program-form'));
        echo "</form>";
        $this->Js->get("#dynamic-generic-program-form");
        $this->Js->each('$(this).buildTtcForm("Request", '.$this->Js->object($request['Request']).', "javascript:saveFormOnServer()")', true);
        /*$dialogueOptions = array();
        foreach($currentProgramData['dialogues'] as $dialogue) {
            if ($dialogue['Active']) {
                $dialogueOptions[] = array(
                    'value' => $dialogue['Active']['dialogue-id'],
                    'html' => $dialogue['Active']['name']
                    );
            }
        }
        $this->Js->set('enrollOptions', $dialogueOptions);
        $this->Js->set('subcondition-fieldOptions', $conditionalActionOptions);
        $attachedTableOptions = array();
        $contentVariableTableSummaryOptions = array();
        foreach($contentVariableTableOptions as $cvt) {
            $attachedTableOptions[] = array(
                'value' => $cvt['ContentVariableTable']['_id']."",
                'html' => $cvt['ContentVariableTable']['name']);
            $rowHeaders = array();
            foreach ($cvt['ContentVariableTable']['columns'] as $column) {
                if ($column['type'] == 'key') {
                    $rowHeaders[] = $column['header'];
                } else {
                    continue;
                }
            }
            $contentVariableTableSummaryOptions[$cvt['ContentVariableTable']['_id'].""] = $rowHeaders;
        }
        $this->Js->set('scvt-attached-tableOptions', $attachedTableOptions);
        $this->Js->set('contentVariableTableSummaryOptions', $contentVariableTableSummaryOptions);*/
        $this->DynamicOptions->setOptions(
            $currentProgramData, $conditionalActionOptions, $contentVariableTableOptions);
        $this->Js->get('document')->event('ready','addCounter(); ');
	?>
	<br/>
	<?php  echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button dynamic-form-save')); ?>
	</div>
</div>


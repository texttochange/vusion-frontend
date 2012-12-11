<div class="unattached_messages form">
<ul class="ttc-actions">		
    <li><?php echo $this->Html->tag('div', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
    <?php $this->Js->get('#button-save')->event('click', '$("#UnattachedMessageAddForm").submit()' , true);?>
</ul>
<h3><?php echo __('Add Separate Message'); ?></h3>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('UnattachedMessage');?>
	<fieldset>	
	<?php 
        echo $this->Form->input(__('name'), array('id' => 'name'));
        $otions = array();
        $options = array(
            'all-participants' => __("All participants"),
            'geek' => 'geek',
            'cool' => 'cool',
            'gender:female' => 'gender:female',
            'gender:male' => 'gender:male',
            'city:kampala' => 'city:kampala');
        $error = "";
        $errorSchedule = "";
        if ($this->Form->isFieldError('to')) 
            $error = "error";            
        echo "<div class='input-text required ".$error."'>";
        echo $this->Html->tag('label',__('Send To'));
        echo "<br />";
		echo $this->Form->select('to', $options, array('multiple'=>true, 'style'=>'margin-bottom:0px'));
		if ($this->Form->isFieldError('to'))
		    echo $this->Form->error('to');
		echo "</div>";
		echo $this->Form->input(__('content'), array('rows'=>5));
		$options = array('immediately'=>'Immediately', 'fixed-time'=>'Fixed Time');
		$attributes = array('separator'=>'&nbsp;&nbsp;', 'legend'=>false);
		if ($this->Form->isFieldError('type-schedule')) 
            $errorSchedule = "error";            
        echo "<div class='input-text required ".$errorSchedule."'>";
		echo $this->Html->tag('label',__('Schedule'));
        echo "<br />";
		echo $this->Form->radio('type-schedule', $options, $attributes);
		if ($this->Form->isFieldError('type-schedule'))
		    echo $this->Form->error('type-schedule');
		echo $this->Form->input(__('fixed-time'), array('id'=>'fixed-time', 'label'=>false));
		echo "</div>";
		$this->Js->get('document')->event('ready','$("#fixed-time").datetimepicker();
		                                           addContentFormHelp();
		                                           $("input").change();
		                                           $("#UnattachedMessageTo").chosen();');
		$this->Js->get('input')->event('change','
		    if ($("input:checked").val() == "fixed-time") {
		        $("#fixed-time").attr("disabled",false);
            } else {
                $("#fixed-time").attr("disabled","disabled");
		        $("#fixed-time").val("");
            }
		    ');
	?>
	</fieldset>
	<?php echo $this->Form->end(__('Save'));?>
	</div>
</div>
<?php echo $this->Js->writeBuffer(); ?>

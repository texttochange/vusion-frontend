<div class="unattached_messages form">
<ul class="ttc-actions">		
    <li><?php echo $this->Html->tag('div', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?></li>
    <?php $this->Js->get('#button-save')->event('click', '$("#UnattachedMessageEditForm").submit()' , true);?>
</ul>
<h3><?php echo __('Edit Separate Message'); ?></h3>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('UnattachedMessage');?>
	<fieldset>
	
	<?php
        $options = array();
        $options['all participants'] = "all participants";
        echo $this->Form->input(__('name'), array('id' => 'name'));
        $error = "";
        $errorSchedule = "";
        if ($this->Form->isFieldError('to')) 
            $error = "error";            
        echo "<div class='input-text required ".$error."'>";
        echo $this->Html->tag('label',__('Send To'));
        echo "<br />";
		echo $this->Form->select('to', $options, array('empty'=>'....'));
		if ($this->Form->isFieldError('to'))
		    echo $this->Form->error('to');
		echo "</div>";
		echo $this->Form->input(__('content'), array('rows'=>5));
		$options = array('immediately'=>'Immediately', 'fixed-time'=>'Fixed Time');
		$attributes = array('separator'=>'&nbsp;&nbsp;', 'legend'=>false);
		if ($this->Form->isFieldError('schedule')) 
            $errorSchedule = "error";            
        echo "<div class='input-text required ".$errorSchedule."'>";
		echo $this->Html->tag('label',__('Schedule'));
        echo "<br />";
		echo $this->Form->radio('schedule', $options, $attributes);
		if ($this->Form->isFieldError('schedule'))
		    echo $this->Form->error('schedule');
		echo "</div>";
		if ($this->data['UnattachedMessage']['fixed-time'] != "")
		    $fixedTime = $this->Time->format('d/m/Y H:i', $this->data['UnattachedMessage']['fixed-time']);
		else
		    $fixedTime = "";
		echo $this->Form->input(__('fixed-time'), array('id'=>'fixed-time',
		    'label'=>false,
		    'value'=>$fixedTime));
		$this->Js->get('document')->event('ready','$("#fixed-time").datetimepicker();
		                                           addContentFormHelp("http://'.env("HTTP_HOST").'");
		                                           $("input").change()');
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

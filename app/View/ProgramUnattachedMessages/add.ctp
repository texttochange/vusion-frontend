<div class="unattached_messages form width-size">
<ul class="ttc-actions">		
    <li>
    <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
    <span class="actions">
    <?php
    echo $this->Html->link(__('Cancel'), 
        array(
            'program' => $programDetails['url'],
            'controller' => 'programHome',
            'action' => 'index'	           
            ));
    ?>
    </span>
    </li>
    <?php $this->Js->get('#button-save')->event('click', '$("#UnattachedMessageAddForm").submit()' , true);?>
</ul>
<h3><?php echo __('Add Separate Message'); ?></h3>
    <div class="ttc-display-area display-height-size">
    <?php 
    $sendToOptions = array(
        'all' => __('All participants'), 
        'match' => __('Participant matching'));
    $sendToMatchOperator = array(
        'all' => __('all'), 
        'any' => __('any'));
    $sendToMatchConditions = (isset($selectors) ? $selectors: array());
    $errorSendTo = "";
    $errorSchedule = "";
    $scheduleOptions = array(
    	'draft'=> __('Draft'),
        'immediately'=>__('Immediately'),
        'fixed-time'=> __('Fixed Time:'));
    $matchSelectDisabled = true;
    $fixedTimeSelectDisabled = true;
    $fileFieldDisabled = true;
    $messageSelectDisabled = true;
       
    ## Form starts
    echo $this->Form->create('UnattachedMessage', array('type' => 'file'));
    ## Name
    echo $this->Form->input('name', array('id' => 'name'));
    ## SentTo 
    if ($this->Form->isFieldError('send-to-type') || 
        $this->Form->isFieldError('send-to-match-operator') || 
        $this->Form->isFieldError('send-to-match-conditions') ||
        $this->Form->isFieldError('send-to-phone')) { 
            $errorSendTo = "error";       
    }
    echo "<div class=\"input-text required ".$errorSendTo."\">";
    echo $this->Html->tag('label',__('Send To'), array('class' => 'required'));
    echo "<br/>";
    echo $this->Form->radio(
        'send-to-type',
        $sendToOptions,
        array('separator' => '<br/>', 'legend' => false, 'class' => 'sublabel no-after', 'hiddenField' => false));
    if (isset($this->Form->data['UnattachedMessage']['send-to-type']) &&
        $this->Form->data['UnattachedMessage']['send-to-type'] == 'match') {
        $matchSelectDisabled = false;
    }
    echo $this->Form->select(
        'send-to-match-operator',
        $sendToMatchOperator,
        array(
            'disabled' => $matchSelectDisabled, 
            'style'=>'margin-bottom:0px; margin-left:3px; margin-right: 3px', 
            'empty' => false));
    echo $this->Html->tag('label',__('of the following tag(s)/label(s):'));
    echo "<div class='subinput'>";
    echo $this->Form->select(
        'send-to-match-conditions', 
        $sendToMatchConditions, 
        array(
            'disabled' => $matchSelectDisabled,
            'multiple'=>true,
            'error' => false,
            'div' => false,
            'data-placeholder' => __('Choose from available tag(s)/label(s)...')));
    echo "</div>";
    if ($this->Form->isFieldError('send-to-match-operator'))
        echo $this->Form->error('send-to-match-operator');
    if ($this->Form->isFieldError('send-to-match-conditions'))
        echo $this->Form->error('send-to-match-conditions');
    echo $this->Form->radio(
        'send-to-type',
        array('phone' => __('List of participant(s)')), 
        array('hiddenField' => false));
    echo "<div class='subinput'>";
    if (isset($this->Form->data['UnattachedMessage']['send-to-type']) &&
        $this->Form->data['UnattachedMessage']['send-to-type'] == 'phone') {
        $fileFieldDisabled = false;
    }
    echo "<span class='input file'>";
    echo $this->Form->input(
        'file',
        array('type' => 'file',
            'disabled' => $fileFieldDisabled,
            'label' => false,
            'div' => false));
    echo "</span>";
    if ($this->Form->isFieldError('send-to-phone'))
        echo $this->Form->error('send-to-phone');
    echo "</div>";
    if ($this->Form->isFieldError('send-to-type'))
        echo $this->Form->error('send-to-type');
    echo "</div>";
    ## Content
    $this->Js->set('predefinedMessageOptions', $predefinedMessageOptions);
    $options = array();
    foreach ($predefinedMessageOptions as $key => $value) {
        $options[$value['id']] = $value['name'];
    }
    echo "&nbsp&nbsp";
    if (count($predefinedMessageOptions) <= 0) {
        echo $this->Html->tag('label',__('There are no predefined messages. '));
        echo $this->Html->link(__('To create a predefined message click here.'),
            array('program'=>$programDetails['url'], 'controller' => 'programPredefinedMessages', 'action' => 'add'),
            array('class'=>'ttc-link')
            );
    } else {
        echo $this->Html->tag('label',__('Use predefined message from list:'));
        echo "&nbsp";
        echo $this->Form->select('predefined-message',
            $options,
            array('id' => 'predefined-message', 'empty' => 'Select one...')
            );
        $this->Js->get('#predefined-message')->event('change','addPredefinedContent();');
    }
    echo $this->Form->input('content', array('id'=>'unattached-content', 'rows'=>5));
    if ($this->Form->isFieldError('type-schedule') || 
        $this->Form->isFieldError('fixed-time')) { 
        $errorSchedule = "error";
    }
    ## Schedule
    echo "<div class='input-text required ".$errorSchedule."'>";
    echo $this->Html->tag('label',__('Schedule'), array('class' => 'required'));
    echo "<br />";
    echo $this->Form->radio('type-schedule', $scheduleOptions, array('separator'=>'<br/>', 'legend'=>false));
    if (isset($this->Form->data['UnattachedMessage']['type-schedule']) &&
        $this->Form->data['UnattachedMessage']['type-schedule'] == 'fixed-time') {
        $fixedTimeSelectDisabled = false;
    }
    echo "<div class='subinput'>";
    echo $this->Form->input('fixed-time', 
        array(
            'id'=>'fixed-time', 
            'label'=>false, 
            'disabled' => $fixedTimeSelectDisabled, 
            'error' => false,
            'div' => false,
            'placeholder' => "Choose a fixed time..."));
    echo "</div>";
    if ($this->Form->isFieldError('type-schedule')) {
        echo $this->Form->error('type-schedule');
    } elseif ($this->Form->isFieldError('fixed-time')) {
        echo $this->Form->error('fixed-time');
    } 
    echo "</div>";
    $this->Js->get('document')->event('ready','
        $("#fixed-time").datetimepicker();
        addContentFormHelp();
        addCounter();
        $("#UnattachedMessageSend-to-match-conditions").chosen();');
    $this->Js->get("input[name*='send-to-type']")->event('change','
        switch ($(this).val()) {
        case "match":
            $("select[name*=\"send-to-match-conditions\"]").attr("disabled",false).trigger("liszt:updated");
            $("select[name*=\"send-to-match-operator\"]").attr("disabled",false);
            $("input[name*=\"file\"]").attr("disabled",true);
            break;
        case "all":
            $("select[name*=\"send-to-match-conditions\"]").attr("disabled", true).val("").trigger("liszt:updated");
            $("select[name*=\"send-to-match-operator\"]").attr("disabled",true);
            $("input[name*=\"file\"]").attr("disabled",true);
            break;
        case "phone":
            $("select[name*=\"send-to-match-conditions\"]").attr("disabled", true).val("").trigger("liszt:updated");
            $("select[name*=\"send-to-match-operator\"]").attr("disabled",true);
            $("input[name*=\"file\"]").attr("disabled", false);
        }');
    $this->Js->get("input[name*='type-schedule']")->event('change','
        if ($(this).val() == "fixed-time" ) {
        $("#fixed-time").attr("disabled",false);
        } else {
        $("#fixed-time").attr("disabled","disabled");
        $("#fixed-time").val("");
        }');
    echo $this->Form->end(__('Save'));?>
	</div>
</div>
<?php echo $this->Js->writeBuffer(); ?>

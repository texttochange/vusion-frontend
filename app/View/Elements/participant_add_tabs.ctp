<span class="tabs">
<ul>
<li <?php echo ($type === 'add' ? 'class="selected"' : ''); ?> >
    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'add')); ?>" >
    <label><?php echo __("Normal"); ?></label>
    </a>
</li>
<li <?php echo ($type === 'simulate' ? 'class="selected"' : ''); ?> >
    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'addSimulated')); ?>" >
    <label><?php echo __("Simulated"); ?></label>
    </a>
</li>
</ul>
</span>
<?php
switch ($type) {
case 'add': 
	echo $this->Form->create('Participant');
	break;
case 'simulate':
	echo $this->Form->create('Participant', array('type' => 'file'));
	break;
}
?>
<div class="tab-content">
<?php
switch ($type) {
case 'add':
    echo $this->Form->input('phone', array('label' => __('Phone')));
	break;
case 'simulate':
    echo $this->Html->tag('div', __('Program Join Type '), array('style'=>'margin-bottom:0px'));
    $options = array(
        'import' => __('Import'),
        'optin-keyword' => __('Optin from Keyword'));
    $attributes = array(
        'legend' => false,
        'style' => 'margin-left:5px',
        'id' => 'join-type');
    echo "<div class='simulator-add-participant'>";
    echo $this->Form->radio(
        'join-type',
        $options,
        $attributes);
    
    echo $this->Form->input(
        'message',
        array(
            'disabled' => true,
            'rows' =>2,
            'label' => __('Message'),
            'name'=>'message',
            'id' => 'smessage'));
    echo '</div>';
    
    $this->Js->get("input[name*='join-type']")->event('change','
        if($(this).val() == "optin-keyword") {
        $("#smessage").attr("disabled", false);
        } else {
        $("#smessage").attr("disabled", "disabled");
        $("#smessage").val("");
        }');
	break;
}?>
</div> 
<?php
echo $this->Form->end(__('Save'));
?>


<div class='table tabs' style='width:100%; margin-top:10px'>
<div class='row' style='width:100%'>
<span class='cell'>
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
</div>
</div>
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
    $joinTypeSelect = 'visibility:hidden';
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
    if (isset($this->Form->data['Participant']['join-type']) &&
        $this->Form->data['Participant']['join-type'] == 'optin-keyword') {
        $joinTypeSelect = "visibility:visible";
    }
    echo $this->Form->input(
        'message',
        array(
            'placeholder' => 'Enter message here: Adding with message might fail if the corresponding Request doesn\'t exist.',
            'rows' =>3,
            'label' => '',
            'name'=>'message',
            'id' => 'smessage',
            'style' =>  $joinTypeSelect));
    echo '</div>';
    
    $this->Js->get("input[name*='join-type']")->event('change','
        if($(this).val() == "optin-keyword") {
        $("#smessage").attr("style", "visibility:visible");
        } else {
        $("#smessage").attr("style", "visibility:hidden");
        $("#smessage").val("");
        }');
	break;
}?>
</div> 
<?php
echo $this->Form->end(__('Save'));
?>


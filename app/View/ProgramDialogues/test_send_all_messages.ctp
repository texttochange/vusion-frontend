<div>
    <div class="test form">
        <?php
        echo $this->Html->tag('h3', __('Send All Messages Of'));
        echo $this->Form->create('SendAllMessages');
        $options = array();
        foreach ($dialogues as $dialogue) {
            if ($dialogue['Active'])
                $options[$dialogue['Active']['_id'].""] = $dialogue['Active']['name'];
            else if ($dialogue['Draft'])
                $options[$dialogue['Draft']['_id'].""] = $dialogue['Draft']['name']. "- draft";
        }
        if (isset($objectId))
            echo $this->Form->select('dialogue-obj-id', $options, array('id' => 'script-selector', 'empty' => 'Select Dialogue...', 'default' => $objectId));
        else
            echo $this->Form->select('dialogue-obj-id', $options, array('id' => 'script-selector', 'empty' => 'Select Dialogue...'));
        echo $this->Form->input('phone-number');
        echo $this->Form->end(__('Send'));
        ?>
    </div>

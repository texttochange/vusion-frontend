<div>
    <div class="test form">
        <?php
        echo $this->Html->tag('h3', __('Send All Messages To'));
        echo $this->Form->create('SendAllMessages');
        $options = array();
        foreach ($scripts as $label => $script) {
            $options[$script[0]['Script']['_id']] = $label;
        }
        echo $this->Form->select('script-id', $options, array('id' => 'script-selector', 'empty' => 'Select Script...'));
        echo $this->Form->input('phone-number');
        echo $this->Form->end(__('Send'));
        ?>
    </div>
    <div class="actions">
        <h3><?php echo __('Actions'); ?></h3>
        <ul>
        </ul>
    </div> 
</div>

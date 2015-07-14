<div class="participant view width-size">
    <?php
        $contentTitle   = __('Simulator'); 
        $contentActions = array();
        
        echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div>
        <dl>
            <dt><?php echo __('Phone'); ?></dt>
            <dd><?php echo $participant['Participant']['phone']; ?></dd>
            
            <?php echo $this->Form->create('Participant');?>
            <fieldset>		
                <?php
                echo $this->Form->input('messageMo', array('rows'=>4, 'label' => __('Message')));
                echo $this->Form->end(__('Send'));
                ?>
            </fieldset>
        </dl>
    </div>

</div>



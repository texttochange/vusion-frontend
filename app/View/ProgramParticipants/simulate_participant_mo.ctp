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
            <table width="90%">
            <?php foreach ($histories as $history): ?>
                <tr>
                    <?php
                    if (isset($history['History']['message-direction'])) {
                        if ($history['History']['message-direction'] == 'outgoing') {?>
                            <td class="simulator-outgoing"><?php echo $history['History']['message-content'];?></td>
                </tr>
                <tr>                
                       <?php } else {?>
                            <td align="right" class="simulator-incoming"><?php echo $history['History']['message-content'];?></td>
                       <?php }
                    }
                    ?>
                </tr> 
            <?php endforeach; ?>    
            </table>
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



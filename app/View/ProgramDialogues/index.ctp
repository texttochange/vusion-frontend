<div class="Dialogue index width-size">
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Dialogue'), array('program'=>$programUrl, 'action' => 'edit'), array('class' => 'ttc-button')); ?></li>
	</ul>
    <h3><?php echo __('Dialogue Index');?></h3>
	<div class="ttc-display-area">    

        <?php
        $draftOnlySeparator = false;
        uasort($dialogues, array('dialogueHelper', 'compareDialogueByName'));
        foreach ($dialogues as $dialogue) {
            $dialogueName = "";
            $actions = "";
            if ($dialogue['Active']) {
                echo "<div class='ttc-dialogue ttc-dialogue-active'>";
                $dialogueName = $dialogue['Active']['name'];
                echo $this->Html->link(
                    $dialogue['Active']['name'], 
                    array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Active']['_id']),
                    array('class'=>'ttc-dialogue-name'));
                if ($dialogue['Draft']) {
                    echo $this->Html->link(
                        '(draft)',
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id']),
                        array('class'=>'ttc-dialogue-draft')
                        );
                    $actions = $this->Html->link(__('Activate draft'), array('program'=>$programUrl,'action'=>'activate', 'id'=>$dialogue['Draft']['_id']), array('class'=>'ttc-button', 'style' => "float:right"));
                }
                //echo $this->Html->tag('div', 'activated', array('class'=>'ttc-dialogue-status-label'));
            } else {
                if (!$draftOnlySeparator) {
                    echo $this->Html->tag('div', __('Only draft'), array('class' => 'ttc-dialogue-separator'));
                    $draftOnlySeparator = true;
                }
                echo "<div class='ttc-dialogue'>";
                $dialogueName = $dialogue['Draft']['name'];
                echo $this->Html->link(
                        $dialogue['Draft']['name'],
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id']),
                        array('class'=>'ttc-dialogue-name')
                        ); 
                $actions = $this->Html->link(__('Activate'), array('program'=>$programUrl,'action'=>'activate', 'id'=>$dialogue['Draft']['_id']), array('class'=>'ttc-button', 'style' => "float:right"));
            }
            echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'action' => 'delete', $dialogue['dialogue-id']), array('class'=>'ttc-button', 'style' => "float:right"), __('Are you sure you want to delete %s?', $dialogueName));
            echo $actions;
            echo "</div>";
        } ?>
    </div>
</div>


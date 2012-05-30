<div class="Dialogue index">
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Dialogue'), array('program'=>$programUrl, 'action' => 'edit')); ?></li>
	</ul>
    <h3><?php echo __('Dialogue Index');?></h3>
	<div class="ttc-display-area">    

        <?php
        
        foreach ($dialogues as $dialogue) {
            $dialogueName = "";
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
                }
                echo $this->Html->tag('div', 'activated', array('class'=>'ttc-dialogue-status-label'));
            } else {
                echo "<div class=ttc-dialogue>";
                $dialogueName = $dialogue['Draft']['name'];
                echo $this->Html->link(
                        $dialogue['Draft']['name'],
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id']),
                        array('class'=>'ttc-dialogue-name')
                        ); 
            }
            echo $this->Form->postLink(__('Delete'), array('program' => $programUrl, 'action' => 'delete', $dialogue['dialogue-id']), array('class'=>'ttc-button'), __('Are you sure you want to delete %s?', $dialogueName));
           
            echo "</div>";
        } ?>
    </div>
</div>


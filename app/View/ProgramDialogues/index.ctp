<div class="Dialogue index width-size">
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Dialogue'), array('program'=>$programDetails['url'], 'action' => 'edit'), array('class' => 'ttc-button')); ?></li>
	</ul>
    <h3><?php echo __('Dialogue Index');?></h3>
	<div class="ttc-display-area display-height-size">    

        <?php
        $draftOnlySeparator = false;
        uasort($dialogues, array('dialogueHelper', 'compareDialogueByName'));
        foreach ($dialogues as $dialogue) {
            $dialogueName = "";
            $actions = "";
            if ($dialogue['Active']) {
                echo "<div class='ttc-dialogue ttc-dialogue-active'>";
                $dialogueNameToolTip = $dialogue['Active']['name'];
                $dialogueName = $this->Text->truncate($dialogue['Active']['name'],
                	115,
                	array('ellipsis' => '...',
                		'exact' => true));
                echo $this->Html->link(
                    $dialogueName,
                    array('program'=>$programDetails['url'], 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Active']['_id']),
                    array('class'=>'ttc-dialogue-name','title' => $dialogueNameToolTip));
                if ($dialogue['Draft']) {
                    echo $this->Html->link(
                        '(draft)',
                        array('program'=>$programDetails['url'], 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id']),
                        array('class'=>'ttc-dialogue-draft')
                        );
                    $actions = $this->Html->link(__('Activate draft'), array('program'=>$programDetails['url'],'action'=>'activate', 'id'=>$dialogue['Draft']['_id']), array('class'=>'ttc-button', 'style' => "float:right"));
                }
                //echo $this->Html->tag('div', 'activated', array('class'=>'ttc-dialogue-status-label'));
            } else {
                if (!$draftOnlySeparator) {
                    echo $this->Html->tag('div', __('Only draft'), array('class' => 'ttc-dialogue-separator'));
                    $draftOnlySeparator = true;
                }
                echo "<div class='ttc-dialogue'>";
                $dialogueNameToolTip = $dialogue['Draft']['name'];
                $dialogueName = $this->Text->truncate($dialogue['Draft']['name'],
                	115,
                	array('ellipsis' => '...',
                		'exact' => true));
                echo $this->Html->link(
                        $dialogueName,
                        array('program'=>$programDetails['url'], 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id']),
                        array('class'=>'ttc-dialogue-name','title' => $dialogueNameToolTip)
                        ); 
                $actions = $this->Html->link(__('Activate'), array('program'=>$programDetails['url'],'action'=>'activate', 'id'=>$dialogue['Draft']['_id']), array('class'=>'ttc-button', 'style' => "float:right"));
            }
            echo $this->Form->postLink(__('Delete'), array('program' => $programDetails['url'], 'action' => 'delete', $dialogue['dialogue-id']), array('class'=>'ttc-button', 'style' => "float:right"), __('Are you sure you want to delete %s?', $dialogueName));
            echo $actions;
            echo "</div>";
        } ?>
    </div>
</div>


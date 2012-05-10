<div class="Dialogue index">
    <h3><?php echo __('Dialogue Index');?></h3>
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Dialogue'), array('program'=>$programUrl, 'action' => 'edit')); ?></li>
	</ul>
	<div class="ttc-display-area">    

        <?php
        
        foreach ($dialogues as $dialogue) {
            if ($dialogue['Active']) {
                echo "<div class='ttc-dialogue ttc-dialogue-active'>";
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
                echo $this->Html->link(
                        $dialogue['Draft']['name'],
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id']),
                        array('class'=>'ttc-dialogue-name')
                        ); 
            }
           
            /* echo $this->Html->link(
                __('Edit'), 
                array('program'=>$programUrl, 'action' => 'edit', $dialogue['Dialogue']['_id']));
            echo $this->Html->link(
                __('Activate'), 
                array('program'=>$programUrl, 'action' => 'activate', $dialogue['Dialogue']['dialogue-id']));
                */
            echo "</div>";
        } ?>
    </div>



<div class="Dialogue index">
    <h3><?php echo __('Dialogue Index');?></h3>
	<ul class="ttc-actions">
		<li><?php echo $this->Html->link(__('New Dialogue'), array('program'=>$programUrl, 'action' => 'edit')); ?></li>
	</ul>
	<div class="ttc-display-area">    

        <?php
        
        foreach ($dialogues as $dialogue) {
            echo "<div class=ttc-dialogue>";
            if ($dialogue['Active']) {
                echo $this->Html->tag('div', $dialogue['Active']['name']);
                if ($dialogue['Draft']) {
                    echo $this->Html->tag('div', 'also a draft');
                }
            } else {
                echo $this->Html->tag('div', $dialogue['Draft']['name']);
                echo $this->Html->tag('div', 'only a draft');
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



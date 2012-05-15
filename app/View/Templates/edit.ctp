<div class="templates form">
    <h3><?php echo __('Edit Template'); ?></h3>
    <?php echo $this->Form->create('Template'); ?>
       <?php echo $this->Form->input(__('name')); ?>
       <div class="ttc-help-box">
           <p>Re-type the text below in the Translation box while replacing text in black with its translation in the language being created.
           Maintain the rest of the words and characters in their case and position</p>
           <span style="color:red">{QUESTION}{break line}<br />{ANSWER 1}{break line}<br />{ANSWER 2}{break line}<br />
           {ANSWER ...}{break line}<br /><br /></span>
           To Reply, type <span style="color:red">KEYWORD &lt;</span>space<span style="color:red">&gt;&lt;</span>
           Answer Nr<span style="color:red">&gt; &amp;</span> send to <span style="color:red">SHORTCODE</span>
       </div>
       <?php echo $this->Form->input(__('translation'), array('rows'=>3)); ?>
    <?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	
</div>

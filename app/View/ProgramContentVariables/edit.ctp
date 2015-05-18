<div class="content_variables form width-size">
    <?php
        $contentTitle   = __('Edit Content Variable'); 
        $contentActions = array();
        
        $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'action' => 'index'),
        array('class' => 'ttc-button'));
        
        $contentActions[] = $this->Html->link(__('Save'),
            array(),
            array('class'=>'ttc-button',
                'id' => 'button-save'));
        $this->Js->get('#button-save')->event('click',
            '$("#ContentVariableEditForm").submit()' , true);
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div class="ttc-display-area">
    <?php echo $this->Form->create('ContentVariable'); ?>
    <fieldset>
       <?php
            $keypair = '';     
            $keys = $this->data['ContentVariable']['keys'];
            if (is_array($keys)) {
                foreach ($keys as $key => $value) {
                    foreach ($value as $key1 => $value1) {
                        $keypair = $keypair . $value1 . ".";
                    }                         
                }
                $keypair = rtrim($keypair, '.');
            } else {
                $keypair = $keys;
            }
            echo $this->Form->input('keys', array('label' => __('keys pair'), 'value'=>$keypair));
            echo $this->Form->input('value', array('label' => __('value')));
        ?>
    </fieldset>
       <?php echo $this->Form->end(__('Save')); ?>
   </div>
</div>

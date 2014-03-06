<div class="index" >
	<h3><?php echo __('Program Simulator'); ?></h3>
	<?php 
        if (isset($dialogues) && !isset($this->params['id'])) {
            echo $this->Form->create(null, array('default'=>false));
            $options = array();
            foreach ($dialogues as $dialogue) {
                if ($dialogue['Active'])
                    $options[$dialogue['Active']['_id'].''] = $dialogue['Active']['name'].' - active';
                if ($dialogue['Draft'])
                    $options[$dialogue['Draft']['_id'].''] = $dialogue['Draft']['name'].' - draft';
            }
            echo $this->Form->select('dialogue', $options, array('id' => 'dialogue-selector', 'empty' => 'Existing Dialogue...'));
            echo $this->Form->end();
            $this->Js->get('#dialogue-selector')->event('change', '
	           window.location = window.location+"/"+$("select option:selected").val();
	       ');
	    } else { 
	    echo $this->Html->tag('label', 'Exchanges');
	    echo $this->Html->tag('div', "it's going be here...", array('class'=>'ttc-simulator-output', 'id' => 'simulator-output'));
	    echo $this->Form->create(null, array('id'=>'simulator-input','default'=>false));
	    echo $this->Form->input('from', array('value' => 'phone here...', 'name'=>'participant-phone'));
	    echo $this->Form->input('message', array('value' => 'message here...', 'name'=>'message'));
	    echo $this->Form->end(array('label' => __('Send Message'), 'id'=>'send-button'));
	    $this->Js->get('#send-button')->event(
	           'click',
	           $this->Js->request(
	               array('program'=>$programUrl, 'action'=>'send.json'),
	               array('method' => 'POST',
                             'async' => true, 
	                     'dataExpression' => true,
	                     'data' => '$("#simulator-input").serialize()',
	                     'success' => 'logMessageSent()')));
	     $this->Js->get('document')->event(
	        'ready',
	        'setInterval(function(){pullSimulatorUpdate("'.$this->Html->url(array('program'=>$programUrl,'action'=>'receive.json')).'")}, 3000);');
	    }
       ?>
</div>

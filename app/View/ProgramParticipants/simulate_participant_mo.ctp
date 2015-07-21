<div class="participant view width-size">
    <?php
        $contentTitle   = __('Simulate Participant'); 
        $contentActions = array();
        
        echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div>
        <table class="simulator">
         <tr>
             <td class="simulator-message">
                 <?php echo $this->Form->create(null, array('id'=>'simulator-input','default'=>false));?>
                 <fieldset>		
                 <?php
                 echo $this->Form->input('from', array(
                     'value' => $participant['Participant']['phone'],
                     'name'=>'phone',
                     'type' => 'hidden'));
                 echo $this->Form->input('message', array('rows'=>4, 'label' => __('Message'), 'name' => 'message'));
                 echo $this->Form->end(array('label' => __('Send'), 'id'=>'send-button'));
                 
                 $this->Js->get('#send-button')->event(
                     'click',
                     $this->Js->request(
                         array('program'=>$programDetails['url'], 'action'=>'send.json'),
                         array('method' => 'POST',
                             'async' => true, 
                             'dataExpression' => true,
                             'data' => '$("#simulator-input").serialize()',
                             'success' => 'logMessageSent()')));
                 $this->Js->get('document')->event(
                     'ready',
                     'setInterval(function(){pullSimulatorUpdate("'.$this->Html->url(array('program'=>$programDetails['url'],'action'=>'receive.json')).'")}, 3000);');
                 
                 ?>
                 </fieldset>
             </td>
             <td class="simulator-profile">
                 <dl>
                     <dt><?php echo __('Phone');
                     echo (': ');
                     echo $participant['Participant']['phone'];?></dt>
                 </dl>
             </td>    
         <tr>
        </table>
        
    </div>

</div>
<?php echo $this->Js->writeBuffer(); ?>        


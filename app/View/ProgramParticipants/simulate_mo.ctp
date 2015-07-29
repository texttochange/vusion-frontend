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
             <?php
                 echo $this->Html->tag('div', "", array('class'=>'ttc-simulator-output', 'id' => 'simulator-output'));
                 echo $this->Form->create(null, array('id'=>'simulator-input'));
                 echo $this->Form->input('from', array(
                     'value' => $participant['Participant']['phone'],
                     'name'=>'phone',
                     'type' => 'hidden'
                     ));
                 echo $this->Form->input('message', array('rows'=>4, 'label' => __('Message'), 'name' => 'message'));
                 echo $this->Form->end(array('label' => __('Send'), 'id'=>'send-button'));
                                
                 $this->Js->get('#send-button')->event(
                    'click',
                    $this->Js->request(
                        array('program'=>$programDetails['url'], 'action'=>'simulateMo.json'),
                        array('method' => 'POST',
                            'async' => true, 
                            'dataExpression' => true,
                            'data' => '$("#simulator-input").serialize()',
                            'success' => 'logMessageSent()'
                            )));
                 
                 $this->Js->set('participant', $participant['Participant']);
                 $this->Js->get('document')->event(
                     'ready',
                     'setInterval(function()
                     {
                     $("[name=phone]").val();
                     pullSimulatorUpdate("'.$this->Html->url(array('program'=>$programDetails['url'],'action'=>'pullSimulateUpdate.json')).'")
                     },
                     3000);');
                       
                 ?>
                 </td>
             <td class="simulator-profile">
                 <dl>
                     <dt>
                     <?php 
                         echo __('Phone');
                         echo (': ');
                         echo $participant['Participant']['phone'];?>
                     </dt>
                 </dl>
                 <dl>
                     <dt>
                     <?php
                         echo __('Labels');
                         echo (': ');
                         if (count($participant['Participant']['profile']) > 0) {
                             foreach ($participant['Participant']['profile'] as $profileItem) {
                                 echo $this->Html->tag('div', __("%s: %s", $profileItem['label'], $profileItem['value']));
                             }
                         } else {
                             echo "&nbsp;"; 
                         }?>
                     </dt>
                  </dl>
                  <dl>
                     <dt>
                     <?php
                         echo __('Tags');
                         echo (': ');
                         if (count($participant['Participant']['tags']) > 0) {
                             foreach ($participant['Participant']['tags'] as $tag) {
                                 echo $this->Html->tag('div', __("%s", $tag));
                             }
                         } else {
                             echo "&nbsp;"; 
                         }?>
                     </dt>
                 </dl>
             </td>    
         <tr>
        </table>
        
    </div>

</div>
<?php echo $this->Js->writeBuffer(); ?> 

<div class='ttc-navigation-menu'>
<?php

    echo $this->Js->get('document')->event(
        'ready',
        '$("ul.sf-menu").superfish({ 
            animation: {height:"show"},   // slide-down effect without fade-in 
            delay:     1200               // 1.2 second delay on mouseout 
         });'
        );
 
?>

<ul class="sf-menu sf-vertical">  
    <li>
        <?php 
        echo $this->Html->link(
            __('Dialogues'),
            array('program'=>$programUrl, 'controller'=>'programDialogues','action'=>'index')); 
        ?>
        <ul>
            <li>
            <?php 
            echo $this->Html->link(
                __('New Dialogue'),
                array('program'=>$programUrl, 'controller'=>'programDialogues','action'=>'edit')); 
            ?>
            </li>
            <?php foreach ($dialogues as $dialogue) { ?>
                <li>
                <?php 
                if ($dialogue['Active']) {
                    echo $this->Html->link(
                        $dialogue['Active']['name'],
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Active']['_id'])
                        ); 
                } else {
                    echo $this->Html->link(
                        $dialogue['Draft']['name'],
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id'])
                        ); 
                }              
                ?>
                <ul>
                    <?php if ($dialogue['Active']) {?>
                    <li>
                    <?php 
                    echo $this->Html->link(
                        __('Active'),
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Active']['_id'])
                    ); ?>
                    </li>
                    <?php } ?>
                    <?php if ($dialogue['Draft']) {?>
                    <li>
                    <?php 
                    echo $this->Html->link(
                        __('Draft'),
                        array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'edit', 'id'=> $dialogue['Draft']['_id'])
                    ); ?>
                    <ul>
                        <li>
                        <?php 
                        echo $this->Html->link(
                            __('Activate'),
                            array('program'=>$programUrl, 'controller'=>'programDialogues', 'action'=>'activate', 'id'=> $dialogue['Draft']['_id'])
                            ); ?>
                        </li>
                    </ul>
                    </li>
                    <?php } ?>
                </ul>
                </li>
            <?php } ?>
        </ul>
    </li>
    <li>
       <?php 
        echo $this->Html->link(
            __('Requests'),
            array('program'=>$programUrl, 'controller'=>'programRequests','action'=>'index')); 
        ?>
        <ul>
            <li>
            <?php 
            echo $this->Html->link(
                __('New Requests'),
                array('program'=>$programUrl, 'controller'=>'programRequests','action'=>'add')); 
            ?>
            </li>
            <?php if(isset($requests) && $requests!=null) { ?>
            <?php foreach ($requests as $request): ?>
                <li>
                <?php
                echo $this->Html->link( $request['Request']['keyword'],
                           array(
                               'program'=>$programUrl,
                               'controller'=>'programRequests',
                               'action' => 'edit', 
                               $request['Request']['_id']
                               )
                           );
                ?>
               </li>
               <?php endforeach; ?>
           <?php } ?>
        </ul>
    </li>
    <li>
       <?php echo $this->Html->link(__('Separate Messages'),
           array(
               'program'=>$programUrl,
               'controller'=>'programUnattachedMessages',
               'action'=>'index'
               )
           ); ?>
       <ul>
           <li><?php echo $this->Html->link(__('New Message'),
               array(
                   'program'=>$programUrl,
                   'controller'=>'programUnattachedMessages',
                   'action' => 'add'
                   )
               ); ?>
           </li>
           <?php if(isset($programUnattachedMessages) && $programUnattachedMessages!=null) { ?>
           <?php foreach ($programUnattachedMessages as $unattachedMessage): ?>
               <li>
               <?php
                   echo $this->Html->link(__($unattachedMessage['UnattachedMessage']['name']),
                       array(
                           'program'=>$programUrl,
                           'controller'=>'programUnattachedMessages',
                           'action' => 'edit', $unattachedMessage['UnattachedMessage']['_id']
                           )
                       );
               ?>
               </li>
           <?php endforeach; ?>
           <?php } ?>
       </ul>
    </li>  
    <li>
        <?php echo $this->Html->link(__('Participants'),
            array(
                'program'=>$programUrl,
                'controller'=>'programParticipants',
               'action'=>'index'
               )
            ); ?>
        <ul>
            <li>
                <?php echo $this->Html->link(__('Add Participant'),
                    array(
                        'program' => $programUrl,
                        'controller' => 'programParticipants',
                        'action' => 'add'
                        )
                    ); ?>
            </li>
		    <li>
		        <?php echo $this->Html->link(__('Import Participant(s)'),
		            array(
		                'program' => $programUrl,
		                'controller' => 'programParticipants',
		                'action' => 'import'
		                )
		            ); ?>
		    </li>
        </ul>
    </li>
    <li>
        <?php echo $this->Html->link(__('History'), array('program'=>$programUrl,'controller'=>'programHistory')); ?>
        <ul>
            <li>
                <?php echo $this->Html->link('Export CSV',
                    array(
                        'program' => $programUrl,
                        'controller'=>'programHistory',
                        'action' => 'export.csv'
                        )
                    ); ?>
            </li>            
            <li>
                <?php echo $this->Html->link('Export Raw CSV',
                    array(
                        'program' => $programUrl,
                        'controller'=>'programHistory',
                        'action' => 'index.csv'
                        )
                    ); ?>
            </li>
            <li>
                <?php echo $this->Html->link('Export Json',
                    array(
                        'program' => $programUrl,
                        'controller'=>'programHistory',
                        'action' => 'index.json'
                        )
                    ); ?>
            </li>
        </ul>
    </li>
    <li>
        <?php echo $this->Html->link(__('Settings'),
            array(
                'program'=>$programUrl,
                'controller'=>'programSettings',
               'action'=>'index'
                )
            ); ?>
    </li>
    <li>
        <?php echo $this->Html->link(__('Logs'),
            array(
                'program'=>$programUrl,
                'controller'=>'programLogs'
                )
            ); ?>
    </li>
    <li>
        <?php echo $this->Html->link(__('Program List'),
            array(
                'controller'=>'programs'
                )
            ); ?>
    </li>
</ul>  

</div>

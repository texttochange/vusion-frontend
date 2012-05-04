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
       <?php echo $this->Html->link(__('Unattached Message'),
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
           <li>
               <?php echo $this->Html->link("Edit", array()); ?>               
               <ul>
                   <?php foreach ($programUnattachedMessages as $unattachedMessage): ?>
                   <li>
                   <?php
                       echo $this->Html->link(__($this->Time->format('d/m/Y H:i:s', $unattachedMessage['UnattachedMessage']['schedule'])),
                           array(
                               'program'=>$programUrl,
                               'controller'=>'programUnattachedMessages',
                               'action' => 'edit', $unattachedMessage['UnattachedMessage']['_id']
                               )
                           );
                   ?>
                   </li>
                   <?php endforeach; ?>
               </ul>               
           </li>
           <?php } ?>
       </ul>
    </li>  
    <li>
        <?php echo $this->Html->link(__('Scripts'),
            array(
                'program'=>$programUrl,
                'controller'=>'programScripts',
                'action'=>'index'
                )
            ); ?>
        <?php if ($hasScriptActive or $hasScriptDraft) { ?>
        <ul> 
            <?php if ($hasScriptDraft) { ?>
            <li>
                <?php echo $this->Html->link(__('Draft'),
                    array(
                        'program'=>$programUrl,
                        'controller'=>'programScripts',
                        'action'=>'draft'
                        )
                    ); ?>
            </li>
            <?php } ?>
            <?php if ($hasScriptActive) { ?>
            <li>
                <?php echo $this->Html->link(__('Active'),
                    array(
                        'program'=>$programUrl,
                        'controller'=>'programScripts',
                        'action'=>'active'
                        )
                    ); ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
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
                <?php echo $this->Html->link('Export Raw CSV',
                    array(
                        'program' => $programUrl,
                        'controller'=>'programHistory',
                        'action' => 'index.csv'
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

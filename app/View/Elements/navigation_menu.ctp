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
        echo $this->AclLink->generateLink(__('Dialogues'),$programUrl,'programDialogues');
        ?>        
        <ul>
            <li>
            <?php 
            echo $this->AclLink->generateLink(__('New Dialogue'),$programUrl,'programDialogues','edit');    
            ?>
            </li>
            <?php foreach ($dialogues as $dialogue) { ?>
                <li>
                <?php 
                if ($dialogue['Active']) {
                    echo $this->AclLink->generateLink($dialogue['Active']['name'],$programUrl,'programDialogues','edit',$dialogue['Active']['_id']);
                } else {
                    echo $this->AclLink->generateLink($dialogue['Draft']['name'],$programUrl,'programDialogues','edit',$dialogue['Draft']['_id']);
                }              
                ?>
                <ul>
                    <?php if ($dialogue['Active']) {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Active'),$programUrl,'programDialogues','edit',$dialogue['Active']['_id']);
                    ?>
                    </li>
                    <?php } ?>
                    <?php if ($dialogue['Draft']) {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Draft'),$programUrl,'programDialogues','edit',$dialogue['Draft']['_id']);
                    ?>
                    <ul>
                        <li>
                        <?php 
                        echo $this->AclLink->generateLink(__('Activate'),$programUrl,'programDialogues','edit',$dialogue['Draft']['_id']);
                        ?>
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
        echo $this->AclLink->generateLink(__('Requests'),$programUrl,'programRequests');
        ?>
        <ul>
            <li>
            <?php 
            echo $this->AclLink->generateLink(__('New Request'),$programUrl,'programRequests','add');
            ?>
            </li>
            <?php if(isset($requests) && $requests!=null) { ?>
            <?php foreach ($requests as $request): ?>
                <li>
                <?php
                echo $this->AclLink->generateLink($request['Request']['keyword'],$programUrl,'programRequests','edit',$request['Request']['_id']);
                ?>
               </li>
               <?php endforeach; ?>
           <?php } ?>
        </ul>
    </li>
    <li>
       <?php
       echo $this->AclLink->generateLink(__('Separate Messages'),$programUrl,'programUnattachedMessages');
       ?>
       <ul>
           <li>
           <?php
           echo $this->AclLink->generateLink(__('New Message'),$programUrl,'programUnattachedMessages','add');
           ?>
           </li>
           <?php if(isset($programUnattachedMessages) && $programUnattachedMessages!=null) { ?>
           <?php foreach ($programUnattachedMessages as $unattachedMessage): ?>
               <li>
               <?php
                   echo $this->AclLink->generateLink(
                       __($unattachedMessage['UnattachedMessage']['name']),
                       $programUrl,
                       'programUnattachedMessages',
                       'edit',
                       $unattachedMessage['UnattachedMessage']['_id']
                       );
               ?>
               </li>
           <?php endforeach; ?>
           <?php } ?>
       </ul>
    </li>  
    <li>
        <?php
        echo $this->AclLink->generateLink(__('Participants'),$programUrl,'programParticipants','index');
        ?>
        <ul>
            <li>
                <?php 
                     echo $this->AclLink->generateLink(__('Add Participants'),$programUrl,'programParticipants','add');
                ?>
            </li>
		    <li>
		        <?php
		             echo $this->AclLink->generateLink(__('Import Participants'),$programUrl,'programParticipants','import');
		        ?>
		    </li>
        </ul>
    </li>
    <li>
        <?php echo $this->AclLink->generateLink(__('History'),$programUrl,'programHistory'); ?>
        <ul>
            <li>
                <?php
                    echo $this->AclLink->generateLink(__('Export CSV'),$programUrl,'programHistory','export',null,'.csv');
                ?>
            </li>            
            <li>
                <?php
                    echo $this->AclLink->generateLink(__('Export Raw CSV'),$programUrl,'programHistory','index',null,'.csv');
                ?>
            </li>
            <li>
                <?php
                    echo $this->AclLink->generateLink(__('Export Json'),$programUrl,'programHistory','index',null,'.json');
                ?>
            </li>
        </ul>
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Settings'),$programUrl,'programSettings','index');
         ?>
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Logs'),$programUrl,'programLogs');
         ?>
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Program List'),null,'programs','index');
        ?>
    </li>
</ul>  

</div>

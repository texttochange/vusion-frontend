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
        echo $this->AclLink->generateLink(__('Requests'), $programDetails['url'], 'programRequests');
        ?>
        <ul>
            <li>
            <?php 
            echo $this->AclLink->generateLink(__('New Request'),$programDetails['url'],'programRequests','add');
            ?>
            </li>
            <?php 
            if(isset($requests) && $requests!=null) { 
                foreach ($requests as $request) {
                    echo "<li title='".$request['Request']['keyword']."'>";
                    $requestLinkName = $this->Text->truncate(
                        $request['Request']['keyword'],
                        20,
                        array('ellipsis' => '...',
                            'exact' => true
                            ));
                    echo $this->AclLink->generateLink($requestLinkName,
                        $programDetails['url'], 'programRequests', 'edit', $request['Request']['_id']);
                    echo "</li>";
                }; 
            } 
            ?>
        </ul>
    </li>

    <li>
        <?php
        echo $this->AclLink->generateLink(__('Dialogues'),$programDetails['url'],'programDialogues');
        ?>        
        <ul>
            <li>
            <?php 
            echo $this->AclLink->generateLink(__('New Dialogue'),$programDetails['url'],'programDialogues','edit');    
            ?>
            </li>
            <?php foreach ($dialogues as $dialogue) { 
                if ($dialogue['Active']) {
                    $dialogue = $dialogue['Active'];
                    $isActive = true;
                } else {
                    $dialogue = $dialogue['Draft'];
                    $isActive = false;
                }
                echo "<li title='".$dialogue['name']."'>";
                $dialogueLinkName = $this->Text->truncate(
                    $dialogue['name'],
                    18,
                    array('ellipsis' => '...',
                        'exact' => true
                        ));
                echo $this->AclLink->generateLink($dialogueLinkName, 
                    $programDetails['url'], 'programDialogues', 'edit', $dialogue['_id']);
                ?>
                <ul>
                    <?php if ($isActive) {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Active'), 
                        $programDetails['url'], 'programDialogues', 'edit', $dialogue['_id']);
                    ?>
                    </li>
                    <?php } else {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Draft'),
                        $programDetails['url'], 'programDialogues', 'edit', $dialogue['_id']);
                    ?>
                    <ul>
                        <li>
                        <?php 
                        echo $this->AclLink->generateLink(__('Activate'),
                            $programDetails['url'], 'programDialogues', 'edit', $dialogue['_id']);
                        ?>
                        </li>
                    </ul>
                    </li>
                    <?php } ?>
                </ul>
                <?php 
                echo "</li>";
                } ?>
        </ul>
    </li>
    
    <li>
       <?php
       echo $this->AclLink->generateLink(__('Separate Messages'),$programDetails['url'],'programUnattachedMessages');
       ?>
       <ul>
           <li>
           <?php
           echo $this->AclLink->generateLink(__('New Message'),$programDetails['url'],'programUnattachedMessages','add');
           ?>
           </li>
           <?php 
           if(isset($programUnattachedMessages) && $programUnattachedMessages!=null) { 
               foreach ($programUnattachedMessages as $unattachedMessage) {
                   echo "<li title='".$unattachedMessage['UnattachedMessage']['name']."'>";
                   $unattachedMessageLinkName = $this->Text->truncate(
                       $unattachedMessage['UnattachedMessage']['name'],
                       20,
                       array('ellipsis' => '...',
                           'exact' => true
                           ));      
		
                   
                   echo $this->AclLink->generateLink($unattachedMessageLinkName,
                       $programDetails['url'], 'programUnattachedMessages', 'edit', $unattachedMessage['UnattachedMessage']['_id']);
                   echo "</li>";
               }
           } 
           ?>
       </ul>
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Predefined Messages'),$programDetails['url'],'programPredefinedMessages');
         ?>
         <ul>
           <li>
           <?php
           echo $this->AclLink->generateLink(__('New Message'),$programDetails['url'],'programPredefinedMessages','add');
           ?>
           </li>
           <?php 
           if(isset($predefinedMessages) && $predefinedMessages!=null) { 
               foreach ($predefinedMessages as $predefinedMessage) {
                   echo "<li title='".$predefinedMessage['PredefinedMessage']['name']."'>";
                   $predefinedMessageLinkName = $this->Text->truncate(
                       $predefinedMessage['PredefinedMessage']['name'],
                       20,
                       array('ellipsis' => '...',
                           'exact' => true
                           ));      
		
                   
                   echo $this->AclLink->generateLink($predefinedMessageLinkName,
                       $programDetails['url'], 'programPredefinedMessages', 'edit', $predefinedMessage['PredefinedMessage']['_id']);
                   echo "</li>";
               }
           } 
           ?>
       </ul>
    </li>  
    <li>
        <?php
        echo $this->AclLink->generateLink(__('Participants'),$programDetails['url'],'programParticipants','index');
        ?>
        <ul>
            <li>
                <?php 
                     echo $this->AclLink->generateLink(__('Add Participants'),$programDetails['url'],'programParticipants','add');
                ?>
            </li>
		    <li>
		        <?php
		             echo $this->AclLink->generateLink(__('Import Participants'),$programDetails['url'],'programParticipants','import');
		        ?>
		    </li>
        </ul>
    </li>
    <li>
        <?php echo $this->AclLink->generateLink(__('History'),$programDetails['url'],'programHistory'); ?>        
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Settings'),$programDetails['url'],'programSettings','index');
         ?>
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Logs'),$programDetails['url'],'programLogs');
         ?>
    </li>
    <li>
        <?php
            echo $this->AclLink->generateLink(__('Program List'),null,'programs','index');
        ?>
    </li>
</ul>  

</div>

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
        echo $this->AclLink->generateLink(__('Requests'),$programUrl,'programRequests');
        ?>
        <ul>
            <li>
            <?php 
            echo $this->AclLink->generateLink(__('New Request'),$programUrl,'programRequests','add');
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
                        $programUrl, 'programRequests', 'edit', $request['Request']['_id']);
                    echo "</li>";
                }; 
            } 
            ?>
        </ul>
    </li>

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
                    $programUrl, 'programDialogues', 'edit', $dialogue['_id']);
                ?>
                <ul>
                    <?php if ($isActive) {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Active'), 
                        $programUrl, 'programDialogues', 'edit', $dialogue['_id']);
                    ?>
                    </li>
                    <?php } else {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Draft'),
                        $programUrl, 'programDialogues', 'edit', $dialogue['_id']);
                    ?>
                    <ul>
                        <li>
                        <?php 
                        echo $this->AclLink->generateLink(__('Activate'),
                            $programUrl, 'programDialogues', 'edit', $dialogue['_id']);
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
       echo $this->AclLink->generateLink(__('Separate Messages'),$programUrl,'programUnattachedMessages');
       ?>
       <ul>
           <li>
           <?php
           echo $this->AclLink->generateLink(__('New Message'),$programUrl,'programUnattachedMessages','add');
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
                       $programUrl, 'programUnattachedMessages', 'edit', $unattachedMessage['UnattachedMessage']['_id']);
                   echo "</li>";
               }
           } 
           ?>
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

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
            if(isset($currentProgramData['requests']) && $currentProgramData['requests']!=null) { 
                foreach ($currentProgramData['requests'] as $request) {
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
            <?php foreach ($currentProgramData['dialogues'] as $dialogue) { 
                if ($dialogue['Active']) {
                    //$dialogue = $dialogue['Active'];
                    $dialogueName = $dialogue['Active']['name'];
                    $dialogueId = $dialogue['Active']['_id'];
                    $isActive = true;
                } else {
                    //$dialogue = $dialogue['Draft'];
                    $dialogueName = $dialogue['Draft']['name'];
                    $dialogueId = $dialogue['Draft']['_id'];
                    $isActive = false;
                }
                echo "<li title='".$dialogueName."'>";
                $dialogueLinkName = $this->Text->truncate(
                    $dialogueName,
                    18,
                    array('ellipsis' => '...',
                        'exact' => true
                        ));
                echo $this->AclLink->generateLink($dialogueLinkName, 
                    $programDetails['url'], 'programDialogues', 'edit', $dialogueId);
                ?>
                <ul>
                    <?php if ($isActive) {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Active'), 
                        $programDetails['url'], 'programDialogues', 'edit', $dialogue['Active']['_id']);
                    ?>
                    </li>
                    <?php }
                    if ($dialogue['Draft']) {?>
                    <li>
                    <?php
                    echo $this->AclLink->generateLink(__('Draft'),
                        $programDetails['url'], 'programDialogues', 'edit', $dialogue['Draft']['_id']);
                    ?>
                    <ul>
                        <li>
                        <?php 
                        echo $this->AclLink->generateLink(__('Activate'),
                            $programDetails['url'], 'programDialogues', 'activate', $dialogue['Draft']['_id']);
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
           <li>
           <?php
           echo $this->AclLink->generateLink(__('Scheduled'), $programDetails['url'],
                    'programUnattachedMessages', 'index', null, null, array('type' => 'scheduled')); 
           ?>
           <ul>
           <?php
           if (isset($currentProgramData['unattachedMessages']['scheduled'])) { 
               foreach ($currentProgramData['unattachedMessages']['scheduled'] as $unattachedMessage) {
                   echo "<li title='".$unattachedMessage['UnattachedMessage']['name']."'>";
                   $unattachedMessageLinkName = $this->Text->truncate(
                       $unattachedMessage['UnattachedMessage']['name'],
                       20,
                       array(
                        'ellipsis' => '...',
                        'exact' => true));
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
           echo $this->AclLink->generateLink(__('Drafted'),$programDetails['url'],
                    'programUnattachedMessages','index', null, null, array('type' => 'drafted'));
           ?>
           <ul>
           <?php
           if (isset($currentProgramData['unattachedMessages']['drafted'])) { 
               foreach ($currentProgramData['unattachedMessages']['drafted'] as $unattachedMessage) {
                   echo "<li title='".$unattachedMessage['UnattachedMessage']['name']."'>";
                   $unattachedMessageLinkName = $this->Text->truncate(
                       $unattachedMessage['UnattachedMessage']['name'],
                       20,
                       array(
                        'ellipsis' => '...',
                        'exact' => true));
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
           echo $this->AclLink->generateLink(
                    __('Sent'), $programDetails['url'],
                    'programUnattachedMessages','index', null, null, array('type' => 'sent')); ?>
           </li>
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
           if(isset($currentProgramData['predefinedMessages']) && $currentProgramData['predefinedMessages']!=null) { 
               foreach ($currentProgramData['predefinedMessages'] as $predefinedMessage) {
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
        echo $this->AclLink->generateLink(__('Content Variables'),$programDetails['url'],'programContentVariables','index');
        ?>
        <ul>
            <li>
                <?php 
                     echo $this->AclLink->generateLink(__('Keys/Value'), $programDetails['url'], 'programContentVariables', 'index');
                ?>
            </li>
            <li>
                <?php 
                     echo $this->AclLink->generateLink(__('Tables'), $programDetails['url'], 'programContentVariables', 'indexTable');
                ?>
            </li>
        </ul>
    </li>  
    <li>
        <?php
        echo $this->AclLink->generateLink(__('Participants'),$programDetails['url'],'programParticipants','index');
        ?>
        <ul>
            <li>
                <?php 
                     echo $this->AclLink->generateLink(__('Add'),$programDetails['url'],'programParticipants','add');
                ?>
            </li>
              <li>
                  <?php
                      echo $this->AclLink->generateLink(__('Import'),$programDetails['url'],'programParticipants','import');
                  ?>
              </li>
            <li>
                <?php
                    echo $this->AclLink->generateLink(__('Exported File(s)'),$programDetails['url'],'programParticipants','exported');
                ?>
            </li>
        </ul>
    </li>
    <li>
        <?php echo $this->AclLink->generateLink(__('History'),$programDetails['url'],'programHistory'); ?>        
        <ul>
          <li>
            <?php echo $this->AclLink->generateLink(__('Exported File(s)'),$programDetails['url'],'programHistory','exported'); ?>
          </li>
        </ul>
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

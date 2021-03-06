<?php
    $this->RequireJs->scripts(array("jquery"));
?>
<div class="status index">
    <?php
    $contentTitle = __('Program History');
    $contentActions = array();
    $containsDataControlNav = true;
    $containsFilter = true;
    $controller = 'programHistory';
    $urlParams = (isset($urlParams) ? $urlParams : "");
    
    $contentActions[] = $this->AclLink->generatePostLink(
        __('Delete'),
        $programDetails['url'], 
        $controller,
        'delete', 
        __('Are you sure you want to delete %s histories?', $this->Paginator->counter(array(
            'format' => __('{:count}')))),
        array('class' => 'ttc-button'),
        null,
        $urlParams); 
    
    if ($histories != null) {
        $exportUrl = array(
            'program' => $programDetails['url'],
            'controller' => $controller,
            'action' => 'export',
            '?' => $urlParams);
        if ($order != null) {
            $exportUrl['sort'] = key($order);
            $exportUrl['direction'] = $order[key($order)];
        }
        $contentActions[] = $this->AclLink->generateButtonFromUrl(
            __('Export'),
            $exportUrl,
            array('class' => 'ttc-button'));
    }
    
    $contentActions[] = $this->Html->tag(
        'span',
        __('Filter'),
        array('class' => 'ttc-button', 'name' => 'add-filter'));
    $this->Js->get('[name=add-filter]')->event(
        'click',
        '$("#advanced_filter_form").show();
        createFilter();
        addStackFilter();');
    
    echo $this->element('header_content', 
        compact('contentTitle', 'contentActions', 'containsFilter','containsDataControlNav', 'controller'));
    ?>
    <div class="ttc-table-display-area">
    <div  class="ttc-table-scrolling-area display-height-size">
    <table  class="histories" cellpadding="0" cellspacing="0">
        <thead >
            <tr>
                <?php
                $userGroupId = $this->Session->read('Auth.User.Group.id');
                if ($userGroupId == 6)
                    {?>
                    <th class="profile"><?php 
                        echo $this->Paginator->sort('profile',__('Labels'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
                    <th class="details2"><?php 
                        echo $this->Paginator->sort('message-content', __('Details'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
              <?php } else {?>
                     <th class="phone"><?php
                        echo $this->Paginator->sort('participant-phone', __('Phone'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
                    <th class="direction"><?php 
                        echo $this->Paginator->sort('message-direction', __('Direction'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
                    <th class="status"><?php 
                        echo $this->Paginator->sort('message-status', __('Status'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
                    <th class="details2"><?php 
                        echo $this->Paginator->sort('message-content', __('Details'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
                   
                <?php } ?>
                <th class="date-time"><?php 
                        echo $this->Paginator->sort('timestamp', __('Time'), array('url'=> array('program' => $programDetails['url'], '?'=>$this->params['url'])));?></th>
                
            </tr>
        </thead>
        <tbody>
            <?php if (preg_grep('/^filter/', array_keys($this->params['url'])) && $histories == null) { ?>
            <tr>
                <td colspan=6><?php echo __("No results found.") ?></td>
            </tr>
            <?php } else {
                foreach ($histories as $history):?>
                <tr>
                    <?php if ($userGroupId == 6) {
                        if (ucfirst($history['History']['message-direction']) == 'Incoming') {?>  
                            <td class="phone"><?php 
                            if (count($history['History']['participant-labels'] > 0)) {
                                foreach ($history['History']['participant-labels'] as $profileItem) {
                                    $profileItemsLabel = $profileItem['label'];
                                    $profileItemsValue = $profileItem['value'];
                                    $participantProfile = $profileItemsLabel . ': ' . $profileItemsValue;	              		  		 
                                    echo $this->Html->tag('div', $participantProfile, array('class'=> 'participant-tuncated-profile', 'title' => $participantProfile)); 
                                }
                            } else {
                                echo $this->Html->tag('div', ''); 
                            }?></td>
                            <td class="details"><?php echo htmlspecialchars($history['History']['message-content']); ?>&nbsp;</td>
                            <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
                        <?php }
                    } else { ?>
                        <td class="phone"><?php echo $history['History']['participant-phone'];?></td>
                        <td class="direction"><?php echo ucfirst($history['History']['message-direction']); ?></td>
                        <?php
                        $status = "&nbsp;";
                        $title = null;
                        if (isset($history['History']['message-status'])) {
                            $status = $history['History']['message-status'];
                            switch ($status) {
                            case 'failed':
                                $title = $history['History']['failure-reason'];
                                break;
                            case 'nack':
                                $title = $history['History']['failure-reason'];
                                break;    
                            case 'forwarded':
                                $tmp=array();
                                foreach ($history['History']['forwards'] as $forward) {
                                    $timestamp = $this->Time->format('d/m/Y H:i:s', $forward['timestamp']);
                                    if ($forward['status'] == 'failed') {
                                        $reason = str_replace('"',"'",$forward['failure-reason']);
                                        $tmp[] = __("forward is %s reason %s at %s by %s", $forward['status'], $reason, $timestamp, $forward['to-addr']);
                                    } else {
                                        $tmp[] = __("forward is %s at %s by %s", $forward['status'], $timestamp, $forward['to-addr']);
                                    }
                                }
                                $title = implode("&#013;", $tmp);
                                break;
                            case 'received':
                                $status = "&nbsp;";
                                break;
                            case 'missing-data':
                                $title = $history['History']['missing-data'][0];
                                break;
                            }
                        }
                        echo '<td class="status" '. (isset($title)? 'title="' . htmlspecialchars($title) . '"' : '') . '>'. $status.'</td>';
                        ?>
                        <td class="details"><?php echo htmlspecialchars($history['History']['message-content']); ?>&nbsp;</td>
                        <td class="date-time"><?php echo $this->Time->format('d/m/Y H:i:s', $history['History']['timestamp']); ?>&nbsp;</td>
                    <?php } ?>
                </tr>
                <?php endforeach;
            } ?>
         </tbody>
    </table>
    </div>
    </div>
</div>
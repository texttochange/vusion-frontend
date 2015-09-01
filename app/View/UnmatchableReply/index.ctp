<div class="admin-action">
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li>
        <?php echo $this->Html->link(__('Program List'),
            array('controller' => 'programs', 
                        'action' => 'index'));
        ?>
        </li>
        <li>
        <?php echo $this->Html->link(__('Exported Files'),
            array('action' => 'exported'));
        ?>
        </li>
    </ul>
</div>
</div>

<div class="admin-index unmatchable-replies index">
    <?php
    $contentTitle = __('Unmatchable Replies');
    $contentActions = array();
    $containsDataControlNav = true;
    $containsFilter = true;
    $controller = 'unmatchableReply';
    $urlParams = (isset($urlParams) ? $urlParams : "");

    $contentActions[] = $this->Html->tag(
        'span', 
        __('Filter'), 
        array('class' => 'ttc-button', 'name' => 'add-filter')); 
    $this->Js->get('[name=add-filter]')->event(
        'click',
        '$("#advanced_filter_form").show();
        createFilter();
        addStackFilter();');

    if ($unmatchableReplies != null) {
        $exportUrl = array(
            'controller' => 'unmatchableReply',
            'action' => 'export',
            '?' => $urlParams);
        if ($order != null) {
            $exportUrl['sort'] = key($order);
            $exportUrl['direction'] = $order[key($order)];
        }
        $contentActions[] = $this->AclLink->generateButtonFromUrl(
            __('Export'), $exportUrl, array('class' => 'ttc-button'));
    }

    echo $this->element('header_content', 
        compact('contentTitle', 'contentActions', 'containsFilter','containsDataControlNav', 'controller'));

    ?>
    <div class="ttc-table-display-area">
    <div class="ttc-table-scrolling-area display-height-size">
    <table class="unmatchable-reply" cellpadding="0" cellspacing="0">
        <thead>
            <tr>                                                                        
                <th class="phone"><?php echo $this->Paginator->sort('participant-phone', __('From'));?></th>
                <th class="direction"><?php echo $this->Paginator->sort('to', __('To'));?></th>
                <th class="content"><?php echo $this->Paginator->sort('message-content', __('Message'));?></th>
                <th class="date-time"><?php echo $this->Paginator->sort('timestamp', __('Time'));?></th>
             </tr>
        </thead>
        <tbody>

             <?php
             foreach($unmatchableReplies as $unmatchableReply):
             ?>
             <tr>
                 <?php

                 $prefix = $this->PhoneNumber->getInternationalPrefix(
                     $unmatchableReply['UnmatchableReply']['participant-phone'],
                     $countriesIndexes);
                 $from = $this->PhoneNumber->displayCode(
                     $unmatchableReply['UnmatchableReply']['participant-phone'],
                     $prefix,
                     $countriesIndexes);
                 echo '<td class="phone">'.$from.'&nbsp;</td>';
                 $to = $this->PhoneNumber->displayCode(
                     $unmatchableReply['UnmatchableReply']['to'],
                     $prefix,
                     $countriesIndexes);
                 echo '<td class="direction"">'.$to.'&nbsp;</td>';
                 ?>
                 <td class="content"><?php echo $unmatchableReply['UnmatchableReply']['message-content']; ?>&nbsp;</td>
                 <td class="date-time"><?php echo $this->Time->format('d/m/y H:i', $unmatchableReply['UnmatchableReply']['timestamp']); ?> (UTC)</td>
             </tr>
             <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    </div>
</div>

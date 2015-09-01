<div class="admin-action">
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
        <li><?php echo $this->AclLink->generateButton(__('Back to Programs'), null, 'programs', 'index'); ?></li>
    </ul>
</div>
</div>

<div class="admin-index index">
<div class="table" style="width:100%">
<div class="row">
<div class="cell">
    <?php
        $contentTitle           = __('Users Logs'); 
        $contentActions         = array();
        $containsDataControlNav = true;
        $containsFilter = true;
        $controller = 'userLogs';
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

        if ($userLogs != null) {
            $exportUrl = array(
                    'controller' => 'userLogs',
                    'action' => 'exportUserLog',
                    'ext' => 'csv',
                    '?' => $urlParams);
            $contentActions[] = $this->AclLink->generateButtonFromUrl(
                __('Export'),
                $exportUrl,
                array('class' => 'ttc-button'));
        }

        echo $this->element('header_content', compact(
            'controller', 'contentTitle', 'contentActions', 'containsDataControlNav', 'containsFilter'));
    ?>
    <div class="ttc-table-display-area">
    <div class="ttc-table-scrolling-area display-height-size">
        <table class="user-logs" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="user_logs_field"><?php echo $this->Paginator->sort('timestamp',__('Timestamp'));?></th>
                    <th class="user_logs_field"><?php echo $this->Paginator->sort('timezone', __('Timezone'));?></th>
                    <th class="user_logs_field"><?php echo $this->Paginator->sort('user-id',__('User'));?></th>
                    <th ><?php echo $this->Paginator->sort('program-database-name', __('Program'));?></th>
                    <th ><?php echo __('Event');?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userLogs as $userLog): ?>
                <tr>
                    <td class="user_logs_field"><?php echo $this->Time->format('d/m/Y H:i:s',$userLog['UserLog']['timestamp']); ?></td>
                    <td class="user_logs_field"><?php echo $userLog['UserLog']['timezone']; ?></td>
                    <td class="user_logs_field"><?php echo $userLog['UserLog']['user-name']; ?></td>
                    <td >
                    <?php
                    if (isset($userLog['UserLog']['program-name'])) {
                        echo $userLog['UserLog']['program-name'];
                    } else {
                        echo ' ';
                    }                    
                    ?>
                    </td>
                    <td ><?php echo $userLog['UserLog']['parameters']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>	
    </div>
    </div>
</div>
</div>
</div>
</div>


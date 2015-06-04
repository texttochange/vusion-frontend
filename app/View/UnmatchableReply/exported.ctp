<div class="admin-action">
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li>
        <?php echo $this->Html->link(__('Program List'),
            array('controller' => 'programs', 'action' => 'index'));
        ?>
        </li>
        <li>
        <?php echo $this->Html->link(__('Unmatchable Reply'),
            array('action' => 'index'));
        ?>
        </li>
    </ul>
</div>
</div>

<div class="admin-index unmatchable-replies index">
    <?php
    $contentTitle = __('Unmatchable Reply Exports');
    $contentActions = array();
    $containsDataControlNav = false;
    $containsFilter = false;
    $controller = 'unmatchableReply';
    $urlParams = (isset($urlParams) ? $urlParams : "");

    echo $this->element('header_content', 
        compact('contentTitle', 'contentActions', 'containsFilter','containsDataControlNav', 'controller'));
    ?>
    <div class="ttc-table-display-area">
    <div class="ttc-table-scrolling-area display-height-size">
    <table class="unmatchable-reply" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="file"><?php echo __('Export'); ?></th>
                <th class="size"><?php echo __('Status'); ?></th>
                <th class="date"><?php echo __('Created'); ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($files == array()) { ?>
            <tr>
                <td colspan=4><?php echo __("No export file found.") ?></td>
            </tr>
        <?php } else {?>   
        <?php foreach ($files as $file): ?>
            <tr>
                <td>
                    <?php
                    if ($file['Export']['filters'] == array()) {
                        echo __("All Unmatchable Replies");
                    } else {
                        if ($file['Export']['filters']['filter_operator'] == 'all') {
                            echo "<div>" . __("All Unmatchable Replies with:") ."</div>";
                        } else {
                            echo "<div>" . __("Unmatchable Replies with either:") ."</div>";
                        }
                        foreach ($file['Export']['filters']['filter_param'] as $filterParam) {
                            echo "<div>- " . $filterParam[1] . " " . $filterParam[2] . " " . (isset($filterParam[3]) ? $filterParam[3]:'') ."</div>";
                        }
                    }
                    ?>
                </td>
                <td>
                <?php
                switch ($file['Export']['status']) {
                    case 'success':
                        echo __("done, size: %s", $this->Number->toReadableSize($file['Export']['size']));
                        break;
                    case 'failed':
                        echo "<i title='". $file['Export']['failure-reason'] ."'>" . __("failed, contact support") . '</i>';
                        break;
                    case 'queued':
                        echo "<i>" . __("queued") . '</i>';
                        break;
                    case 'processing':
                        echo "<i>" . __("processing") . '</i>';
                        break;
                    case 'no-space':
                        echo "<i>" . __("no space, contact support") . '</i>';
                        break;
                    default:
                        echo "<i>" . __("error, contact support") . '</i>';
                }
                ?>
                </td>
                <td>
                <?php 
                echo $this->Time->niceShort(strtotime($file['Export']['timestamp']));
                ?>
                </td>
                <td>
                <?php
                if (!in_array($file['Export']['status'], array('queued', 'processing'))) {
                    if ($file['Export']['status'] == 'success') {
                        echo $this->AclLink->generateButton(
                            __('Download'),
                            null,
                            'unmatchableReply',
                            'download',
                            array('class'=>'ttc-button'),
                            null,
                            array('file' => basename($file['Export']['file-full-name'])));
                    }
                    echo $this->AclLink->generatePostLink(
                        __('Delete'),
                        null,
                        'unmatchableReply',
                        'deleteExport',
                        __('Are you sure you want to delete this export?'),
                        array('class'=>'ttc-button'),
                        $file['Export']['_id']);
                }
                ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php }; ?>
        </tbody>
    </table>
    </div>
    </div>
</div>
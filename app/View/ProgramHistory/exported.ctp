<div class="history index width-size">
    <?php
        $contentTitle   = __('History Exports');
        $contentActions = array();
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
    <div class="ttc-table-display-area" style="width:97%">
    <div class="ttc-table-scrolling-area display-height-size">
    <table class="participants" cellpadding="0" cellspacing="0">
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
                <td colspan=4><?php echo __("No export found.") ?></td>
            </tr>
        <?php } else {?>   
        <?php foreach ($files as $file): ?>
            <tr>
                <td>
                    <?php
                    if ($file['Export']['filters'] == array()) {
                        echo __("All Histories");
                    } else {
                        if ($file['Export']['filters']['filter_operator'] == 'all') {
                            echo "<div>" . __("All Histories with:") ."</div>";
                        } else {
                            echo "<div>" . __("Histories with either:") ."</div>";
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
                echo $this->Time->niceShort(
                    $this->Time->convert(
                        strtotime($file['Export']['timestamp']),
                        $programDetails['settings']['timezone']));
                ?>
                </td>
                <td>
                <?php
                if (!in_array($file['Export']['status'], array('queued', 'processing'))) {
                    if ($file['Export']['status'] == 'success') {
                        echo $this->AclLink->generateButton(
                            __('Download'),
                            $programDetails['url'],
                            'ProgramHistory',
                            'download',
                            array('class'=>'ttc-button'),
                            null,
                            array('file' => basename($file['Export']['file-full-name'])));
                    }
                    echo $this->AclLink->generatePostLink(
                        __('Delete'),
                        $programDetails['url'],
                        'programHistory',
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
<div class="participants index width-size">
    <div>
        <h3><?php echo __("Participant Exports"); ?></h3>
        <ul class="ttc-actions">
        <li></li>
        </ul>
    </div>
    <div class="ttc-table-display-area" style="width:100%">
    <div class="ttc-table-scrolling-area display-height-size">
    <table class="participants" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="file"><?php echo __('Export'); ?></th>
                <th class="size"><?php echo __('Size'); ?></th>
                <th class="date"><?php echo __('Created'); ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>

            </tr>
        </thead>
        <tbody>
        <?php if ($files == array()) { ?>
            <tr>
                <td colspan=3><?php echo __("No export found.") ?></td>
            </tr>
        <?php } else {?>   
        <?php foreach ($files as $file): ?>
            <tr>
                <td>
                    <?php
                    if ($file['Export']['filters'] == array()) {
                        echo __("All participants");
                    } else {
                        if (count($file['Export']['filters']['filter_param']) > 1) {
                            if ($file['Export']['filters']['filter_operator'] == 'all') {
                                echo "<div>" . __("All participants with:") ."</div>";
                            } else {
                                echo "<div>" . __("Participants with either:") ."</div>";
                            }
                        }
                        foreach ($file['Export']['filters']['filter_param'] as $filterParam) {
                            echo "<div>- " . $filterParam[1] . " " . $filterParam[2] . " " . (isset($filterParam[3]) ? $filterParam[3]:'') ."</div>";
                        }
                    }
                    ?>
                </td>
                <td><?php
                if ($file['Export']['status'] == 'success') {
                    echo $this->Number->toReadableSize($file['Export']['size']);
                } else {
                    if ($file['Export']['status'] == 'failed') {
                        echo "<i title='". $file['Export']['failure-reason'] ."'>" . $file['Export']['status'] . '</i>';
                    } else {
                        echo "<i>" . $file['Export']['status'] . '</i>';
                    }
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
                    echo $this->AclLink->generateButton(
                        __('Download'),
                        $programDetails['url'],
                        'ProgramParticipants',
                        'download',
                        array('class'=>'ttc-button'),
                        null,
                        array('file' => basename($file['Export']['file-full-name'])));
                    echo $this->AclLink->generatePostLink(
                        __('Delete'),
                        $programDetails['url'],
                        'programParticipants',
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
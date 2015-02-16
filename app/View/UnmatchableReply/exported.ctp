<div class="unmatchable-replies index width-size users-index">
    <ul class="ttc-actions"></ul>
    <h3><?php echo __('Exported File of Unmatchable Replies'); ?></h3>
    <div class="ttc-table-display-area" style="width:100%">
    <div class="ttc-table-scrolling-area display-height-size">
    <table class="unmatchable-reply" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="file"><?php echo __('File Name'); ?></th>
                <th class="size"><?php echo __('Size'); ?></th>
                <th class="date"><?php echo __('Created'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($fileCurrenltyExported) { ?>
            <tr>
                <td colspan=3><?php echo __("Vusion is currenlty exporting one or more set of data.") ?></td>
            </tr>
        <?php } ?>
        <?php if ($files == array()) { ?>
            <tr>
                <td colspan=3><?php echo __("No export file found.") ?></td>
            </tr>
        <?php } else {?>   
        <?php foreach ($files as $file): ?>
            <tr>
                <td>
                    <a href="<?php echo $this->Html->url(array('action' => 'download', '?' => array('file' => $file['name'])));?>">
                    <?php echo $file['name']; ?>
                    </a>
                </td>
                <td>
                <?php 
                echo $this->Number->toReadableSize($file['size']); 
                ?>
                </td>
                <td>
                <?php 
                echo $this->Time->niceShort($this->Time->convert($file['created'], 'UTC')) . ' (UTC)';
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
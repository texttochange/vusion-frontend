<div class="participants index width-size">
    <div>
        <h3><?php echo __('Exported Files of Participants'); ?></h3>
        <ul class="ttc-actions">
        <li></li>
        </ul>
    </div>
    <div class="ttc-table-display-area" style="width:100%">
    <div class="ttc-table-scrolling-area display-height-size">
    <table class="participants" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="file"><?php echo __('File Name'); ?></th>
                <th class="size"><?php echo __('Size'); ?></th>
                <th class="date"><?php echo __('Created'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($files == array()) { ?>
            <tr>
                <td colspan=2><?php echo __("No participant export file found.") ?></td>
            </tr>
        <?php } else {?>   
        <?php foreach ($files as $file): ?>
            <tr>
                <td>
                    <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'download', '?' => array('file' => $file['name'])));?>">
                    <?php echo $file['name']; ?></td>
                    </a>
                <td><?php echo $this->Number->toReadableSize($file['size']); ?></td>
                <td><?php 
                echo $this->Time->niceShort($this->Time->convert($file['created'], $programDetails['settings']['timezone'])); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php }; ?>
        </tbody>
    </table>
</div>
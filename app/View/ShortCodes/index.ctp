<div class="admin-action">
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
	<li><?php echo $this->Html->link(__('New ShortCode'), array('action' => 'add')); ?></li>
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
        $contentTitle           = __('ShortCodes'); 
        $contentActions         = array();
        $containsDataControlNav = true;
        
        echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav'));
    ?>
	
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
		<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
			<th class="phone"><?php echo $this->Paginator->sort('shortcode', __('Shortcode'));?></th>
			<th class="country"><?php echo $this->Paginator->sort('country', __('Country'));?></th>
			<th class="prefix"><?php echo $this->Paginator->sort('international-prefix', __('International Prefix'));?></th>
			<th class="support"><?php echo $this->Paginator->sort('support-customized-id', __('Support Customized Id'));?></th>
			<th class="support"><?php echo $this->Paginator->sort('supported-internationally', __('Supported Internationally'));?></th>
			<th class="support"><?php echo $this->Paginator->sort('max-character-per-sms', __('Maximum Characters per SMS'));?></th>
			<th class="action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>	
			<?php foreach ($shortcodes as $shortcode): ?>
			<tr class="<?php echo ($shortcode['ShortCode']['status']  == 'archived') ? 'archive' : '';?>">		
			<td class="phone"><?php echo $shortcode['ShortCode']['shortcode']; ?>&nbsp;</td>
			<td class="country"><?php echo $shortcode['ShortCode']['country']; ?>&nbsp;</td>
			<td class="prefix"><?php echo $shortcode['ShortCode']['international-prefix']; ?>&nbsp;</td>
			<td class="support"><?php echo ($shortcode['ShortCode']['support-customized-id']? __('yes'):__('no')); ?>&nbsp;</td>
			<td class="support"><?php echo ($shortcode['ShortCode']['supported-internationally']? __('yes'):__('no')); ?>&nbsp;</td>
			<td class="support"><?php
			$maxCharacterPerSms = (isset($shortcode['ShortCode']['max-character-per-sms'])? $shortcode['ShortCode']['max-character-per-sms'] :__('undefined'));
			echo $maxCharacterPerSms; ?>&nbsp;</td>
			<td class="actions action">
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $shortcode['ShortCode']['_id'])); ?>
			<?php
			if ($shortcode['ShortCode']['status']  == 'archived') {
			    echo $this->Form->postLink(__('Enable'), array('action' => 'unarchive', $shortcode['ShortCode']['_id']), null, __('Are you sure you want to enable the shortcode "%s"?', $shortcode['ShortCode']['shortcode']));
			    
			} else {
			    echo $this->Form->postLink(__('Disables'), array('action' => 'archive', $shortcode['ShortCode']['_id']), null, __('Are you sure you want to disable the shortcode "%s"?', $shortcode['ShortCode']['shortcode']));
			}
			?>
			</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
	</div>
	</div
</div>
</div>
</div>
</div>

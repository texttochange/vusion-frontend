<div class="programs form">
<?php
/*
$now = new DateTime('now');
echo "now time:";
print_r($now);
echo "<br/>";

$DateTimeZone = timezone_open('Africa/Kampala');
date_timezone_set($now, $DateTimeZone);

echo "now time in Africa/Kampala:";
print_r($now);
echo "<br/>";

$DateTimeZone = timezone_open('Africa/Nairobi');
date_timezone_set($now, $DateTimeZone);

echo "now time in Africa/Nairobi:";
print_r($now);
echo "<br/>";

$DateTimeZone = timezone_open('EAT');
date_timezone_set($now, $DateTimeZone);

echo "now time in EAT:";
print_r($now);
echo "<br/>";

$DateTimeZone = timezone_open('Africa/Kinshasa');
date_timezone_set($now, $DateTimeZone);

echo "now time in Africa/Kinshasa:";
print_r($now);
echo "<br/>";
*/
?>

<h3><?php echo __('Add Program'); ?></h3>
<?php echo $this->Form->create('Program');?>
	<fieldset>
		
	<?php
		echo $this->Form->input(__('name'));
		echo $this->Form->input(__('country'));
		?>
		<div class='input text'>
		<?php
		echo $this->Html->tag('label',__('Timezone'));
		$timezone_identifiers = DateTimeZone::listIdentifiers();
		$timezone_options = array();
		foreach($timezone_identifiers as $timezone_identifier) {
			$timezone_options[$timezone_identifier] = $timezone_identifier; 
		}
		echo $this->Form->select('timezone', $timezone_options);
		?>
		</div>
		<?php
		echo $this->Form->input('url');
		echo $this->Form->input('database');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Programs'), array('action' => 'index'));?></li>
	</ul>
</div>

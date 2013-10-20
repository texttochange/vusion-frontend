<?php 
$this->Html->script("jquery.handsontable-0.9.18.full.js", array("inline" => false));
$this->Html->script("ttc-table.js", array("inline" => false))
?>
<div class='content_variables index'>
    <div class="ttc-page-title">
        <h3><?php echo __('Content Variables');?></h3>
		<div class="tabs">
    	    <ul>
    	    <li>
                <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index')) ?>" >
                    <label><?php echo __("Keys/Values") ?></label>
                </a>
            </li>
    	    <li class="selected">
    	        <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'indexTable')) ?>" >
                    <label><?php echo __("Tables") ?></label>
                </a>
            </li>
    	    </ul>
    	</div>
        <ul class="ttc-actions">
		    <li><?php echo $this->Html->link(__('New'), array('program'=>$programDetails['url'], 'action' => 'addTable'), array('class' => 'ttc-button')); ?></li>
		</ul>	
	</div>	    
    <div class="ttc-data-control">
  
	<div id="data-control-nav" class="ttc-paging paging">
	<?php
	echo "<span class='ttc-page-count'>";
	echo $this->Paginator->counter(array(
	    'format' => __('{:start} - {:end} of {:count}')
	    ));
	echo "</span>";
	echo $this->Paginator->prev('<', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'prev disabled'));
	echo $this->Paginator->next(' >', array('url'=> array('program' => $programDetails['url'], '?' => $this->params['url'])), null, array('class' => 'next disabled'));
	?>
	</div>
  </div>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table class="content-variable-tables" cellpadding="0" cellspacing="0">
	    <thead>
	        <tr>
			    <th class="name"><?php echo $this->Paginator->sort('name', __('Name'), array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="values"><?php echo __('Table')?></th>
			    <th class="actions action"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		    <?php
		    $tableIndex = 0;
		    $canEditTableValue = false;
		    if ($this->AclLink->_allow('controllers/ProgramContentVariables/editTableValue')) {
		        $canEditTableValue = true;
		    }
		    foreach ($contentVariableTables as $contentVariableTable): ?>
		    <tr>
		        <td><?php echo $contentVariableTable['ContentVariableTable']['name'] ?></td>
		        <td ><?php
		        $elementId = "table-" . $tableIndex++;
		        echo '<div id="'.$elementId.'"/>';
		        $lastColKey = 0;
		        foreach($contentVariableTable['ContentVariableTable']['columns'] as $column) {
		            if ($column['type'] !== "key") {
		                continue;
		            }
		            $lastColKey++;
		        }
		        $numberOfRows = count($contentVariableTable['ContentVariableTable']['columns'][0]['values']);
		        $this->Js->get('document')->event('ready',
		            'createTable(
    		            "#'.$elementId.'", 
    		            {
    		            startRows: 5,
    		            startCols: 10,
    		            width: 700,
    		            height: '.($numberOfRows*40+30).',
    		            fixedRowsTop: 1,
    		            fixedColumnsLeft: '.$lastColKey.', 
    		            strechH: \'all\',
    		            cells: function(row, col, prop) {
    		              var cellProperties = {};
    		              if (row === 0 || col < '.$lastColKey.') { 
     		                  cellProperties.renderer = keyRenderer;
                          } else {
                              cellProperties.renderer = '.($canEditTableValue? 'valueRenderer' : 'valueReadOnlyRenderer').';
    		              }
    		              return cellProperties;
    		              },
    		            afterInit: setTableTitles,
    		            afterRender: setTableTitles,
    		            data: fromVusionToHandsontableData(\''.json_encode($contentVariableTable['ContentVariableTable']['columns']).'\'),
    		            afterChange: function (change,source) {
    		              if (change == null) {
    		                  return;
    		              }
    		              keys = getKeysFromCellPosition(this, change[0][0], change[0][1]);
    		              formData = {"ContentVariable": {"keys": keys, "value": change[0][3]}};
    		              data = JSON.stringify(formData, null, "\t");
    		              $.ajax({
    		                  url: "'.$this->Html->url(array('program'=> $programDetails['url'], 'action'=>'editTableValue', 'ext'=>'json')).'",
    		                  contentType: "application/json; charset=utf-8",
    		                  dataType: "json",
    		                  type: "POST",
    		                  data: data,
    		                  callbackData: { "table": "'.$elementId.'",
                                              "change": change[0]},
    		                  success: saveValueCallback
    		                  });
    		              }
    		            }
    		        )'
                 );
		        ?></td>
		        <td class="actions action">
		            <?php echo $this->Html->link(__('Edit'), array('program' => $programDetails['url'], 'action' => 'editTable', $contentVariableTable['ContentVariableTable']['_id'])); ?>
		            <?php echo $this->Form->postLink(
		                __('Delete'),
		                array('program' => $programDetails['url'],
		                    'action' => 'deleteTable',
		                    $contentVariableTable['ContentVariableTable']['_id']),
		                null,
		                __('Are you sure you want to delete "%s"?', $contentVariableTable['ContentVariableTable']['name'])); ?>
		        </td>
		    </tr>
		   <?php endforeach; ?>
		 </tbody>
	</table>
	</div>
	</div>
</div>

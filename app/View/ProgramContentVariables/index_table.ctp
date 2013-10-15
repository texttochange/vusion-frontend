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
		        $this->Js->get('document')->event('ready',
		            'function myRenderer(instance, td, row, col, prop, value, cellProperties) {
		                Handsontable.TextCell.renderer.apply(this, arguments);
		                if (row === 0) {
		                    td.className = "key";
		                    td.setAttribute("title", "This is a readonly key.")
		                    cellProperties.readOnly = true;
		                } else if (col < '.$lastColKey.'){
		                    td.className = "key";
		                    td.setAttribute("title", "This is a readonly key.")
		                    cellProperties.readOnly = true;
		                } else {
    		                td.setAttribute("title", "This value is editable and can be used in message.");
    		                td.className = "value";
		                }
		            }
		            createTable(
    		            "#'.$elementId.'", 
    		            {
    		            startRows: 5,
    		            startCols: 10,
    		            width: 700,
    		            height: 120,
    		            strechH: \'all\',
    		            cells: function(row, col, prop) {
    		              var cellProperties ={};
    		              cellProperties.renderer = myRenderer;
    		              return cellProperties;
    		              },
    		            data: fromVusionToHandsontableTable(\''.json_encode($contentVariableTable['ContentVariableTable']['columns']).'\'),
    		            afterChange: function (change,source) {
    		              if (change == null) {
    		                  return;
    		              }
    		              var row = change[0][0], 
    		                  col = change[0][1],
    		                  i = 0,
    		                  keys = [];
    		              while (this.getCell(row, i).className ==="key") {
    		                  keys.push(this.getDataAtCell(row, i));
    		                  i++;
    		              }
    		              keys.push(this.getDataAtCell(0, col));
    		              formData = {"ContentVariable": {"keys": keys, "value": change[0][3]}};
    		              data = JSON.stringify(formData, null, "\t");
    		              var callBack = function(someData) {
    		                  return function(data, textStatus, someData) {
    		                      alert("saved data "+data+" of from change "+someData);
    		                  }
    		              }
    		              $.ajax({
    		                  url: "'.$this->Html->url(array('program'=> $programDetails['url'], 'action'=>'editTableValue', 'ext'=>'json')).'",
    		                  contentType: "application/json; charset=utf-8",
    		                  dataType: "json",
    		                  type: "POST",
    		                  data: data,
    		                  callbackData: { "table": "'.$elementId.'",
                                              "change": change[0]},
    		                  complete: function(data) {
    		                          var cellClass = "cell-failure";
    		                          if (data.status == "ok") {
    		                              cellClass = "cell-success";
    		                          }
    		                          var cell = $("#"+this.callbackData.table).data("handsontable").getCell(this.callbackData.change[0], this.callbackData.change[1]);
    		                          $(cell).addClass(cellClass);
    		                      },
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

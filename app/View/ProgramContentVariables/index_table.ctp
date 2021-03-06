<?php 
    $this->RequireJs->scripts(array('table'));
?>
<div class='content_variables index'>
   <?php
       $contentTitle           = __('Content Variables'); 
       $contentActions         = array();
       $containsDataControlNav = true;
       $containsSpan           = 'tables';
       $controller             = 'programContentVariables';
       
       $contentActions[] = $this->Html->link(__('+ New'),
           array('program'=>$programDetails['url'],
               'action' => 'addTable'),
           array('class' => 'ttc-button'));
       
       echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav', 'controller', 'containsSpan'));
    ?>
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
		        $this->RequireJs->runLine('
		            createTable(
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
    		                for (var index in change) {
    		                    keys = getKeysFromCellPosition(this, change[index][0], change[index][1]);
    		                    formData = {"ContentVariable": {"keys": keys, "value": change[index][3]}};
    		                    data = JSON.stringify(formData, null, "\t");
    		                    $.ajax({
    		                        url: "'.$this->Html->url(array('program'=> $programDetails['url'], 'action'=>'editTableValue', 'ext'=>'json')).'",
    		                        contentType: "application/json; charset=utf-8",
    		                        dataType: "json",
    		                        type: "POST",
    		                        data: data,
    		                        callbackData: { "table": "'.$elementId.'",
                                                    "change": change[index]},
                                    success: saveValueCallback,
                                    error: vusionAjaxError
                                });
                            }
                        }
    		            })'
                 );
		        ?></td>
		        <td class="actions action">
		            <?php echo $this->Html->link(__('Edit'), array(
		                'program' => $programDetails['url'],
		                'action' => 'editTable', $contentVariableTable['ContentVariableTable']['_id'])); ?>
		            <?php 
		            $exportUrl = $this->Html->url(array(
		                'program' => $programDetails['url'],
		                'controller' => 'programContentVariables',
		                'id' =>  $contentVariableTable['ContentVariableTable']['_id'],
		                'action'=>'export'));
		            
		            echo $this->Html->tag(
		                'span',
		                __('Export'), array(
		                    'class' => 'ttc-button ttc-button-export',
		                    'url' => $exportUrl,
		                    'name' => $contentVariableTable['ContentVariableTable']['_id']));
		            $this->Js->get('[name='.$contentVariableTable['ContentVariableTable']['_id'].']')->event('click',
		                'generateExportDialogue(this);');
		            ?>
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

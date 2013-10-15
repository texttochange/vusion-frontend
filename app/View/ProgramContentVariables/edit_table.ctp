<?php 
$this->Html->script("jquery.handsontable-0.9.18.full.js", array("inline" => false));
$this->Html->script("ttc-table.js", array("inline" => false));
?>
<div class="content_variables form width-size">
    <div class="ttc-page-title">
        <h3><?php echo __('Edit Content Variable'); ?></h3>
        <div class="tabs">
    	    <ul>
    	    <li>
                 <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'index')) ?>" >
                     <label><?php echo __("Keys/Value") ?></label>
                 </a>
            </li>
    	    <li class="selected">
    	         <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'indexTable')) ?>" >
    	             <label><?php echo __("Table") ?></label>
                 </a>
            </li>
    	    </ul>
    	</div>
        <ul class="ttc-actions">
            <li>
            <?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
            <span class="actions">
            <?php
            echo $this->Html->link( __('Cancel'), 
                array(
                    'program' => $programDetails['url'],
                    'action' => 'indexTable'	           
                    ));
            ?>
            </span>
            </li>
            <?php $this->Js->get('#button-save')->event('click', '$("#content-variable-table").submit()' , true);?>
        </ul>
	</div>
    <div class="ttc-display-area">
        <form id="content-variable-table" action="javascript:saveTable()">
        <fieldset>
            <?php $contentVariableTable = $this->data; ?>
           <div class="input text">
               <label for="ContentVariableTableName"><?php echo __('Name'); ?> <label/>
               <input name="ContentVariableTable.name" type="text" id="ContentVariableTableName" value="<?php echo $contentVariableTable['ContentVariableTable']['name']?>"/>
           </div>
           <div call="input text required">
           <label><?php echo __('Table'); ?></label>
           <div id="columns" style="padding-left:0px; margin-bottom:0px"/>
           <?php 
           $this->Js->get('document')->event('ready',
               'createTable(
                     "#columns", 
                     {startRows: 5,
                      startCols: 10,
                      minSpareRows: 1,
                      minSpareCols: 1,
                      width: 700,
                      height: 120,
                      strechH: \'all\',
                      contextMenu: [\'row_above\', \'row_below\', \'remove_row\', \'col_left\', \'col_right\', \'remove_col\'],
                      data: fromVusionToHandsontableTable(\''.json_encode($contentVariableTable['ContentVariableTable']['columns']).'\'),
                      }
                     )');
           ?>
           </div>
        </fieldset>
        <?php echo $this->Form->end(__('Save')); ?>
        </form>
   </div>
</div>

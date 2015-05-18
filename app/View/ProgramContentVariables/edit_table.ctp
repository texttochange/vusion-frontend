<?php 
$this->Html->script("jquery.handsontable-0.9.18.full.js", array("inline" => false));
$this->Html->script("ttc-table.js", array("inline" => false));
?>
<div class="content_variables form width-size">
    <?php
        $contentTitle   = __('Edit Content Variable Table'); 
        $contentActions = array();
        
        $contentActions[] = $this->Html->link( __('Cancel'), 
        array(
          'program' => $programDetails['url'],
          'action' => 'indexTable'),
        array('class' => 'ttc-button'));
        
        $contentActions[] = $this->Html->link(__('Save'),
            array(),
            array('class'=>'ttc-button',
                'id' => 'button-save'));
        $this->Js->get('#button-save')->event('click',
            '$("#content-variable-table").submit()' , true);
		
		echo $this->element('header_content', compact('contentTitle', 'contentActions'));
    ?>
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
           $numberOfRows = count($contentVariableTable['ContentVariableTable']['columns'][0]['values']);     
           $this->Js->get('document')->event('ready',
               'createTable(
                     "#columns", 
                     {startRows: 5,
                      startCols: 10,
                      minSpareRows: 1,
                      minSpareCols: 1,
                      width: 700,
                      height: '.($numberOfRows*40+30).',
                      strechH: \'all\',
                      contextMenu: [\'row_above\', \'row_below\', \'remove_row\', \'col_left\', \'col_right\', \'remove_col\'],
                      data: fromVusionToHandsontableData(\''.json_encode($contentVariableTable['ContentVariableTable']['columns']).'\'),
                      }
                     )');
           ?>
           </div>
        </fieldset>
        <?php echo $this->Form->end(__('Save')); ?>
        </form>
   </div>
</div>

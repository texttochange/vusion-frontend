<?php 
$this->Html->script("jquery.handsontable-0.9.18.full.js", array("inline" => false));
$this->Html->script("ttc-table.js", array("inline" => false));
?>
<div class="content_variables form width-size">
    <div class="ttc-page-title">
        <h3><?php echo __('Add Content Variable'); ?></h3>
        <div class="ttc-data-control">
		<span class="tabs">
		    <ul>
		    <li>
			 <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'add')) ?>" >
			     <label><?php echo __("Keys/Value") ?></label>
			 </a>
		    </li>
		    <li class="selected">
			 <a href="<?php echo $this->Html->url(array('program' => $programDetails['url'], 'action' => 'indexTable')) ?>" >
			     <label><?php echo __("Table") ?></label>
			 </a>
		    </li>
		    </ul>
		</span>
		<ul class="ttc-actions">        
		<li>
		<?php echo $this->Html->tag('span', __('Save'), array('class'=>'ttc-button', 'id' => 'button-save')); ?>
		<span class="actions">
		<?php
		echo $this->Html->link( __('Cancel'), 
		    array(
			'program' => $programDetails['url'],
			'action' => 'indexTable'	           
			)
		    );
		?>
		</span>
		</li>
		<?php $this->Js->get('#button-save')->event('click', '$("#content-variable-table").submit()' , true);?>
		</ul>
        </div>
    <div class="ttc-display-area">
        <form id="content-variable-table" action="javascript:saveTable()">
        <fieldset>
           <div class="input text">
               <label for="ContentVariableTableName"><?php echo __('Name'); ?> </label>
               <input name="ContentVariableTable.name" type="text" id="ContentVariableTableName"/>
           </div>
           <div class="input">
           <label><?php echo __('Table'); ?></label>
           <div id="columns" style="padding-left:0px; margin-bottom:0px"/>
           <?php 
           $this->Js->get('document')->event('ready',
               'createTable(
                     "#columns", 
                     {startRows: 5,
                      startCols: 10,
                      width: 700,
                      height: 120,
                      strechH: \'all\',
                      contextMenu: [\'row_above\', \'row_below\', \'remove_row\', \'col_left\', \'col_right\', \'remove_col\'],
                      /*cells: function(row, col, prop) {
                          var cellProperties ={};
                          cellProperties.renderer = tableRenderer;
                          return cellProperties;
                      }*/
                      }
                     )');
           ?>
           </div>
        </fieldset>
        <?php echo $this->Form->end(__('Save')); ?>
        </form>
   </div>
</div>

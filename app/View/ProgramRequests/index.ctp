<div class='Program Requests index'>
   <?php
       $contentTitle   = __('Requests'); 
       $contentActions = array();
       $containsDataControlNav = true;
       $controller = 'programRequests';
       
       $contentActions[] = $this->Html->link(__('+ New Request'),
           array('program'=>$programDetails['url'],
               'controller' => $controller,
               'action' => 'add'),
           array('class' => 'ttc-button'));
       
       echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav', 'controller'));
   ?>
	<div class="ttc-table-display-area">
	<div class="ttc-table-scrolling-area display-height-size">
	<table class="requests" cellpadding="0" cellspacing="0">
	    <thead>	
	        <tr>
			    <th class="keywords"><?php echo $this->Paginator->sort('keyword', null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="responses"><?php echo $this->Paginator->sort('responses', null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="do"><?php echo $this->Paginator->sort('do', null, array('url'=> array('program' => $programDetails['url'])));?></th>
			    <th class="action-requests"><?php echo __('Actions');?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		    foreach ($requests as $request): ?>
		    <tr>
		        <td class="direction"><?php echo $request['Request']['keyword']; ?>&nbsp;</td>
		        <td class="responses">
		            <?php 
		            if (isset($request['Request']['responses']))
		            foreach ($request['Request']['responses'] as $response) {
		                echo $this->Html->tag('div', '"'.$response['content'].'"');
		            } 
		            ?>
		        </td>
		        <td class="status">
		            <?php 
		            if (isset($request['Request']['actions']))
		            foreach ($request['Request']['actions'] as $action) {
		                $info = $action['type-action'];
		                if ($action['type-action']=='enrolling')
		                    foreach ($currentProgramData['dialogues'] as $dialogue) {
		                        if ($dialogue['dialogue-id'] == $action['enroll']) {
		                            $info = __("Enrolling dialogue %s", $dialogue['Active']['name']);
		                            break;
		                        }
		                    }
		                    echo $this->Html->tag('div', $info);
		            } 
		            ?>
		        </td>
		        <td class="action-requests actions">
		            <?php echo $this->Html->link(__('Edit'), array('program'=>$programDetails['url'], 'action' => 'edit', $request['Request']['_id'])); ?>
		            <?php echo $this->Form->postLink(__('Delete'), array('program'=>$programDetails['url'], 'action' => 'delete', $request['Request']['_id']), null,
			                                __('Are you sure you want to delete %s?', $request['Request']['keyword'])); ?>
			    </td>
			    </tr>
			        <?php endforeach; ?>
			  </tbody>
			</table>
		</div>
	</div>	
</div>
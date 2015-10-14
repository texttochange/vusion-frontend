<?php
    $this->RequireJs->scripts(array("jquery", "graph-nvd3", "moment"));
?>
<div class='Program Home index'>
    <?php
        $contentTitle           = __('Sending Next'); 
        $contentActions         = array();
        $containsDataControlNav = false;
        $controller             = 'programHome';
        
        //echo $this->element('header_content', compact('contentTitle', 'contentActions', 'containsDataControlNav', 'controller'));
   ?>
	<div class="ttc-table-display-area" style='border:none;'>
	<div class="ttc-table-scrolling-area display-height-size">
        <div class="table">
            <div class='row'>
                <div class='cell'>
                    <div>
                    <h4>Message History</h4>
            		<div id="history-brief" style="width:500px"></div>
                    </div>
                    <div>
                    <h4>Participant Count</h4>
                    <div id="history-brief2" style="width:500px"></div>
                    </div>
                </div>
            
                <div class='cell'>
                    <div>
                        <h4>Future messages</h4>
                        <div id="history-brief3" style="width:500px"/>
                    </div>
                </div>
            </div>
        </div>
	<?php
		$this->RequireJs->runLine('$("#history-brief").history({"program": "'.$programDetails['url'].'"});');
        $this->RequireJs->runLine('$("#history-brief2").history({"program": "'.$programDetails['url'].'"});');
        $this->RequireJs->runLine('$("#history-brief3").history({"program": "'.$programDetails['url'].'"});');

        $this->RequireJs->runLine('$("#schedule-brief").schedule({"program": "'.$programDetails['url'].'"});');
	?>
	</div>
	</div>
</div>
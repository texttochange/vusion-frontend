<?php
    $this->RequireJs->scripts(array("jquery", "graph-nvd3", "moment"));
?>
<div class='Program Home index dashboard'>
	<div class="ttc-table-display-area" style='border:none;padding-top:0px'>
	<div class="ttc-table-scrolling-area display-height-size">
        <div class='table' style='width:100%'>
            <div class='row' style='height:10px'></div>
            <div class='row'>
                <div class='cell graph-cell'>
                    <div class='caption' style='padding-right:10px'> 
                       <img src='/img/message-icon-20.png' style='height:10px'/>
                        <span><?php echo __('View messages over past') ?></span>
                        <select id="history-brief-selector">
                          <option value="week">week</option>
                          <option value="month">month</option>
                          <option value="year">year</option>
                        </select>
                    </div>
                    <div id="history-brief" class="graph">
                        <img src="/img/ajax-loader.gif">
                    </div>
                    <?php $this->RequireJs->runLine('
                        $("#history-brief").history({"program": "'.$programDetails['url'].'"});');
                    ?>
                </div>
                <div class='cell graph-cell'>
                    <div class='caption'> 
                        <img src='/img/schedule-icon-14.png'/>
                        <span><?php echo __('View schedule item(s) over next'); ?></span>
                        <select id="schedule-brief-selector">
                          <option value="day">day</option>
                          <option selected value="week">week</option>
                          <option value="month">month</option>
                        </select>
                    </div>
                    <div id="schedule-brief" class='graph' style=''/>
                        <img src="/img/ajax-loader.gif"> 
                    </div>
                    <?php
                        $this->RequireJs->runLine('$("#schedule-brief").schedule({"program": "'.$programDetails['url'].'"});');
                    ?>
                </div>
            </div>
            <div class='row' style='height:30px'></div>
            <div class='row'>
                <div class='cell graph-cell'>
                    <div class='caption' style='padding-right:10px'> 
                        <img src='/img/participant-icon-14.png' style='padding-left:3px'/>
                        <span><?php echo __('View participant(s) over past') ?></span>
                        <select id="participant-brief-selector">
                          <option value="week">week</option>
                          <option value="month">month</option>
                          <option value="year">year</option>
                        </select>
                    </div>
                    <div id="participant-brief" class='graph'>
                        <img src="/img/ajax-loader.gif" style='loader'>
                    </div>
                    <?php 
                        $this->RequireJs->runLine('$("#participant-brief").participant({"program": "'.$programDetails['url'].'"});');
                    ?>
                </div>
                <div class='cell graph-cell'>
                    <div class='most-actives'>
                        <div class='caption'> 
                            <span><?php echo __('View most active receivers over past') ?></span>
                            <select id="most-active-selector">
                              <option value="day"><?php echo __('over past day'); ?></option>
                              <option value="week"><?php echo __('over past week'); ?></option>
                              <option value="month"><?php echo __('over past month'); ?></option>
                              <option value="year"><?php echo __('over past year'); ?></option>
                              <option value="program-start"><?php echo __('since program started'); ?></option>
                            </select>
                        </div>
                        <div style='padding:9px 0px 0px 30px'>
                        <div class='table' style='width:100%'>
                            <div id='most-active' class='row' >
                                <div class='cell title list-header' style='width:47%'><?php echo __('Dialogues'); ?>:</div>
                                <div class='cell' style='width:5%;min-width:2px'></div>
                                <div class='cell title list-header' style='width:47%'><?php echo __('Requests'); ?>:</div>        
                            </div>
                            <div class='row'>
                                <div class='cell'>
                                    <div id="most-active-dialogue">
                                       <img src="/img/ajax-loader.gif" style='loader'>
                                    </div>
                                </div>
                                <div class='cell'></div>
                                <div class='cell'>
                                    <div id="most-active-request">
                                         <img src="/img/ajax-loader.gif" style='loader'>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $this->RequireJs->runLine('$("#most-active").mostActive({"program": "'.$programDetails['url'].'"})')
                            ?>
                        </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
	</div>
</div>
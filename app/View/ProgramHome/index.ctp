<?php
    $this->RequireJs->scripts(array("jquery", "graph-nvd3", "moment", "dropit"));
?>
<div class='Program Home index dashboard'>
	<div class="ttc-table-display-area" style='border:none;padding-top:0px'>
	<div class="ttc-table-scrolling-area display-height-size">
        <div class='table'>
            <div class='row'>
                <div class='cell'>
                    <h3 style='margin-bottom:0px'><?php echo __('Dashboard') ?></h3>
                </div>
            </div>
            <div class='row'>
                <div class='cell' style='text-align:right'>
                    <span>View past</span>
                    <select id="history-brief-selector">
                      <option value="week">week</option>
                      <option value="month">month</option>
                      <option value="year">year</option>
                    </select>
                    <?php $this->RequireJs->runLine('
                        $("#history-brief").history({"program": "'.$programDetails['url'].'"});');
                    ?>
                    <div id="history-brief" class="graph">
                        <img src="/img/ajax-loader.gif">
                    </div>
                </div>
                <div class='cell' style='text-align:right'>
                    <span>View next</span>
                    <select id="schedule-brief-selector">
                      <option value="day">day</option>
                      <option selected value="week">week</option>
                      <option value="month">month</option>
                    </select>
                    <?php
                        $this->RequireJs->runLine('$("#schedule-brief").schedule({"program": "'.$programDetails['url'].'"});');
                    ?>
                    <div id="schedule-brief" class='graph' style='margin-left:20px'/>
                        <img src="/img/ajax-loader.gif"> 
                    </div>
                </div>
            </div>
            <div class='row' style='height:10px'>

            </div>
            <div class='row'>
                <div class='cell' style='text-align:right' >
                    <span>View past</span>
                    <select id="participant-brief-selector">
                      <option value="week">week</option>
                      <option value="month">month</option>
                      <option value="month">year</option>
                    </select>
                    <?php 
                        $this->RequireJs->runLine('$("#participant-brief").participant({"program": "'.$programDetails['url'].'"});');
                    ?>
                    <div id="participant-brief" class='graph'>
                        <img src="/img/ajax-loader.gif" style='loader'>
                    </div>
                </div>
            </div>
        </div>
	</div>
	</div>
</div>
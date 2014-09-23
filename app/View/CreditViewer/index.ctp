<?php
$this->Html->script("jstree.min.js", array("inline" => false));
?>
<div class="credit-logs index users-index">
    <ul class="ttc-actions">
        <li>
            <?php
                $exportUrl = $this->Html->url(array('controller' => 'creditViewer', 'action'=>'export'));
                echo $this->Html->tag(
                    'span', 
                    __('Export'), 
                    array('class' => 'ttc-button', 'name' => 'export', 'url' => $exportUrl)); 
                $this->Js->get('[name=export]')->event('click',
                    'generateExportDialogue(this);');
            ?>
        </li>
    </ul>

	<h3><?php echo __('Credit Viewer');?></h3>
    <div>
    <?php
    $predefinedTimeframes = array(
        'today' => __("today"),
        'yesterday' => __("yesterday"),
        'current-month' => __("current month"),
        'last-month' => __("last month"));
    $dateTimeframeClass = '';
    $predefinedTimeframeClass = '';
    if ((isset($timeframeParams['date-from']) && $timeframeParams['date-from'] != '') ||
        (isset($timeframeParams['date-to']) && $timeframeParams['date-to'] != '')) {
        $dateTimeframeClass = 'selected';
    } else if (isset($timeframeParams['predefined-timeframe']) && $timeframeParams['predefined-timeframe'] != '') {
        $predefinedTimeframeClass = 'selected';
    }
    echo '<div class="timeframe">';
    echo '<div id="timeframe-caption" class="caption">';
    if ($timeframeParams['predefined-timeframe'] != '') {
        $caption = __("Showing credits of %s", $predefinedTimeframes[$timeframeParams['predefined-timeframe']]);
    } else if ($timeframeParams['date-from'] == '' && $timeframeParams['date-to'] == '') {
        $caption = __("Showing all credits");
    } else if ($timeframeParams['date-from'] != '' && $timeframeParams['date-to'] == '') {
        $caption = __("Showing credits from %s to now", $timeframeParams['date-from']);
    } else if ($timeframeParams['date-from'] == '' && $timeframeParams['date-to'] != '') {
        $caption = __("Showing credits from the begining of times to %s", $timeframeParams['date-to']);
    } else {
        $caption = __("Showing credits from %s to %s", $timeframeParams['date-from'], $timeframeParams['date-to']);
    }
    echo $this->Html->tag('span', $caption);
    echo $this->Html->tag('span', __('Change'), array('class' => 'ttc-button', 'id' => 'change-timeframe'));
    echo '</div>';
    echo '<div id="timeframe-form" style="display:none">';
    echo $this->Form->create(
        false, array(
            'type' => 'get',
            'class' => 'timeframe-form',
            ));
    echo $this->Html->tag('span', __("Calculate credits of"));
    echo "<span class='timeframe predefined-timeframe ".$predefinedTimeframeClass."'>";
    echo $this->Form->input(
        'predefined-timeframe',
        array(
            'options' => $predefinedTimeframes,
            'empty' => _('choose...'),
            'div' => false,
            'label' => false,
            'value' => (isset($timeframeParams['predefined-timeframe']) ? $timeframeParams['predefined-timeframe']: ''),
            ));
    echo "</span>";
    echo $this->Html->tag('span', __("or"));
    echo "<span class='timeframe date-timeframe ".$dateTimeframeClass."'>";
    echo $this->Form->input(
        'date-from',
        array(
            'id' => 'date-from',
            'label' => __("from"),
            'style' => 'width:120px',
            'div' => false,
            'value' => (isset($timeframeParams['date-from']) ? $timeframeParams['date-from']: '')
            ));
    echo $this->Form->input(
        'date-to',
        array(
            'id' => 'date-to',
            'label' => "&nbsp;".__("to"),
            'style' => 'width:120px',
            'div' => false,
            'value' => (isset($timeframeParams['date-to']) ? $timeframeParams['date-to']: '')
            ));
    echo "</span>";
    echo $this->Form->end(array(
        'div' => false,
        'class' => 'submit',
        'label' => __('Calculate')));
    echo "</div>";
    echo "</div>";
    $this->Js->get("#change-timeframe")->event(
        'click',
        '$("#timeframe-caption").hide();
        $("#timeframe-form").show();');
    $this->Js->get(".date-timeframe")->event(
        'click',
        '
        if (event.target.id == "date-from") {
            $("#date-from:not(.hasDatepicker)").datepicker({dateFormat:"dd/mm/yy"}).datepicker("show");
        } else if (event.target.id == "date-to") {
            $("#date-to:not(.hasDatepicker)").datepicker({dateFormat:"dd/mm/yy"}).datepicker("show");
        }
        $(".predefined-timeframe").removeClass("selected").children("select").val(null);
        $(".date-timeframe").addClass("selected");
        ');
    $this->Js->get(".predefined-timeframe")->event(
        'click',
        '
        if ($(this).children("select").val() == "") {
            $(".predefined-timeframe").removeClass("selected");
            return;
        }
        $(".date-timeframe").removeClass("selected").children("input").val(null).datepicker("destroy").removeClass("hasDatepicker");
        $(this).addClass("selected");
        ');
    ?>
    </div>
	<div class="ttc-table-display-area">
	    <div class="ttc-table-scrolling-area display-height-size">
	         <div id="countries-credits-tree">
                <ul>
                <?php
                //Little function to help generating the tree leaves
                function getTreeElt($label, $value, $isLeave=true, $class=false, $icon=false) {
                    return "<li ". ($class? " class='".$class."' ":"") . ($icon? "data-jstree='{\"icon\":\"../img/".$icon."\"}'":"") . " >"
                            ."<span style='font-weight:normal'>". $label . ": ". $value ."</span>"
                            . ($isLeave? "</li>":"");
                }
                function getCountSummary($incomingCount, $outgoingCount) {
                    return " <span style='font-weight:normal'>".
                               "<span class='stat-summary' title='".__("Received")."'>".
                                    "<img src='../img/incoming-icon-14.png'/> ".$incomingCount.
                                "</span>".
                                 "<span class='stat-summary' title='".__("Sent")."'>".
                                    "<img src='../img/outgoing-icon-14.png' /> ".$outgoingCount.
                                "</span>".
                            "</span>";
                }

                foreach ($countriesCredits as $countryCredits) {
                    echo '<li data-jstree=\'{"icon":"../img/country-icon-20.png"}\'>'. $countryCredits['country'];
                    echo getCountSummary(
                            $this->Number->format($countryCredits['incoming']),
                            $this->Number->format($countryCredits['outgoing']));
                    echo '<ul>';
                    foreach ($countryCredits['codes'] as $code) {
                        echo '<li data-jstree=\'{"icon":"../img/code-icon-20.png"}\'>'. $code['code'];
                        echo getCountSummary(
                                $this->Number->format($code['incoming']), 
                                $this->Number->format($code['outgoing']));
                        echo '<ul>';
                        foreach ($code['programs'] as $programCreditLog) {
                            if ($programCreditLog['object-type'] == 'deleted-program-credit-log') {
                                $programIcon = "deleted-program-icon";
                                $programTitle = __("Deleted program");
                            } else {
                                $programIcon = "program-icon";
                                $programTitle = __("Running program");
                            }
                            echo '<li data-jstree=\'{"icon":"../img/'.$programIcon.'-20.png"}\' title=\''.$programTitle.'\'>'. $programCreditLog['program-name'];
                            echo getCountSummary(
                                    $this->Number->format($programCreditLog['incoming']), 
                                    $this->Number->format($programCreditLog['outgoing']));
                            echo '<ul>';
                            echo getTreeElt(__("Received"), $this->Number->format($programCreditLog['incoming']), true, false, "incoming-icon-20.png");
                            echo getTreeElt(__("Sent"), $this->Number->format($programCreditLog['outgoing']), false, false, "outgoing-icon-20.png");
                            echo '<ul>';
                            echo getTreeElt(__("Pending"), $this->Number->format($programCreditLog['outgoing-pending']), true, false, "status-pending-icon-20.png");
                            echo getTreeElt(__("Acked"), $this->Number->format($programCreditLog['outgoing-acked']), true, false, "status-acked-icon-20.png");
                            echo getTreeElt(__("NAcked"), $this->Number->format($programCreditLog['outgoing-nacked']), true, false, "status-nacked-icon-20.png");
                            echo getTreeElt(__("Delivered"), $this->Number->format($programCreditLog['outgoing-delivered']), true, false, "status-delivered-icon-20.png");
                            echo getTreeElt(__("Failed"), $this->Number->format($programCreditLog['outgoing-failed']), true, false, "status-failed-icon-20.png");
                            echo '</ul>';
                            echo '</li>';                            
                            echo '</ul></li>';
                        }
                        if ($code['garbage'] != array()) {
                            echo '<li data-jstree=\'{"icon":"../img/garbage-icon-20.png"}\'>'. __('Unmatchable Replies');
                            echo getCountSummary(
                                    $this->Number->format($code['garbage']['incoming']), 
                                    $this->Number->format($code['garbage']['outgoing']));
                            echo '<ul>';
                            echo getTreeElt(__("Received"), $code['garbage']['incoming'], true, false, "../img/incoming-icon-20.png");
                            echo getTreeElt(__("Sent"), $code['garbage']['outgoing'], true, false, "../img/outgoing-icon-20.png");
                            echo '</ul></li>';
                        }
                        echo '</ul></li>';
                
                    }
                    echo '</ul></li>';
                }
                $this->Js->get('document')->event('ready', '
                    $("#countries-credits-tree").jstree();');
                ?>
                </ul>
             </div>
        </div>
	</div>
</div>
<div class="admin-action">
   <div class="actions">
      <h3><?php echo __('Actions'); ?></h3>
            <ul>
                <li><?php echo $this->Html->link(__('Back to Admin menu'), array('controller' => 'admin', 'action' => 'index')); ?></li>
            </ul>
   </div>
 </div>

<?php
$this->Html->script("jstree.min.js", array("inline" => false));
?>
<div class="credit-logs index users-index">
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
                    $value = (is_numeric($value) ? $value : 0);
                    return "<li ". ($class? " class='".$class."' ":"") . ($icon? "data-jstree='{\"icon\":\"".$icon."\"}'":"") . " >"
                            ."<span style='font-weight:normal'>". $label . ": ". $value ."</span>"
                            . ($isLeave? "</li>":"");
                }

                foreach ($countriesCredits as $countryCredits) {
                    echo '<li data-jstree=\'{"icon":"../img/country-icon-20.png"}\'>'. 
                    __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", $countryCredits['country'], $countryCredits['incoming'], $countryCredits['outgoing']);
                    echo '<ul>';
                    foreach ($countryCredits['codes'] as $code) {
                        echo '<li data-jstree=\'{"icon":"../img/code-icon-20.png"}\'>'. 
                            __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", $code['code'], $code['incoming'], $code['outgoing']);
                        echo '<ul>';
                        foreach ($code['programs'] as $programCreditLog) {
                            if ($programCreditLog['object-type'] == 'deleted-program-credit-log') {
                                $programIcon = "deleted-program-icon";
                            } else {
                                $programIcon = "program-icon";
                            }
                            echo '<li data-jstree=\'{"icon":"../img/'.$programIcon.'-20.png"}\'>'. 
                                __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", $programCreditLog['program-name'], $programCreditLog['incoming'], $programCreditLog['outgoing']);
                            echo '<ul>';
                            echo getTreeElt(__("incoming"), $programCreditLog['incoming'], true, false, "../img/incoming-icon-20.png");
                            echo getTreeElt(__("outgoing"), $programCreditLog['outgoing'], false, false, "../img/outgoing-icon-20.png");
                            echo '<ul>';
                            echo getTreeElt(__("pending"), $programCreditLog['outgoing-pending'], true, false, "../img/status-pending-icon-20.png");
                            echo getTreeElt(__("acked"), $programCreditLog['outgoing-ack'], true, false, "../img/status-acked-icon-20.png");
                            echo getTreeElt(__("nacked"), $programCreditLog['outgoing-nack'], true, false, "../img/status-nacked-icon-20.png");
                            echo getTreeElt(__("delivered"), $programCreditLog['outgoing-delivered'], true, false, "../img/status-delivered-icon-20.png");
                            echo getTreeElt(__("failed"), $programCreditLog['outgoing-failed'], true, false, "../img/status-failed-icon-20.png");
                            echo '</ul>';
                            echo '</li>';                            
                            echo '</ul></li>';
                        }
                        if ($code['garbage'] != array()) {
                            echo '<li data-jstree=\'{"icon":"../img/garbage-icon-20.png"}\'>'. 
                            __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", __('Unmatchable Replies'), $code['garbage']['incoming'], $code['garbage']['outgoing']);
                            echo '<ul>';
                            echo getTreeElt(__("incoming"), $code['garbage']['incoming'], true, false, "../img/incoming-icon-20.png");
                            echo getTreeElt(__("outgoing"), $code['garbage']['outgoing'], true, false, "../img/outgoing-icon-20.png");
                            echo '</ul></li>';
                            echo '</ul>';
                            echo '</li>';
                        }
                    }
                    echo '</ul>';
                    echo '</li>';
                }
                $this->Js->get('document')->event('ready', '
                    $("#countries-credits-tree").jstree({
                  //      "core":{"themes":{"icons": false}}
                                });');
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

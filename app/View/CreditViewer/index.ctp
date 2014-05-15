<?php
$this->Html->script("jstree.min.js", array("inline" => false));
?>
<div class="credit-logs index users-index">
	<h3><?php echo __('Credit Viewer');?></h3>
    <div>
    <?php
    $dateTimeframeClass = '';
    $predefinedTimeframeClass = '';
    if ((isset($timeframeParams['date-from']) && $timeframeParams['date-from'] != '') ||
        (isset($timeframeParams['date-to']) && $timeframeParams['date-to'] != '')) {
        $dateTimeframeClass = 'selected';
    } else if (isset($timeframeParams['predefined-timeframe']) && $timeframeParams['predefined-timeframe'] != '') {
        $predefinedTimeframeClass = 'selected';
    }
    echo $this->Form->create(
        false, array(
            'type' => 'get',
            'id' => 'timeframe-form',
            'class' => 'timeframe-form'));
    echo $this->Html->tag('span', __("Calculate credits"));
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
    echo $this->Html->tag('span', __(" or"));
    echo "<span class='timeframe predefined-timeframe ".$predefinedTimeframeClass."'>";
    echo $this->Form->input(
        'predefined-timeframe',
        array(
            'options' => array(
                'current-month' => __("current month"),
                'last-month' => __("last month")),
            'empty' => _('choose...'),
            'div' => false,
            'label' => false,
            'value' => (isset($timeframeParams['predefined-timeframe']) ? $timeframeParams['predefined-timeframe']: ''),
            ));
    echo "</span>";
    echo $this->Form->end(array(
        'div' => false,
        'class' => 'submit',
        'label' => __('Calculate')));
    $this->Js->get("document")->event(
        'ready',
        '$("input[name*=\"date-\"]").datepicker({dateformat:"dd/mm/yy"});');
    $this->Js->get(".date-timeframe")->event('click','
        $(".predefined-timeframe").removeClass("selected").children("select").val(null);
        $(this).addClass("selected");
        ');
    $this->Js->get(".predefined-timeframe")->event('click','
        if ($(this).children("select").val() == "") {
            $(".predefined-timeframe").removeClass("selected");
            return;
        }
        $(".date-timeframe").removeClass("selected").children("input").val(null);
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
                function getTreeElt($label, $value, $isLeave=true) {
                    $value = (is_numeric($value) ? $value : 0);
                    return "<li><span style='font-weight:normal'>". $label . ": ". $value ."</span>". ($isLeave? "</li>":"");
                }

                foreach ($countriesCredits as $countryCredits) {
                    echo '<li data-jstree=\'{"icon":"../img/country-icon.png"}\'>'. 
                    __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", $countryCredits['country'], $countryCredits['incoming'], $countryCredits['outgoing']);
                    echo '<ul>';
                    foreach ($countryCredits['codes'] as $code) {
                        echo '<li data-jstree=\'{"icon":"../img/phone-icon-20.png"}\'>'. 
                            __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", $code['code'], $code['incoming'], $code['outgoing']);
                        echo '<ul>';
                        foreach ($code['programs'] as $programCreditLog) {
                            echo '<li data-jstree=\'{"icon":"../img/vusion-logo-20.png"}\'>'. 
                                __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", $programCreditLog['name'], $programCreditLog['incoming'], $programCreditLog['outgoing']);
                            echo '<ul>';
                            echo getTreeElt(__("incoming"), $programCreditLog['incoming']);
                            echo getTreeElt(__("outgoing"), $programCreditLog['outgoing'], false);
                            echo '<ul>';
                            echo getTreeElt(__("pending"), $programCreditLog['outgoing-pending']);
                            echo getTreeElt(__("acked"), $programCreditLog['outgoing-ack']);
                            echo getTreeElt(__("nacked"), $programCreditLog['outgoing-nack']);
                            echo getTreeElt(__("delivered"), $programCreditLog['outgoing-delivered']);
                            echo getTreeElt(__("failed"), $programCreditLog['outgoing-failed']);
                            echo '</ul>';
                            echo '</li>';                            
                            echo '</ul></li>';
                        }
                        if ($code['garbage'] != array()) {
                            echo '<li data-jstree=\'{"icon":"../img/garbage-icon-20.png"}\'>'. 
                            __("%s  <span style='font-weight:normal'>in:%s  out:%s</span>", __('Unmatchable Replies'), $code['garbage']['incoming'], $code['garbage']['outgoing']);
                            echo '<ul>';
                            echo getTreeElt(__("incoming"), $code['garbage']['incoming']);
                            echo getTreeElt(__("outgoing"), $code['garbage']['outgoing']);
                            echo '</ul></li>';
                            echo '</ul>';
                            echo '</li>';
                        }                        
                    }
                    echo '</ul>';
                    echo '</li>';
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

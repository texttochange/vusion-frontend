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

    /*
    echo "<div>";
    echo "<div class='form-line'>";
    echo $this->Form->radio(
        'timeframe-type',
        array('date-to-now' => __("Calculate credit from")),
        array('legend' => false,
            'hiddenField' => false));
    echo "<span class='subinput' name='date-to-now-subtype'>";
    echo $this->Form->input('date-from',
        array(
            'id' => 'date-from',
            'label' => false,
            'style' => 'width:60px',
            'div' => false));
    echo "</span>";
    echo "</div>";
    echo "<div class='form-line'>";
    echo $this->Form->radio(
        'timeframe-type',
        array('between-dates' => __("Calculate credit from")),
        array('legend' => false,
            'hiddenField' => false));
    echo "<span class='subinput' name='between-dates-subtype'>";
    echo $this->Form->input('date-from',
        array(
            'id' => 'date-from',
            'label' => false,
            'style' => 'width:60px',
            'div' => false));
    echo $this->Form->input('date-to',
        array(
            'id' => 'date-to',
            'label' => __("to"),
            'style' => 'width:60px',
            'div' => false));
    echo "</span>";
    echo "</div>";
    echo "<div class='form-line'>"; 
    echo $this->Form->radio(
        'timeframe-type',
        array('predefined-timeframe' => __("Calculate credit of")),
        array('legend' => false,
            'hiddenField' => false));
    echo "<span class='subinput' name='predefined-timeframe-subtype'>";
    echo $this->Form->select(
        'predefined-timeframe',
        array(
            '' => __('choose a timeframe...')
            'this-month' => __('this month'),
            'last-month' => __('last month')),
        array(
            'placeholder' => __("Choose timeframe...")));
    echo "</span>";
    echo "</div>";
    echo "</div>";
    */
    echo $this->Form->end(array(
        'div' => false,
        'class' => 'submit',
        'label' => __('Calculate')));
    
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
                            echo '<li>'.__("<span style='font-weight:normal'>incoming: %s</span>", $programCreditLog['incoming']).'</li>';
                            echo '<li>'.__("<span style='font-weight:normal'>outgoing: %s</span>", $programCreditLog['outgoing']);
                            echo '<ul>';
                            echo '<li>'.__("<span style='font-weight:normal'>ack: %s</span>", $programCreditLog['outgoing-ack']).'</li>';
                            echo '<li>'.__("<span style='font-weight:normal'>nack: %s</span>", $programCreditLog['outgoing-nack']).'</li>';
                            echo '<li>'.__("<span style='font-weight:normal'>delivered: %s</span>", $programCreditLog['outgoing-delivered']).'</li>';
                            echo '<li>'.__("<span style='font-weight:normal'>failed: %s</span>", $programCreditLog['outgoing-failed']).'</li>';
                            echo '</ul>';
                            echo '</li>';                            
                            echo '</ul></li>';
                        }
                        echo '<li data-jstree=\'{"icon":"../img/garbage-icon-20.png"}\'>'. __('unmatchable');
                        echo '<ul>';
                        echo '<li>'.__('incoming: %s', $code['garbage']['incoming']).'</li>';
                        echo '<li>'.__('outgoing: %s', $code['garbage']['outgoing']).'</li>';
                        echo '</ul></li>';
                        echo '</ul>';
                        echo '</li>';                        
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

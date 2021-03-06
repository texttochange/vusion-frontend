<div class="table" style="width:100%">
<div class="cell" style="width:60%">
    <div class="ttc-program-index">
        <?php 
            $this->RequireJs->scripts(array("ttc-utils", "jquery"));
            
            echo $this->AclLink->generateButton(
                __('Create Program'), 
                null,
                'programs',
                'add',
                array('class' => 'ttc-button', 'style'=>'float:right'));
            echo $this->Html->tag(
                'span', 
                __('Filter'), 
                array('class' => 'ttc-button', 'style'=>'float:right', 'name' => 'add-filter')); 
            $this->Js->get('[name=add-filter]')->event(
                'click',
                '$("#advanced_filter_form").show();
                createFilter();
                addStackFilter();');
        ?>
        <h3><?php echo __('Programs');?></h3>
        <?php
            echo $this->element('filter_box', array(
                'controller' => 'programs'));
            $this->RequireJs->runLine('$(".ttc-paging").css("margin-right", "0px");');
        ?>
        <div style="clear:both">
           <!-- Buffer zone -->
        </div>
        <?php
            if (preg_grep('/^filter/', array_keys($this->params['url'])) && empty($programs))
                echo "No results found.";
            $programStatsToCompute =array();
            foreach ($programs as $program): 
                $url = $program['Program']['url']; 
                $class = "ttc-program-box";
                if ($program['Program']['status'] === 'archived') {
                    $class = $class . " archived";
                }
                echo "<div id=" . $program['Program']['url'] ." class='".$class."' onclick='clickProgramBox(\"". $program['Program']['url'] ."\", event)'>";        
                $programName = $this->Text->truncate($program['Program']['name'], 
                    24, 
                    array('ellipsis' => '...',
                    'exact' => true ));
                echo $this->Html->tag('div', $programName, array('class' => 'ttc-program-title','title' => $program['Program']['name']));
                if (isset($program['Program']['shortcode'])) {
                    $shortcode = $this->PhoneNumber->replaceCountryCodeOfShortcode(
                        $program['Program']['prefixed-shortcode'],
                        $countryIndexedByPrefix);   
                    echo $this->Html->tag('div', $shortcode, array('class'=>'ttc-program-details'));
                } elseif ($program['Program']['status'] === 'archived') {
                    echo $this->Html->tag('div', __('Archived'), array('class'=>'ttc-program-details'));
                }
                if (isset($program['Program']['shortcode']) || ($program['Program']['status'] ==='archived')) {
                    echo '<div class ="ttc-program-stats">';
                    $programStatsToCompute[] = $program;			
                    echo '<div>';
                    echo '<img src="/img/ajax-loader.gif">';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo $this->Html->link('Configure Shortcode and TimeZone', 
                        array('program' => $program['Program']['url'],
                            'controller' => 'programSettings',
                            'action' => 'index'
                            ),
                        array('class' => 'ttc-program-stats configure-program-settings'));
                } 
            ?>
            <?php if ($isProgramEdit) { ?>
            <div class="ttc-program-quicklinks">
                <?php 
                echo $this->Html->link(__('Admin'), array('action' => 'edit', $program['Program']['id']));
                ?>
            </div>
            <?php }; ?>
        </div>
        <?php endforeach; ?>
        <?php
            $this->Js->get("[name='delete-program']")->event("click", "event.stopPropagation()");
            $this->Js->set('programs', $programStatsToCompute);
            $this->RequireJs->runLine('loadProgramStats();');?>
    </div>
</div>

<div class="cell" style="width:40%">
    <div class="table ttc-recent-issues">
        <h3><?php echo __('Recent Issues'); ?></h3>
        <ul class="ttc-issues-list">
        <?php foreach ($unmatchableReplies as $unmatchableReply): ?>
        <li>
        <?php
        echo "<div class='row'>";
            echo "<div class='cell ttc-issue-content'>";
            echo $this->Html->tag('h3', $this->Html->link(__('unmatchable reply'),array('controller'=>'unmatchableReply','action' => 'index')));
            echo $this->Html->tag('p', ($unmatchableReply['UnmatchableReply']['message-content']!=null ? $unmatchableReply['UnmatchableReply']['message-content'] : "<i>message empty</i>"));
            echo "</div>";
            echo "<div class='cell ttc-issue-time' >";
            echo $this->Time->format('d/m/y H:i', $unmatchableReply['UnmatchableReply']['timestamp']);
            echo ' (UTC)';
            echo "</div>";
            echo "</div>"; 
        ?>
        </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php
App::uses('AppHelper', 'View/Helper');


class DynamicOptionsHelper extends AppHelper
{

	public $helpers = array('Js');

	function setOptions($currentProgramData, $conditionalActionOptions, 
		$contentVariableTableOptions, $dialogue=null, $dynamicOptions=null)
	{
	    ##specific for dialogue edit
        $offsetConditionOptions = array(); //array('value'=> null, 'html' => __('Choose one question...'));
		if (isset($dialogue['Dialogue']['interactions'])) {
		    foreach($dialogue['Dialogue']['interactions'] as $interaction) {
		        if ($interaction['type-interaction']!='question-answer' and $interaction['type-interaction']!='question-answer-keyword')
		            continue;
		        $offsetConditionOptions[] = array(
		            'value' => $interaction['interaction-id'],
		            'html' => (isset($interaction['content']) ? $interaction['content'] : "")
		            );
		    }
		}
		$this->Js->set('offset-condition-interaction-idOptions', $offsetConditionOptions);
		$this->Js->set('dymanicOptions', $dynamicOptions);
	    
	    $dialogueOptions = array();
        foreach($currentProgramData['dialogues'] as $dialogue) {
            if ($dialogue['Active']) {
                $dialogueOptions[] = array(
                    'value' => $dialogue['Active']['dialogue-id'],
                    'html' => $dialogue['Active']['name']
                    );
            }
        }
        $this->Js->set('enrollOptions', $dialogueOptions);
        $this->Js->set('subcondition-fieldOptions', $conditionalActionOptions);
        $attachedTableOptions = array();
        $contentVariableTableSummaryOptions = array();
        foreach($contentVariableTableOptions as $cvt) {
            $attachedTableOptions[] = array(
                'value' => $cvt['ContentVariableTable']['_id']."",
                'html' => $cvt['ContentVariableTable']['name']);
            $rowHeaders = array();
            foreach ($cvt['ContentVariableTable']['columns'] as $column) {
                if ($column['type'] == 'key') {
                    $rowHeaders[] = $column['header'];
                } else {
                    continue;
                }
            }
            $contentVariableTableSummaryOptions[$cvt['ContentVariableTable']['_id'].""] = $rowHeaders;
        }
        $this->Js->set('scvt-attached-tableOptions', $attachedTableOptions);
        $this->Js->set('contentVariableTableSummaryOptions', $contentVariableTableSummaryOptions);
        
    }
}

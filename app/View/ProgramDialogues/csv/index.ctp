<?php
$fields = array(
    'dialogue-id',
    'interaction-id',
    'question-type',
    'content',
    'answers');
echo $this->Csv->arrayToLine($fields);

foreach($dialogues as $dialogue) {
    $interactions = array();
    if (isset($dialogue['Dialogue'])) {
        $interactions = $dialogue['Dialogue']['interactions'];
    } else if (isset($dialogue['Active'])) {
        $interactions = $dialogue['Active']['interactions'];
    }
    foreach($interactions as $interaction) {
        $line = array(
            $dialogue['Dialogue']['dialogue-id'],
            $interaction['interaction-id']);
        if ($interaction['type-interaction'] === 'question-answer') {
            $line[] = $interaction['type-question'];
            $line[] = $interaction['content'];
            if ($interaction['type-question'] === 'closed-question') {
                $answers = array();
                foreach($interaction['answers'] as $answer) {
                    $answers[] = $answer['choice'];
                }
                $line[] = implode(',', $answers);
            } else {
                $line[] = '';
            }
        } elseif ($interaction['type-interaction'] === 'question-answer-keyword') {
            $line[] = 'closed-question';
            $line[] = $interaction['content'];
            $answers = array();
            foreach($interaction['answer-keywords'] as $answer) {
                $answers[] = $answer['keyword'];
            }
            $line[] = implode(',', $answers);
        } else {
            print_r('continue');
            continue;
        }
        echo $this->Csv->arrayToLine($line);
    }
}
<?php 
App::uses('AppHelper', 'View/Helper');


class CreditManagerHelper extends AppHelper
{

    var $helpers = array('Time');


    public function flash($creditStatus, $settings) 
    {
        $out = false;
        if (!isset($settings['timezone']) || !isset($settings['shortcode']) || !$creditStatus || $creditStatus['manager']['status']=='none') {
            return $out;
        }

        if (isset($creditStatus) && isset($settings)) {
            $message = $this->getErrorMessage($creditStatus, $settings);
            if ($message) {
                $out = '<div id="credit-status" class="message credit">'.$message.'</div>';
            } else {
                $message = $this->getWarningMessage($creditStatus, $settings);
                if ($message) {
                    $out = '<div id="credit-status" class="message credit warning">'.$message.'</div>';
                }
            }
        }

        return $out;
    }

    
    public function getWarningMessage($creditStatus, $programSettings) 
    {
        $out = array();
        $programTime = $this->getProgramTime($programSettings['timezone']);
        switch ($creditStatus['manager']['status']) {
            case 'ok':
                $balance = $this->getBalance($creditStatus, $programSettings);
                if (0 < $balance && $balance <= 50) {
                    $out[] = __('Warning only %s credits are available for sending message(s).', $balance);
                } else if ($balance < 0) {
                    $out[] = __('Internal error, please contact support.');
                }
                $toDate = new DateTime($programSettings['credit-to-date']);
                if ($toDate < $programTime) {
                    $out[] = __('Warning the credits timeframe is ending tomorrow.');
                }
            break;
        }
        return implode($out, ' ');
    }


    public function getErrorMessage($creditStatus, $programSettings) 
    {
        $out = array();
        $programTime = $this->getProgramTime($programSettings['timezone']);
        switch ($creditStatus['manager']['status']) {
            case 'no-credit':
                $out[] = __('The program cannot send any message since %s.', $this->Time->nice($creditStatus['manager']['since']));
                $balance  = $this->getBalance($creditStatus, $programSettings);
                if ($balance < 0) {
                    $out[] = __('It is exeeding allowed credit by %s.', abs($balance));
                } else if ($balance > 0) {
                    $out[] = __('Internal error, please contact support.');
                }
            break;
            case 'no-credit-timeframe':
                $fromDate = new DateTime($programSettings['credit-from-date']);
                $toDate = new DateTime($programSettings['credit-to-date']);
                if ($programTime <= $fromDate) {
                    $out[] = __('The program cannot send any messages before %s.',$this->Time->nice($programSettings['credit-from-date']));
                } elseif ($toDate <= $programTime) {
                    $out[] = __('The program cannot send any messages after %s.',$this->Time->nice($programSettings['credit-to-date']));                                
                } else {
                    $out[] = __('Internal error, please contact support.');
                }
            break;
        }
        return implode($out, ' ');
    }

    
    public function getBalance($creditStatus, $programSettings)
    {
        return ((int)$programSettings['credit-number']) - ((int)$creditStatus['count']);
    }


    public function getProgramTime($timezone)
    {
        $now = new DateTime('now');
        date_timezone_set($now, timezone_open($timezone));        
        return $now;
    }


}
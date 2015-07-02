<?php
App::uses('Component', 'Controller');

class CaptchaComponent extends Component
{

    var $components = array('Session');

    /*public function __construct(ComponentCollection $collection, $settings = array())
    {
        $settings         = array_merge($this->settings, (array)$settings);
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
    }*/
    
    
    public function generateCaptchaCode($characters)
    {
        /* list all possible characters ; similar looking characters and vowels have been removed */
        $possible    = '23456789bcdfghjkmnpqrstvwxyz';//ABCDFGHJKMNPRSTVWXYZ
        $captchaCode = '';
        $i           = 0;
        while ($i < $characters) {
            $captchaCode .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
            $i++;
        }
        return $captchaCode;
    }   
    
    
    public function create()
    {
        $settings   = Configure::read('vusion.captcha.settings');
        $themes     = Configure::read('vusion.captcha.themes');
        $captchaCode = $this->generateCaptchaCode($settings['characters']);
        $this->setCaptchaCode($captchaCode);
        $this->_outputImage($settings, $themes, $captchaCode);        
    }
    
    
    /* to avoid the image to be displayed in the unittest output*/
    protected function _outputImage($settings, $themes, $captchaCode)
    {
        $width      = $settings['width'];
        $height     = $settings['height'];
        $characters = $settings['characters'];
        $this->_prepare_themes($settings, $themes);       
        $theme       = $settings['theme'];
        /* font size will be 75% of the image height */
        $fontSize = $height * $settings['font_adjustment'];
        $image    = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        /* set the colours */
        $backgroundColor = imagecolorallocate(
            $image,
            $themes[$theme]['bgcolor'][0],
            $themes[$theme]['bgcolor'][1],
            $themes[$theme]['bgcolor'][2]
        );
        $textColor       = imagecolorallocate(
            $image,
            $themes[$theme]['txtcolor'][0],
            $themes[$theme]['txtcolor'][1],
            $themes[$theme]['txtcolor'][2]
        );
        $noiseColor      = imagecolorallocate(
            $image,
            $themes[$theme]['noisecolor'][0],
            $themes[$theme]['noisecolor'][1],
            $themes[$theme]['noisecolor'][2]
        );
        /* generate random dots in background */
        for ( $i=0; $i<($width*$height)/3; $i++ ) {
            imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noiseColor);
        }
        /* generate random lines in background */
        for ( $i=0; $i<($width*$height)/150; $i++ ) {
            imageline(
                $image,
                mt_rand(0,$width),
                mt_rand(0,$height),
                mt_rand(0,$width),
                mt_rand(0,$height),
                $noiseColor
            );
        }
        /* create textbox and add text */
        $font    = WWW_ROOT . 'files/fonts/' . $settings['font'];
        $textbox = imagettfbbox($fontSize, 0, $font, $captchaCode) or die('Error in imagettfbbox function');
        $x       = ($width - $textbox[4])/2;
        $y       = ($height - $textbox[5])/2;
        $y      -= 5;
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $font , $captchaCode) or die('Error in imagettftext function');
        @ob_end_clean(); //clean buffers, as a fix for 'headers already sent errors..'

        /* output captcha image to browser */
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image); //stop the flow of instructions
    }
    
    
    public function setCaptchaCode($captchaCode)
    {          
        return $this->Session->write('captchaCode', $captchaCode);
    }
    
    
    public function getCaptchaCode()
    {
        return $this->Session->read('captchaCode');
    }
    
    
    public function _prepare_themes($settings, $themes)
    {
        if ($settings['theme']=='random') {
            $themes['random'] = array(
                'bgcolor'    => array(
                    $bgR = rand(0,255),
                    $bgG = rand(0,255),
                    $bgB = rand(0,255)),
                'txtcolor'   => array(rand(0,255), rand(0,255), rand(0,255)),
                'noisecolor' => array(rand(0,255), rand(0,255), rand(0,255))
                );
            $chR                          = rand(40, 50);
            $chG                          = rand(40, 50);
            $chB                          = rand(40, 50);
            $txtR                         = $bgR+$chR >= 255 ? 255 : $bgR+$chR;
            $txtG                         = $bgG+$chG >= 255 ? 255 : $bgG+$chG;
            $txtB                         = $bgB+$chB >= 255 ? 255 : $bgB+$chB;
            $themes['random']['txtcolor'] = array($txtR, $txtG, $txtB);
        }
    }
    
    
}

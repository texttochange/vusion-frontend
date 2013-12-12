<?php
App::uses('Component', 'Controller');

class CaptchaComponent extends Component
{
    public function __construct(ComponentCollection $collection, $settings = array())
    {
        $settings         = array_merge($this->settings, (array)$settings);
        $this->Controller = $collection->getController();
        parent::__construct($collection, $settings);
    }
    
    
    public function generateCaptchaCode($characters)
    {
        /* list all possible characters ; similar looking characters and vowels have been removed */
        $possible = '23456789bcdfghjkmnpqrstvwxyz';//ABCDFGHJKMNPRSTVWXYZ
        $captchaCode     = '';
        $i        = 0;
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
        $width      = $settings['width'];
        $height     = $settings['height'];
        $characters = $settings['characters'];
        $this->prepare_themes();       
        $theme = $settings['theme'];
        $captchaCode  = $this->generateCaptchaCode($characters);
        /* font size will be 75% of the image height */
        $font_size = $height * $settings['font_adjustment'];
        $image     = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');
        /* set the colours */
        $background_color = imagecolorallocate(
            $image,
            $themes[$theme]['bgcolor'][0],
            $themes[$theme]['bgcolor'][1],
            $themes[$theme]['bgcolor'][2]
        );
        $text_color       = imagecolorallocate(
            $image,
            $themes[$theme]['txtcolor'][0],
            $themes[$theme]['txtcolor'][1],
            $themes[$theme]['txtcolor'][2]
        );
        $noise_color      = imagecolorallocate(
            $image,
            $themes[$theme]['noisecolor'][0],
            $themes[$theme]['noisecolor'][1],
            $themes[$theme]['noisecolor'][2]
        );
        /* generate random dots in background */
        for ( $i=0; $i<($width*$height)/3; $i++ ) {
            imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
        }
        /* generate random lines in background */
        for ( $i=0; $i<($width*$height)/150; $i++ ) {
            imageline(
                $image,
                mt_rand(0,$width),
                mt_rand(0,$height),
                mt_rand(0,$width),
                mt_rand(0,$height),
                $noise_color
            );
        }
        /* create textbox and add text */
        $font    = WWW_ROOT . 'files/fonts/' . $settings['font'];
        $textbox = imagettfbbox($font_size, 0, $font, $captchaCode) or die('Error in imagettfbbox function');
        $x       = ($width - $textbox[4])/2;
        $y       = ($height - $textbox[5])/2;
        $y      -= 5;
        imagettftext($image, $font_size, 0, $x, $y, $text_color, $font , $captchaCode) or die('Error in imagettftext function');
        $this->setCaptchaCode($captchaCode);
        if (ob_get_length() > 0 ) {    //Test necessary during unit testing
            @ob_end_clean(); //clean buffers, as a fix for 'headers already sent errors..'
        }
        $this->_outputImage($image);
    }

    /* to avoid the image to be displayed in the unittest output*/
    protected function _outputImage($image)
    {
        /* output captcha image to browser */
        header('Content-Type: image/jpeg');
        imagejpeg($image);
        imagedestroy($image);
    }
    
    public function setCaptchaCode($captchaCode)
    {          
        return $this->Controller->Session->write('captchaCode', $captchaCode);
    }
    
    
    public function getCaptchaCode()
    {
        return $this->Controller->Session->read('captchaCode');
    }
    
    
    public function prepare_themes()
    {
        $settings = Configure::read('vusion.captcha.settings');
        $themes   = Configure::read('vusion.captcha.themes');
        if ($settings['theme']=='random') {
            $themes['random'] = array(
                'bgcolor'    => array(
                    $bg_r = rand(0,255),
                    $bg_g = rand(0,255),
                    $bg_b = rand(0,255)),
                'txtcolor'   => array(rand(0,255), rand(0,255), rand(0,255)),
                'noisecolor' => array(rand(0,255), rand(0,255), rand(0,255))
                );
            $ch_r                         = rand(40, 50);
            $ch_g                         = rand(40, 50);
            $ch_b                         = rand(40, 50);
            $txt_r                        = $bg_r+$ch_r >= 255 ? 255 : $bg_r+$ch_r;
            $txt_g                        = $bg_g+$ch_g >= 255 ? 255 : $bg_g+$ch_g;
            $txt_b                        = $bg_b+$ch_b >= 255 ? 255 : $bg_b+$ch_b;
            $themes['random']['txtcolor'] = array($txt_r, $txt_g, $txt_b);
        }
    }
    
    
}

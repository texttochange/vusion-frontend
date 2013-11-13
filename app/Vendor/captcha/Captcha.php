<?php
class Captcha {	
	public function show_captcha() {
		if (session_id() == "") {
			session_name("CAKEPHP");
			session_start();
		}

		$imgname = 'noise.jpg';
		$imgpath  = '../Vendor/captcha/images/'.$imgname;
		$captchatext = md5(time());
		$captchatext = substr($captchatext, 0, 5);
		$_SESSION['captcha'] = $captchatext;
		//$this->Session->write('captcha', $_SESSION['captcha']);
		if (file_exists($imgpath) ){
			$im = imagecreatefromjpeg($imgpath); 
			$grey = imagecolorallocate($im, 128, 128, 128);
			$font = '../Vendor/captcha/fonts/'.'BIRTH_OF_A_HERO.ttf';
			
			imagettftext($im, 26, 0, 10, 25, $grey, $font, $captchatext) ;
			
			header('Content-Type: image/jpeg');
			header("Cache-control: private, no-cache");
			header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
			header("Pragma: no-cache");
			imagejpeg($im);
			
			imagedestroy($im);
			ob_flush();
			flush();
		}
		else{
			echo 'captcha error';
			exit;
		}		 
	}
}
?>

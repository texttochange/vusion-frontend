<?php
App::uses('AppHelper', 'View/Helper');

class BigNumberHelper extends AppHelper
{

		public function replaceBigNumbers($count) 
		{   
				if($count < 1000){
						$count_format= number_format($count / 1);     		 
				}else if($count < 1000000) {
						$count_format= number_format($count / 1000, 2) .'K'; 
				
				}else if($count < 1000000000) {
						$count_format= number_format($count / 1000000, 3) .'M'; 	 
				}else{
						$count_format= number_format($count / 1000000000, 3) .'B'; 
				}
				return $count_format;   
		}
		
}
?>

Hello,<br><br>
 <body>
     Your email has been used in a password reset request at<br>
     <?php echo $linkdomain; ?><br><br>
     If you did not initiate this request, then ignore this message.<br>
     Otherwise click the link below in order to set up anew password. <br>
     <i>(Link expire after 24hrs, can only be used once)</i><br>
     http://<?php echo $linkdomain; ?>/users/useTicket/<?php echo $token; ?><br><br>
     Thanks<br/>
     <b><i>(Please don\'t reply to this email)</i><b/>
 </body>

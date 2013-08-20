    <h2> <?php echo __('Change Password') ?></h2>
    <?php
    //echo $this->Session->flash('auth');
            echo $this->Form->create('User', array('url' => array('controller' => 'users', 'action' =>'changePassword', $userId)));
            echo $this->Form->input('password', array('label' => __('Old Password'), 'id' => 'oldPassword', 'name' => 'oldPassword'));
            echo $this->Form->input('password', array('label' => __('New Password'), 'id' => 'newPassword', 'name' => 'newPassword'));
            echo $this->Form->input('password', array('label' =>__('Confirm New Password'), 'id' => 'confirmNewPassword', 'name' => 'confirmNewPassword'));
            echo $this->Form->end(__('Save',true));
    ?>
    <div class="back-button">
     <ul class="actions">
        <?php
        echo $this->Html->link(__('Cancel'),
            array(
            	'controller'=> 'users',
            	'action'=>'view', 
                $this->Session->read('Auth.User.id'))
            ); 
        ?>
     </ul>
    </div>

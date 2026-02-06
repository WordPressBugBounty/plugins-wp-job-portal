<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
* @param wp-job-portal Detail
*/
?>
<div class="wpjobportal-user-list-top-wrp">
	<?php
		wpjobportalincluder::getTemplate('user/views/admin/logo',array('wpjobportal_user' => $wpjobportal_user,'wpjobportal_layout' => 'userlogo'));

		wpjobportalincluder::getTemplate('user/views/admin/userdetail',array('wpjobportal_user' => $wpjobportal_user,'wpjobportal_layout' => 'user'));
	?>
</div>
<div class="wpjobportal-user-list-btm-wrp">
	<?php
		wpjobportalincluder::getTemplate('user/views/admin/control',array('wpjobportal_user' => $wpjobportal_user,'wpjobportal_layout' => 'usercontrol'));
	?>
</div>
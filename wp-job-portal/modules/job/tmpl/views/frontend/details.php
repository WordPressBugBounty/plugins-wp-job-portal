<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
 */
?>
<div class="js-data">
    <?php
    WPJOBPORTALincluder::getTemplate('job/views/frontend/title', array('wpjobportal_myjob' => $wpjobportal_myjob,	'wpjobportal_layout' => 'job'));
    
    WPJOBPORTALincluder::getTemplate('job/views/frontend/salary', array('wpjobportal_myjob' => $wpjobportal_myjob));
    ?>
</div>
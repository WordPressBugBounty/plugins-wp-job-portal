<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<span class="js-admin-title"><?php echo esc_html(__('User Stats Companies', 'wp-job-portal')) ?></span>
<?php
if (!empty(wpjobportal::$_data[0])) {
    ?>
    <table id="js-table">
        <thead>
            <tr>
                <th class="left-row"><?php echo esc_html(__('Name', 'wp-job-portal')); ?></th>
                <th><?php echo esc_html(__('Category', 'wp-job-portal')); ?></th>
                <th><?php echo esc_html(__('Created', 'wp-job-portal')); ?></th>
                <th><?php echo esc_html(__('Status', 'wp-job-portal')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $wpjobportal_status = array('1' => esc_html(__('Job Approved', 'wp-job-portal')), '-1' => esc_html(__('Job Rejected', 'wp-job-portal')));
            $wpjobportal_deleteimg = 'remove.png';
            $wpjobportal_deletealt = esc_html(__('Delete', 'wp-job-portal'));
            for ($wpjobportal_i = 0, $wpjobportal_n = count(wpjobportal::$_data[0]); $wpjobportal_i < $wpjobportal_n; $wpjobportal_i++) {
                $wpjobportal_row = wpjobportal::$_data[0][$wpjobportal_i];
                ?>
                <tr valign="top">
                    <td class="left-row"><?php echo esc_html($wpjobportal_row->name); ?></td>
                    <td><?php echo esc_html($wpjobportal_row->cat_title); ?></td>
                    <td><?php echo esc_html(date_i18n($this->config['date_format'], strtotime($wpjobportal_row->created))); ?></td>
                    <td>
                        <?php
                        if ($wpjobportal_row->status == 1)
                            echo "<font color='green'>" . esc_html($wpjobportal_status[$wpjobportal_row->status]) . "</font>";
                        elseif ($wpjobportal_row->status == -1)
                            echo "<font color='red'>" . esc_html($wpjobportal_status[$wpjobportal_row->status]) . "</font>";
                        else
                            echo "<font color='blue'>" . esc_html($wpjobportal_status[$wpjobportal_row->status]) . "</font>";
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
    if (wpjobportal::$_data[1]) {
        echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(wpjobportal::$_data[1]) . '</div></div>';
    }
} else {
    $wpjobportal_msg = esc_html(__('No record found','wp-job-portal'));
    WPJOBPORTALlayout::getNoRecordFound($wpjobportal_msg);
}
?>

<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<div id="wpjobportal-head">
    <h1 class="wpjobportal-head-text">
        <?php echo esc_html(__('User Stats Jobs', 'wp-job-portal')) ?>
    </h1>
</div>
<?php
if (!empty(wpjobportal::$_data[0])) {
    ?>  		
    <table id="wpjobportal-table" class="wpjobportal-table">
        <thead>
            <tr>
                <th class="wpjobportal-text-left">
                    <?php echo esc_html(__('Name', 'wp-job-portal')); ?>
                </th>
                <th>
                    <?php echo esc_html(__('Company', 'wp-job-portal')); ?>
                </th>
                <th>
                    <?php echo esc_html(__('Category', 'wp-job-portal')); ?>
                </th>
                <th>
                    <?php echo esc_html(__('Start Publishing', 'wp-job-portal')); ?>
                </th>
                <th>
                    <?php echo esc_html(__('Stop Publishing', 'wp-job-portal')); ?>
                </th>
                <th>
                    <?php echo esc_html(__('Created', 'wp-job-portal')); ?>
                </th>
                <th>
                    <?php echo esc_html(__('Status', 'wp-job-portal')); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $wpjobportal_k = 0;
                for ($wpjobportal_i = 0, $wpjobportal_n = count(wpjobportal::$_data[0]); $wpjobportal_i < $wpjobportal_n; $wpjobportal_i++) {
                    $wpjobportal_row = wpjobportal::$_data[0][$wpjobportal_i];
                    ?>
                    <tr>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html($wpjobportal_row->title); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_row->companyname); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_row->cat_title); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_row->startpublishing); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_row->stoppublishing); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n($this->config['date_format'], strtotime($wpjobportal_row->created))); ?>
                        </td>
                        <td>
                            <?php
                                if ($wpjobportal_row->status == 1)
                                    echo "<span class='wpjobportal-table-priority-color' style='background:green'>" . esc_html($wpjobportal_status[$wpjobportal_row->status]) . "</span>";
                                elseif ($wpjobportal_row->status == -1)
                                    echo "<span class='wpjobportal-table-priority-color' style='background:red'>" . esc_html($wpjobportal_status[$wpjobportal_row->status]) . "</span>";
                                else
                                    echo "<span class='wpjobportal-table-priority-color' style='background:blue'>" . esc_html($wpjobportal_status[$wpjobportal_row->status]) . "</span>";
                            ?>
                        </td>
                    </tr>
                    <?php
                    $wpjobportal_k = 1 - $wpjobportal_k;
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

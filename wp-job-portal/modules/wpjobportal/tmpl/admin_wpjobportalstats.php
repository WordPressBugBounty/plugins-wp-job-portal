<?php 
if (!defined('ABSPATH'))
    die('Restricted Access');
wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
$wpjobportal_data = wpjobportal::$_data[0];
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/leftmenue',array('wpjobportal_module'=>'wpjobportal')); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTAL_Admin_List_UI::legacy_top(); ?>
        <!-- top head -->
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'wpjobportal' , 'wpjobportal_layouts' => 'stats')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <table id="wpjobportal-table" class="wpjobportal-table">
                <thead>
                    <tr>
                        <th class="wpjobportal-text-left">
                            <?php echo esc_html(__('Title', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Total', 'wp-job-portal')); ?>
                        </th>
                        <th>
                            <?php echo esc_html(__('Active', 'wp-job-portal')); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html(__('Companies', 'wp-job-portal')); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['companies']->totalcompanies); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['companies']->activecompanies); ?>
                        </td>
                    </tr>
                    <?php if (in_array('featuredcompany',wpjobportal::$_active_addons) && in_array('credits', wpjobportal::$_active_addons)) { ?>
                        <tr>
                            <td class="wpjobportal-text-left">
                                <?php echo esc_html(__('Featured Companies', 'wp-job-portal')); ?>
                            </td>
                            <td>
                                <?php echo isset($wpjobportal_data['featuredcompanies']->totalfeaturedcompanies) ? esc_html($wpjobportal_data['featuredcompanies']->totalfeaturedcompanies): '' ; ?>
                            </td>
                            <td>
                                <?php echo isset($wpjobportal_data['featuredcompanies']->activefeaturedcompanies) ? esc_html($wpjobportal_data['featuredcompanies']->activefeaturedcompanies): '' ; ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html(__('Jobs', 'wp-job-portal')); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['jobs']->totaljobs); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['jobs']->activejobs); ?>
                        </td>
                    </tr>
                    <?php if (in_array('featuredjob',wpjobportal::$_active_addons) && in_array('credits', wpjobportal::$_active_addons)) { ?>
                        <tr>
                            <td class="wpjobportal-text-left">
                                <?php echo esc_html(__('Featured Jobs', 'wp-job-portal')); ?>
                            </td>
                            <td>
                                <?php echo esc_html($wpjobportal_data['featuredjobs']->totalfeaturedjobs); ?>
                            </td>
                            <td>
                                <?php echo esc_html($wpjobportal_data['featuredjobs']->activefeaturedjobs); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html(__('Resume', 'wp-job-portal')); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['resumes']->totalresumes); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['resumes']->activeresumes); ?>
                        </td>
                    </tr>
                    <?php if (in_array('featureresume',wpjobportal::$_active_addons) && in_array('credits', wpjobportal::$_active_addons)) { ?>
                        <tr>
                            <td class="wpjobportal-text-left">
                                <?php echo esc_html(__('Featured Resume', 'wp-job-portal')); ?>
                            </td>
                            <td>
                                <?php echo esc_html($wpjobportal_data['featuredresumes']->totalfeaturedresumes); ?>
                            </td>
                            <td>
                                <?php echo esc_html($wpjobportal_data['featuredresumes']->activefeaturedresumes); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html(__('Employer', 'wp-job-portal')); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['totalemployer']->totalemployer); ?>
                        </td>
                        <td>
                            <?php echo '-'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="wpjobportal-text-left">
                            <?php echo esc_html(__('Job Seeker', 'wp-job-portal')); ?>
                        </td>
                        <td>
                            <?php echo esc_html($wpjobportal_data['totaljobseeker']->totaljobseeker); ?>
                        </td>
                        <td>
                            <?php echo '-'; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
if (!defined('ABSPATH'))
die('Restricted Access');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper">
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('thirdpartyimport')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
        ?>
        <?php WPJOBPORTAL_Admin_List_UI::legacy_top(); ?>
        <!-- top head -->
        <?php WPJOBPORTALincluder::getTemplate('templates/admin/pagetitle',array('wpjobportal_module' => 'thirdpartyimport' , 'wpjobportal_layouts' => 'importdata')); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper">
            <?php
/*
            $wpjobportal_results_array = [
                'company' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'job' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'resume' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'user' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'jobapply' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'field' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],

                'jobtype' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'category' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ],
                'tag' => [
                    'imported' => 10,
                    'skipped'  => 2,
                    'failed'   => 3,
                ]
            ];
*/
            // $wpjobportal_results_array = wpjobportal::$_data['import_counts'];
            $wpjobportal_results_array = get_option('wpjob_portal_import_counts');

            $wpjobportal_plugin_label = 'WP Job Manager';
            if(!empty(wpjobportal::$_data['import_for'])){
                $wpjobportal_import_for = wpjobportal::$_data['import_for'];
                if($wpjobportal_import_for == 1){
                    $wpjobportal_plugin_label = 'WP Job Manager';
                }
            }

            ?>
            <table class="wpjobportal-import-data-result-import-table">
                <thead>
                    <tr>
                        <th style="width:50%;"><?php echo  esc_html__('Entity','wp-job-portal'); ?></th>
                        <th style="text-align: center;background-color: #006D3A;width:16.6%;"><?php echo  esc_html__('Imported','wp-job-portal'); ?></th>
                        <th style="text-align: center;background-color: #A75424;width:16.6%;"><?php echo  esc_html__('Similar Found','wp-job-portal'); ?></th>
                        <th style="text-align: center;background-color: #891518;width:16.6%;"><?php echo  esc_html__('Not Imported','wp-job-portal'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wpjobportal_results_array as $type => $wpjobportal_counts){
                        $wpjobportal_label = ucwords(str_replace(['_', 'jobtype', 'jobapply'], [' ', 'Job Type', 'Job Application'], $type));
                        $wpjobportal_imported = (int) $wpjobportal_counts['imported'];
                        $wpjobportal_skipped  = (int) $wpjobportal_counts['skipped'];
                        $wpjobportal_failed   = (int) $wpjobportal_counts['failed'];

                        if(strtolower($wpjobportal_label) == 'tag'){
                            if(!in_array('tags', wpjobportal::$_active_addons)){
                                $wpjobportal_query = "SELECT taxonomy.*, terms.*
                                            FROM `" . wpjobportal::$_db->prefix . "term_taxonomy` AS taxonomy
                                            JOIN `" . wpjobportal::$_db->prefix . "terms` AS terms ON terms.term_id = taxonomy.term_id
                                            WHERE taxonomy.taxonomy = 'job_listing_tag';";
                                $wpjobportal_tags = wpjobportal::$_db->get_results($wpjobportal_query);
                                $wpjobportal_failed = is_array($wpjobportal_tags) ? count($wpjobportal_tags) : 0;
				$wpjobportal_skipped = 0;
                            }
                        }
			if($wpjobportal_imported > 1){
		                if($wpjobportal_label == 'Company'){
		                    $wpjobportal_label = 'Companies';
		                }elseif(strtolower($wpjobportal_label) == 'category'){
		                    $wpjobportal_label = 'Categories';
		                }else{
		                    $wpjobportal_label = $wpjobportal_label.'s';
		                }
			}
                        ?>
                        <tr>
                            <td><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_label)); ?></td>

                            <td class="wpjobportal-import-data-result-success">
                                <?php echo esc_html( $wpjobportal_imported .' '. __('Imported','wp-job-portal') ); ?>
                            </td>

                            <td class="wpjobportal-import-data-result-similar">
                                <?php echo esc_html( $wpjobportal_skipped .' '. __('Skipped','wp-job-portal') ); ?>
                            </td>

                            <td class="wpjobportal-import-data-result-failed">
                                <?php echo esc_html( $wpjobportal_failed .' '. __('Failed','wp-job-portal') ); ?>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <div>
            <span class="wpjobportal-import-data-addon-message"><?php echo esc_html(__('Please verify Locations and salaries for imported data entities','wp-job-portal')); ?></span>
        </div>
        </div>
    </div>
</div>

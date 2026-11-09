<?php
    if (!defined('ABSPATH'))
        die('Restricted Access');
    wp_enqueue_script( 'wpjobportal-res-tables', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/responsivetable.js', array(), wpjobportal_asset_version('includes/js/responsivetable.js'), false );
?>
<?php
$wpjobportal_conf = WPJOBPORTALincluder::getJSModel('configuration');
$wpjobportal_text = function ($name, $value) {
    return WPJOBPORTALformfield::text($name, $value, array('class' => 'inputbox wpjobportal-form-input-field'));
};
$wpjobportal_card = function ($id, $title, $icon, $rows) {
    echo '<section class="wjpl-card wjpl-fcard" aria-labelledby="wjpl-fs-' . esc_attr($id) . '"><h2 id="wjpl-fs-' . esc_attr($id) . '">' . WPJOBPORTAL_Admin_Form_UI::icon($icon) . esc_html($title) . '</h2><div class="wjpl-fgrid">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG icon
    foreach ($rows as $row) {
        WPJOBPORTAL_Admin_Form_UI::field($row);
    }
    echo '</div></section>';
};
WPJOBPORTAL_Admin_Form_UI::scripts('wpjobportal-form');
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <div id="wpjobportaladmin-leftmenu">
        <?php WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php WPJOBPORTALMessages::getLayoutMessage(WPJOBPORTALincluder::getJSModel('wpjobportal')->getMessagekey()); ?>
        <div class="wjpl wjpl-formpage">
            <?php
            WPJOBPORTAL_Admin_List_UI::top_bar(WPJOBPORTAL_Admin_List_UI::menu_crumbs());
            WPJOBPORTAL_Admin_List_UI::title_row(__('SEO', 'wp-job-portal'), __('Browser tab titles and link words for job, company and resume pages.', 'wp-job-portal'));
            WPJOBPORTAL_Admin_List_UI::section_tabs();
            ?>
            <form id="wpjobportal-form" class="wpjobportal-form wjpl-form" method="post" action="<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal&task=savedocumenttitleoptions")); ?>">
                <div class="wjpl-form-main">
                    <?php
                    $wpjobportal_card('titles', __('Document Title Settings', 'wp-job-portal'), '<path d="M4 6h16M4 12h10M4 18h7"/>', array(
                        array('title' => __('Company Detail Document Title', 'wp-job-portal'), 'wpjobportal_content' => $wpjobportal_text('wpjobportal_company_document_title_settings', get_option('wpjobportal_company_document_title_settings')), 'wpjobportal_description' => __('Company detail page document title options are Company Name and Company City . eg- [name] [location] [separator] [sitename]', 'wp-job-portal')),
                        array('title' => __('Job Detail Document Title', 'wp-job-portal'), 'wpjobportal_content' => $wpjobportal_text('wpjobportal_job_document_title_settings', get_option('wpjobportal_job_document_title_settings')), 'wpjobportal_description' => __('Job detail page document title options are Job Title, Company Name, Job Category, Job Type and Job Cities . eg- [title] [companyname] [jobcategory] [jobtype] [location] [separator] [sitename]', 'wp-job-portal')),
                        array('title' => __('Resume Detail Document Title', 'wp-job-portal'), 'wpjobportal_content' => $wpjobportal_text('wpjobportal_resume_document_title_settings', get_option('wpjobportal_resume_document_title_settings')), 'wpjobportal_description' => __('Resume detail document title options are Application Title, Job Category, Job Type and Resume Location. eg- [applicationtitle] [jobcategory] [jobtype] [location] [separator] [sitename]', 'wp-job-portal')),
                    ));
                    $wpjobportal_card('urls', __('URL Settings', 'wp-job-portal'), '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>', array(
                        array('title' => __('Job Detail URL', 'wp-job-portal'), 'wpjobportal_content' => $wpjobportal_text('job_seo', $wpjobportal_conf->getConfigValue('job_seo')), 'wpjobportal_description' => __('Job detail URL options are title, company, category, location, jobtype. eg- [title] [company] [category] [jobtype] [location]', 'wp-job-portal')),
                        array('title' => __('Company Detail URL', 'wp-job-portal'), 'wpjobportal_content' => $wpjobportal_text('company_seo', $wpjobportal_conf->getConfigValue('company_seo')), 'wpjobportal_description' => __('Company detail url options are name, location. eg- [name] [location]', 'wp-job-portal')),
                        array('title' => __('Resume Detail URL', 'wp-job-portal'), 'wpjobportal_content' => $wpjobportal_text('resume_seo', $wpjobportal_conf->getConfigValue('resume_seo')), 'wpjobportal_description' => __('Resume detail URL options are title, category, location. eg- [title] [location]', 'wp-job-portal')),
                    ));
                    ?>
                </div>
                <aside class="wjpl-form-side">
                    <section class="wjpl-card wjpl-fcard wjpl-publish" aria-labelledby="wjpl-fs-save">
                        <h2 id="wjpl-fs-save"><?php echo WPJOBPORTAL_Admin_Form_UI::icon('<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG icon ?><?php echo esc_html__('Publishing', 'wp-job-portal'); ?></h2>
                        <div class="wpjobportal-form-button wjpl-form-acts">
                            <?php echo wp_kses(WPJOBPORTALformfield::submitbutton('save', esc_html(__('Save','wp-job-portal')), array('class' => 'button wpjobportal-form-save-btn')),WPJOBPORTAL_ALLOWED_TAGS); ?>
                        </div>
                    </section>
                </aside>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('wpjobportallt', 'configurations'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('action', 'configuration_savedocumenttitleoptions'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('form_request', 'wpjobportal'),WPJOBPORTAL_ALLOWED_TAGS); ?>
                <?php echo wp_kses(WPJOBPORTALformfield::hidden('_wpnonce', esc_html(wp_create_nonce('wpjobportal_document_title_nonce'))),WPJOBPORTAL_ALLOWED_TAGS); ?>
            </form>
        </div>
    </div>
</div>

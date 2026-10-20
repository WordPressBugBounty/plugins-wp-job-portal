<?php
if (!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="wjportal-jobs-list-top-filter">
    <h3><?php echo esc_html__('Filter', 'wp-job-portal')?> </h3>
    <!-- Filter Dropdowns & Sorting -->
    <div class="wpjp-joblisting-filters-row">
        <?php
        wpjobportal::$_data['fields_titles_array'] = WPJOBPORTALincluder::getJSModel('fieldordering')->getFieldOrderingData(2); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- a static property of the wpjobportal class, not a global.
        // 1. Preparation of Data
        $wpjobportal_job_types = WPJOBPORTALincluder::getJSModel('jobtype')->getJobTypeForCombo();
        $wpjobportal_job_status = WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatusForCombo();
        $wpjobportal_career_levels = WPJOBPORTALincluder::getJSModel('careerlevel')->getCareerLevelsForCombo();

        // Extract Active Filters from global data
        $wpjobportal_active_filters = isset(wpjobportal::$_data['filter']) ? wpjobportal::$_data['filter'] : array();

        $wpjp_workplace_types = array(
            (object) array('id' => '1', 'text' => esc_html__('On-site', 'wp-job-portal')),
            (object) array('id' => '2', 'text' => esc_html__('Hybrid', 'wp-job-portal')),
            (object) array('id' => '3', 'text' => esc_html__('Remote', 'wp-job-portal')),
        );

        function wpjp_get_active_array($wpjobportal_filter_key, $wpjobportal_active_filters) {
            if (!isset($wpjobportal_active_filters[$wpjobportal_filter_key])) return array();
            $wpjobportal_tpl_val = $wpjobportal_active_filters[$wpjobportal_filter_key];
            if (is_array($wpjobportal_tpl_val)) return $wpjobportal_tpl_val;
            return explode(',', $wpjobportal_tpl_val);
        }

        $wpjobportal_active_types   = wpjp_get_active_array('jobtype', $wpjobportal_active_filters);
        $wpjobportal_active_status  = wpjp_get_active_array('jobstatus', $wpjobportal_active_filters);
        $wpjobportal_active_careers = wpjp_get_active_array('careerlevel', $wpjobportal_active_filters);
        $wpjobportal_active_dates   = wpjp_get_active_array('dates', $wpjobportal_active_filters);
        $wpjobportal_active_workplace = wpjp_get_active_array('workplace_type', $wpjobportal_active_filters);
        $wpjobportal_active_sort    = isset(wpjobportal::$_data['sortby']) ? wpjobportal::$_data['sortby'] : 'newest';

        // Prepare array for the Active Tags UI
        $wpjobportal_active_tags_ui = array();


        // Prepare static array for Salary
        $wpjobportal_salaries = array(
            '0-99999'         => esc_html__('Under $100k', 'wp-job-portal'),
            '100000-149999'   => esc_html__('$100k - $150k', 'wp-job-portal'),
            '150000-9999999'  => esc_html__('$150k+', 'wp-job-portal')
        );

        // Prepare static array for Duration / Date Posted
        $wpjobportal_durations = array(
            '24h' => esc_html__('Past 24 hours', 'wp-job-portal'),
            '7d'  => esc_html__('Past Week', 'wp-job-portal'),
            '30d' => esc_html__('Past Month', 'wp-job-portal')
        );
        ?>
        <?php
        // --- NEW: Configurable Filters Array ---
        // To disable a filter, simply remove it from this array. You can also reorder them!
        $wpjobportal_active_ajax_filters = [];
        $wpjobportal_joblisting_ajax_filter_tags = wpjobportal::$_config->getConfigValue('joblisting_ajax_filter_tags');
        if(!empty($wpjobportal_joblisting_ajax_filter_tags)){
            preg_match_all('/\[([^\]]+)\]/', $wpjobportal_joblisting_ajax_filter_tags, $matches);
            $wpjobportal_active_ajax_filters = $matches[1];
        }
        foreach ($wpjobportal_active_ajax_filters as $wpjobportal_filter_key) {
            switch ($wpjobportal_filter_key) {
                case 'company':
                    // if field is not published then hide it
                    if(empty(wpjobportal::$_data['fields_titles_array']['company'])){
                        break;
                    }
                    ?>
                    <div class="wpjp-joblisting-dropdown-container wpjp-company-filter-container">
                        <select name="company[]" multiple="multiple" data-placeholder="<?php echo esc_attr(esc_html__('Select', 'wp-job-portal') . ' ' . wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['company'])); ?>" class="wpjp-joblisting-select wpjobportal-multiselect" style="width:100%; box-sizing: border-box;">
                            <?php
                            $wpjobportal_companies = WPJOBPORTALincluder::getJSModel('company')->getCompaniesForCombo();
                            $wpjobportal_pre_selected_companies = isset(wpjobportal::$_data['filter']['company']) ? (array) wpjobportal::$_data['filter']['company'] : array();
                            if(!empty($wpjobportal_companies)){
                                foreach($wpjobportal_companies as $wpjobportal_comp) {
                                    $wpjobportal_tpl_val = isset($wpjobportal_comp->value) ? $wpjobportal_comp->value : (isset($wpjobportal_comp->id) ? $wpjobportal_comp->id : '');
                                    $wpjobportal_selected = in_array($wpjobportal_tpl_val, $wpjobportal_pre_selected_companies);
                                    echo '<option value="'.esc_attr($wpjobportal_tpl_val).'" '.selected($wpjobportal_selected, true, false).'>'.esc_html($wpjobportal_comp->text).'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php break;

                case 'jobtype':
                    // if field is not published then hide it
                    if(empty(wpjobportal::$_data['fields_titles_array']['jobtype'])){
                        break;
                    }
                    ?>
                    <div class="wpjp-joblisting-dropdown-container">
                        <button class="wpjp-joblisting-btn-filter wpjp-joblisting-dropdown-toggle">
                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['jobtype'])); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="wpjp-joblisting-dropdown-menu wpjp-is-type">
                            <div class="wpjp-joblisting-dropdown-header"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['jobtype'])); ?></div>
                            <?php
                            if (!empty($wpjobportal_job_types)) {
                                foreach ($wpjobportal_job_types as $wpjobportal_record) {
                                    $wpjobportal_is_checked = in_array($wpjobportal_record->id, $wpjobportal_active_types);
                                    if ($wpjobportal_is_checked) { $wpjobportal_active_tags_ui[] = wpjobportal::wpjobportal_getVariableValue($wpjobportal_record->text); }
                                    ?>
                                    <label class="wpjp-joblisting-checkbox-label">
                                        <input type="checkbox" class="wpjp-joblisting-filter-type" value="<?php echo esc_attr($wpjobportal_record->id); ?>" <?php checked($wpjobportal_is_checked); ?>>
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_record->text)); ?>
                                    </label>
                                    <?php
                                }
                            }
                            ?>
                            <div class="wpjp-joblisting-dropdown-footer">
                                <button type="button" class="wpjp-joblisting-btn-clear"><?php echo esc_html__('Clear', 'wp-job-portal'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php break;

                case 'jobstatus':
                    // if field is not published then hide it
                    if(empty(wpjobportal::$_data['fields_titles_array']['jobstatus'])){
                        break;
                    }
                ?>
                    <div class="wpjp-joblisting-dropdown-container">
                        <button class="wpjp-joblisting-btn-filter wpjp-joblisting-dropdown-toggle">
                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['jobstatus'])); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="wpjp-joblisting-dropdown-menu wpjp-is-status">
                            <div class="wpjp-joblisting-dropdown-header"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['jobstatus'])); ?></div>
                            <?php
                            if (!empty($wpjobportal_job_status)) {
                                foreach ($wpjobportal_job_status as $wpjobportal_record) {
                                    $wpjobportal_is_checked = in_array($wpjobportal_record->id, $wpjobportal_active_status);
                                    if ($wpjobportal_is_checked) { $wpjobportal_active_tags_ui[] = wpjobportal::wpjobportal_getVariableValue($wpjobportal_record->text); }
                                    ?>
                                    <label class="wpjp-joblisting-checkbox-label">
                                        <input type="checkbox" class="wpjp-joblisting-filter-status" value="<?php echo esc_attr($wpjobportal_record->id); ?>" <?php checked($wpjobportal_is_checked); ?>>
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_record->text)); ?>
                                    </label>
                                    <?php
                                }
                            }
                            ?>
                            <div class="wpjp-joblisting-dropdown-footer">
                                <button type="button" class="wpjp-joblisting-btn-clear"><?php echo esc_html__('Clear', 'wp-job-portal'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php break;

                case 'careerlevel':
                    // if field is not published then hide it
                    if(empty(wpjobportal::$_data['fields_titles_array']['careerlevel'])){
                        break;
                    }
                    ?>
                    <div class="wpjp-joblisting-dropdown-container">
                        <button class="wpjp-joblisting-btn-filter wpjp-joblisting-dropdown-toggle">
                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['careerlevel'])); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="wpjp-joblisting-dropdown-menu wpjp-is-career">
                            <div class="wpjp-joblisting-dropdown-header"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['careerlevel'])); ?></div>
                            <?php
                            if (!empty($wpjobportal_career_levels)) {
                                foreach ($wpjobportal_career_levels as $wpjobportal_record) {
                                    $wpjobportal_is_checked = in_array($wpjobportal_record->id, $wpjobportal_active_careers);
                                    if ($wpjobportal_is_checked) { $wpjobportal_active_tags_ui[] = wpjobportal::wpjobportal_getVariableValue($wpjobportal_record->text); }
                                    ?>
                                    <label class="wpjp-joblisting-checkbox-label">
                                        <input type="checkbox" class="wpjp-joblisting-filter-career" value="<?php echo esc_attr($wpjobportal_record->id); ?>" <?php checked($wpjobportal_is_checked); ?>>
                                        <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue($wpjobportal_record->text)); ?>
                                    </label>
                                    <?php
                                }
                            }
                            ?>
                            <div class="wpjp-joblisting-dropdown-footer">
                                <button type="button" class="wpjp-joblisting-btn-clear"><?php echo esc_html__('Clear', 'wp-job-portal'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php break;

                case 'jobsalaryrange':
                    // if field is not published then hide it
                    if(empty(wpjobportal::$_data['fields_titles_array']['jobsalaryrange'])){
                        break;
                    }
                    ?>
                    <div class="wpjp-joblisting-dropdown-container">
                        <button class="wpjp-joblisting-btn-filter wpjp-joblisting-dropdown-toggle">
                            <?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['jobsalaryrange'])); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="wpjp-joblisting-dropdown-menu wpjp-is-salary">
                            <div class="wpjp-joblisting-dropdown-header"><?php echo esc_html(wpjobportal::wpjobportal_getVariableValue(wpjobportal::$_data['fields_titles_array']['jobsalaryrange'])); ?></div>
                            <?php
                            $wpjp_filters = isset(wpjobportal::$_data['filter']) ? wpjobportal::$_data['filter'] : array();
                            $wpjobportal_s_type = isset($wpjp_filters['salarytype']) ? $wpjp_filters['salarytype'] : '';
                            $wpjobportal_s_min = isset($wpjp_filters['salarymin']) ? $wpjp_filters['salarymin'] : '';
                            $wpjobportal_s_max = isset($wpjp_filters['salarymax']) ? $wpjp_filters['salarymax'] : '';
                            $wpjobportal_s_fixed = isset($wpjp_filters['salaryfixed']) ? $wpjp_filters['salaryfixed'] : '';
                            $wpjobportal_s_duration = isset($wpjp_filters['salaryduration']) ? $wpjp_filters['salaryduration'] : '';
                            ?>
                            <div class="wpjp-filter-salary-fields">
                                <select class="wpjp-joblisting-filter-salarytype wpjp-joblisting-select">
                                    <option value=""><?php echo esc_html__('Select Type', 'wp-job-portal'); ?></option>
                                    <option value="1" <?php selected($wpjobportal_s_type, 1); ?>><?php echo esc_html__('Negotiable', 'wp-job-portal'); ?></option>
                                    <option value="2" <?php selected($wpjobportal_s_type, 2); ?>><?php echo esc_html__('Fixed', 'wp-job-portal'); ?></option>
                                    <option value="3" <?php selected($wpjobportal_s_type, 3); ?>><?php echo esc_html__('Range', 'wp-job-portal'); ?></option>
                                </select>

                                <input type="number" class="wpjp-joblisting-filter-salaryfixed wjportal-form-input-field" placeholder="<?php echo esc_attr__('e.g 45000', 'wp-job-portal'); ?>" style="display:<?php echo ($wpjobportal_s_type == 2) ? 'block' : 'none'; ?>;" value="<?php echo esc_attr($wpjobportal_s_fixed); ?>">
                                <div id="wpjp-salary-range-wrapper" style="display:<?php echo ($wpjobportal_s_type == 3) ? 'block' : 'none'; ?>; margin: 15px 0;">
                                    <div class="wpjp-inputs-grid">
                                        <div>
                                            <label class="wpjp-slider-label"><?php echo esc_html__('Min', 'wp-job-portal'); ?></label>
                                            <div class="wpjp-input-wrapper">
                                                <span class="wpjp-currency-symbol"><?php echo esc_html(wpjobportal::$_config->getConfigValue('job_currency_symbol') ? wpjobportal::$_config->getConfigValue('job_currency_symbol') : '$'); ?></span>
                                                <input type="text" id="wpjp-min-display" class="wpjp-display-input" readonly>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="wpjp-slider-label" style="text-align: right;"><?php echo esc_html__('Max', 'wp-job-portal'); ?></label>
                                            <div class="wpjp-input-wrapper">
                                                <span class="wpjp-currency-symbol"><?php echo esc_html(wpjobportal::$_config->getConfigValue('job_currency_symbol') ? wpjobportal::$_config->getConfigValue('job_currency_symbol') : '$'); ?></span>
                                                <input type="text" id="wpjp-max-display" class="wpjp-display-input" style="text-align: right;" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="wpjp-range-area" id="wpjp-range-container">
                                        <div class="wpjp-track-bg"></div>
                                        <div id="wpjp-track-fill" class="wpjp-track-fill"></div>

                                        <input type="range" id="wpjp-input-min" class="wpjp-range-input" min="0" value="0" step="1">
                                        <input type="range" id="wpjp-input-max" class="wpjp-range-input" min="0" value="18" step="1">

                                        <div id="wpjp-thumb-min" class="wpjp-custom-thumb"></div>
                                        <div id="wpjp-thumb-max" class="wpjp-custom-thumb"></div>
                                    </div>
                                </div>

                                <input type="hidden" class="wpjp-joblisting-filter-salarymin" value="<?php echo esc_attr($wpjobportal_s_min); ?>">
                                <input type="hidden" class="wpjp-joblisting-filter-salarymax" value="<?php echo esc_attr($wpjobportal_s_max); ?>">
                                <select class="wpjp-joblisting-filter-salaryduration wpjp-joblisting-select" style="display:<?php echo ($wpjobportal_s_type == 2 || $wpjobportal_s_type == 3) ? 'block' : 'none'; ?>; width:100%; margin-bottom: 0.5rem; box-sizing: border-box;">
                                    <option value=""><?php echo esc_html__('Select Duration', 'wp-job-portal'); ?></option>
                                    <?php
                                    $wpjobportal_salary_durations = WPJOBPORTALincluder::getJSModel('salaryrangetype')->getSalaryRangeTypesForCombo();
                                    if(!empty($wpjobportal_salary_durations)){
                                        foreach($wpjobportal_salary_durations as $wpjobportal_sd) {
                                            $wpjobportal_tpl_val = isset($wpjobportal_sd->id) ? $wpjobportal_sd->id : (isset($wpjobportal_sd->value) ? $wpjobportal_sd->value : '');
                                            $wpjobportal_selected = ($wpjobportal_tpl_val == $wpjobportal_s_duration);
                                            echo '<option value="'.esc_attr($wpjobportal_tpl_val).'" '.selected($wpjobportal_selected, true, false).'>'.esc_html($wpjobportal_sd->text).'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="wpjp-joblisting-dropdown-footer">
                                <button type="button" class="wpjp-joblisting-btn-clear"><?php echo esc_html__('Clear', 'wp-job-portal'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php break;

                case 'dateposted': ?>
                    <div class="wpjp-joblisting-dropdown-container">
                        <button class="wpjp-joblisting-btn-filter wpjp-joblisting-dropdown-toggle">
                            <?php echo esc_html__('Date Posted', 'wp-job-portal'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="wpjp-joblisting-dropdown-menu wpjp-is-date">
                            <div class="wpjp-joblisting-dropdown-header"><?php echo esc_html__('Posted Within', 'wp-job-portal'); ?></div>
                            <?php
                            foreach ($wpjobportal_durations as $wpjobportal_val => $wpjobportal_label) {
                                $wpjobportal_is_checked = in_array($wpjobportal_val, $wpjobportal_active_dates);
                                if ($wpjobportal_is_checked) { $wpjobportal_active_tags_ui[] = $wpjobportal_label; }
                                ?>
                                <label class="wpjp-joblisting-checkbox-label">
                                    <input type="checkbox" class="wpjp-joblisting-filter-date" value="<?php echo esc_attr($wpjobportal_val); ?>" <?php checked($wpjobportal_is_checked); ?>>
                                    <?php echo esc_html($wpjobportal_label); ?>
                                </label>
                                <?php
                            }
                            ?>
                            <div class="wpjp-joblisting-dropdown-footer">
                                <button type="button" class="wpjp-joblisting-btn-clear"><?php echo esc_html__('Clear', 'wp-job-portal'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php break;
                    case 'workplacetype': ?>
                        <div class="wpjp-joblisting-dropdown-container">
                            <button class="wpjp-joblisting-btn-filter wpjp-joblisting-dropdown-toggle">
                                <?php echo esc_html__('Workplace Type', 'wp-job-portal'); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="wpjp-joblisting-dropdown-menu wpjp-is-workplace">
                                <div class="wpjp-joblisting-dropdown-header"><?php echo esc_html__('Workplace Type', 'wp-job-portal'); ?></div>
                                <?php
                                foreach ($wpjp_workplace_types as $wpjobportal_record) {
                                    $wpjobportal_is_checked = in_array($wpjobportal_record->id, $wpjobportal_active_workplace);
                                    if ($wpjobportal_is_checked) { $wpjobportal_active_tags_ui[] = $wpjobportal_record->text; }
                                    ?>
                                    <label class="wpjp-joblisting-checkbox-label">
                                        <input type="checkbox" class="wpjp-joblisting-filter-workplace" value="<?php echo esc_attr($wpjobportal_record->id); ?>" <?php checked($wpjobportal_is_checked); ?>>
                                        <?php echo esc_html($wpjobportal_record->text); ?>
                                    </label>
                                    <?php
                                }
                                ?>
                                <div class="wpjp-joblisting-dropdown-footer">
                                    <button type="button" class="wpjp-joblisting-btn-clear"><?php echo esc_html__('Clear', 'wp-job-portal'); ?></button>
                                </div>
                            </div>
                        </div>
                    <?php break;
            }
        }
        // to handle tags properly
        $wpjobportal_tags_string = '';
        if(!empty(wpjobportal::$_data['filter']['tags'])){
            $wpjobportal_tags_array = json_decode(wpjobportal::$_data['filter']['tags'],true);
            $wpjobportal_tags_strings_array = [];
            if(!empty($wpjobportal_tags_array)){
                foreach ($wpjobportal_tags_array as $wpjobportal_tags) {
                    $wpjobportal_tags_strings_array[] = $wpjobportal_tags['name'];
                }
                $wpjobportal_tags_string = implode(',', $wpjobportal_tags_strings_array);
            }
        }
        ?>

        <input type="hidden" id="wpjp-active-tags" name="tags" value="<?php echo esc_attr($wpjobportal_tags_string); ?>">
        <input type="hidden" id="wpjp-is-urgent-filter" name="is_urgent" value="<?php echo esc_attr(isset(wpjobportal::$_data['filter']['is_urgent']) ? wpjobportal::$_data['filter']['is_urgent'] : ''); ?>">
    </div>
    <div id="wpjp-active-filters-display" class="wpjp-active-filters-display" style="display: none;"></div>
    <div class="wpjp-joblisting-filters-row">
        <!-- Global Actions Right Side Wrapper -->
        <div class="wpjp-joblisting-actions-right">
            <div class="wpjp-joblisting-global-clear-container" id="wpjp-joblisting-global-clear-container">
                <button type="button" class="wpjp-joblisting-btn-clear-all" id="wpjp-joblisting-btn-clear-all">
                    <?php echo esc_html__('Clear All Filters', 'wp-job-portal'); ?>
                </button>
            </div>

            <?php
            // --- NEW: Sorting Toggle ---
            $wpjobportal_joblisting_ajax_show_sorting = wpjobportal::$_config->getConfigValue('joblisting_ajax_show_sorting');
            if ($wpjobportal_joblisting_ajax_show_sorting == 1) :
                ?>
                <div class="wpjp-joblisting-sort-container">
                    <span class="wpjp-joblisting-sort-label"><?php echo esc_html__('Sort By', 'wp-job-portal'); ?>: </span>
                    <select id="wpjp-joblisting-sort" class="wpjp-joblisting-select">
                        <option value="newest"><?php echo esc_html__('Latest Posted', 'wp-job-portal'); ?></option>
                        <option value="oldest"><?php echo esc_html__('Oldest Posted', 'wp-job-portal'); ?></option>
                        <option value="salary-high"><?php echo esc_html__('Highest Salary', 'wp-job-portal'); ?></option>
                        <option value="salary-low"><?php echo esc_html__('Lowest Salary', 'wp-job-portal'); ?></option>
                        <option value="title-asc"><?php echo esc_html__('Job Title (A-Z)', 'wp-job-portal'); ?></option>
                        <option value="title-desc"><?php echo esc_html__('Job Title (Z-A)', 'wp-job-portal'); ?></option>
                        <option value="company-asc"><?php echo esc_html__('Company (A-Z)', 'wp-job-portal'); ?></option>
                        <option value="company-desc"><?php echo esc_html__('Company (Z-A)', 'wp-job-portal'); ?></option>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    (function(jQuery) {
        jQuery(document).ready(function() {

            // --- SECURITY FIX: Helper function to prevent DOM-based XSS ---
            function escapeHTML(str) {
                if (typeof str !== 'string') return str;
                return str.replace(/[&<>'"]/g, function(tag) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[tag] || tag;
                });
            }

            // --- PERFORMANCE FIX: Cache frequently used DOM elements ---
            const $salaryTypeDropdown = jQuery('.wpjp-joblisting-filter-salarytype');
            const $salaryHiddenFields = jQuery('.wpjp-joblisting-filter-salaryfixed, .wpjp-joblisting-filter-salarymin, .wpjp-joblisting-filter-salarymax, .wpjp-joblisting-filter-salaryduration');
            const $salaryRangeWrapper = jQuery('#wpjp-salary-range-wrapper');
            const $inputMin = jQuery('#wpjp-input-min');
            const $inputMax = jQuery('#wpjp-input-max');
            const $displayMin = jQuery('#wpjp-min-display');
            const $displayMax = jQuery('#wpjp-max-display');
            const $trackFill = jQuery('#wpjp-track-fill');
            const $thumbMin = jQuery('#wpjp-thumb-min');
            const $thumbMax = jQuery('#wpjp-thumb-max');
            const $hiddenMin = jQuery('.wpjp-joblisting-filter-salarymin');
            const $hiddenMax = jQuery('.wpjp-joblisting-filter-salarymax');
            const $rangeArea = jQuery('#wpjp-range-container');

            // --- NEW: Initialize button states on load for pre-selected filters ---
            function initializeButtonStates() {
                jQuery('.wpjp-joblisting-dropdown-menu').each(function() {
                    const $menu = jQuery(this);
                    const $toggleBtn = $menu.siblings('.wpjp-joblisting-dropdown-toggle');
                    if ($menu.find('input[type="checkbox"]:checked').length > 0) {
                        $menu.addClass('has-selections');
                        $toggleBtn.addClass('wpjp-has-active-filters');
                    }
                });
            }

            // --- 1. Function to group and print ALL active filter tags ---
            function updateActiveFilterTags() {
                var $displayContainer = jQuery('#wpjp-active-filters-display');
                $displayContainer.empty();
                var hasFilters = false;

                // Translated Strings
                var activeFiltersLbl = '<?php echo esc_js(esc_html__("Active Filters:", "wp-job-portal")); ?>';
                var sortedByLbl = '<?php echo esc_js(esc_html__("Sorted By", "wp-job-portal")); ?>';
                var aiSearchLbl = '<?php echo esc_js(esc_html__("AI Search", "wp-job-portal")); ?>';

                var keywordLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['jobtitle']) ? wpjobportal::$_data['fields_titles_array']['jobtitle'] : '')); ?>';
                var cityLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['city']) ? wpjobportal::$_data['fields_titles_array']['city'] : '')); ?>';
                var categoryLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['jobcategory']) ? wpjobportal::$_data['fields_titles_array']['jobcategory'] : '')); ?>';
                var compLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['company']) ? wpjobportal::$_data['fields_titles_array']['company'] : '')); ?>';
                var tagsLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['tags']) ? wpjobportal::$_data['fields_titles_array']['tags'] : '')); ?>';
                var workplaceLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['workplace_type']) ? wpjobportal::$_data['fields_titles_array']['workplace_type'] : '')); ?>';
                var jobsalaryLbl = '<?php echo esc_js(wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['jobsalaryrange']) ? wpjobportal::$_data['fields_titles_array']['jobsalaryrange'] : '')); ?>';


                var html = '<span class="wpjp-active-filters-label">' + activeFiltersLbl + '</span>';
                html += '<div class="wpjp-active-filters-list">';

                // A. Handle Text Inputs from filter.php
                var jobtitleVal = jQuery('input[name="jobtitle"]').val();
                if (jobtitleVal && jobtitleVal.trim() !== '') {
                    hasFilters = true;
                    html += '<div class="wpjp-filter-group">';
                    html += '<span class="wpjp-filter-group-name">' + keywordLbl + ': </span>';
                    html += '<span class="wpjp-filter-tag wpjp-filter-tag-text" data-input-name="jobtitle">';
                    html += escapeHTML(jobtitleVal); // SECURITY FIX
                    html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                    html += '</span></div>';
                }

                // Handle City via TokenInput API
                var cityInput = jQuery('input[name="city"]');
                var cityVal = cityInput.val();
                if (cityVal && cityVal.trim() !== '') {
                    var tokens = [];
                    try {
                        tokens = jQuery('.wpjobportal-job-listing-city-field').tokenInput("get");
                    } catch(e) {}

                    if (tokens && tokens.length > 0) {
                        hasFilters = true;
                        html += '<div class="wpjp-filter-group">';
                        html += '<span class="wpjp-filter-group-name">' + cityLbl + ': </span>';

                        tokens.forEach(function(token) {
                            html += '<span class="wpjp-filter-tag wpjp-filter-tag-city" data-val="' + escapeHTML(String(token.id)) + '">';
                            html += escapeHTML(token.name); // SECURITY FIX
                            html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                            html += '</span>';
                        });
                        html += '</div>';
                    } else {
                        hasFilters = true;
                        html += '<div class="wpjp-filter-group">';
                        html += '<span class="wpjp-filter-group-name">' + cityLbl + ': </span>';
                        html += '<span class="wpjp-filter-tag wpjp-filter-tag-text" data-input-name="city">';
                        html += escapeHTML(cityVal); // SECURITY FIX
                        html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                        html += '</span></div>';
                    }
                }

                // --- NEW: Handle Category (Multiselect Dropdown) ---
                var categorySelect = jQuery('select[name="category[]"]');
                var selectedCategories = categorySelect.val();
                if (selectedCategories && selectedCategories.length > 0) {
                    var hasValidCategory = false;
                    var catHtml = '<div class="wpjp-filter-group">';
                    catHtml += '<span class="wpjp-filter-group-name">' + categoryLbl + ': </span>';

                    categorySelect.find('option:selected').each(function() {
                        if (jQuery(this).val() !== '') {
                            hasValidCategory = true;
                            catHtml += '<span class="wpjp-filter-tag wpjp-filter-tag-category" data-val="' + escapeHTML(jQuery(this).val()) + '">';
                            catHtml += escapeHTML(jQuery(this).text()); // SECURITY FIX
                            catHtml += '<span class="wpjp-filter-tag-remove">&times;</span>';
                            catHtml += '</span>';
                        }
                    });
                    catHtml += '</div>';

                    if (hasValidCategory) {
                        hasFilters = true;
                        html += catHtml;
                    }
                }

                // --- NEW: Handle Company (Multiselect Dropdown) ---
                var companySelect = jQuery('select[name="company[]"]');
                var selectedCompanies = companySelect.val();
                if (selectedCompanies && selectedCompanies.length > 0) {
                    var hasValidCompany = false;
                    var compHtml = '<div class="wpjp-filter-group">';
                    compHtml += '<span class="wpjp-filter-group-name">' + compLbl + ': </span>';

                    companySelect.find('option:selected').each(function() {
                        if (jQuery(this).val() !== '') {
                            hasValidCompany = true;
                            compHtml += '<span class="wpjp-filter-tag wpjp-filter-tag-company" data-val="' + escapeHTML(jQuery(this).val()) + '">';
                            compHtml += escapeHTML(jQuery(this).text()); // SECURITY FIX
                            compHtml += '<span class="wpjp-filter-tag-remove">&times;</span>';
                            compHtml += '</span>';
                        }
                    });
                    compHtml += '</div>';

                    if (hasValidCompany) {
                        hasFilters = true;
                        html += compHtml;
                    }
                }


                var aiVal = jQuery('input[name="aijobsearcch"]').val();
                if (aiVal && aiVal.trim() !== '') {
                    hasFilters = true;
                    html += '<div class="wpjp-filter-group">';
                    html += '<span class="wpjp-filter-group-name">' + aiSearchLbl + ': </span>';
                    html += '<span class="wpjp-filter-tag wpjp-filter-tag-text" data-input-name="aijobsearcch">';
                    html += escapeHTML(aiVal); // SECURITY FIX
                    html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                    html += '</span></div>';
                }

                // B. Handle Dropdown Menu Groups
                jQuery('.wpjp-joblisting-dropdown-menu').each(function() {
                    var $menu = jQuery(this);
                    var groupName = $menu.find('.wpjp-joblisting-dropdown-header').text().trim();
                    var $checked = $menu.find('input[type="checkbox"]:checked');

                    if ($checked.length > 0) {
                        hasFilters = true;
                        html += '<div class="wpjp-filter-group">';
                        html += '<span class="wpjp-filter-group-name">' + escapeHTML(groupName) + ': </span>';

                        $checked.each(function() {
                            var labelText = jQuery(this).parent().text().trim();
                            var val = jQuery(this).val();
                            var inputClass = jQuery(this).attr('class');

                            html += '<span class="wpjp-filter-tag" data-input-class="' + escapeHTML(inputClass) + '" data-val="' + escapeHTML(val) + '">';
                            html += escapeHTML(labelText); // SECURITY FIX
                            html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                            html += '</span>';
                        });
                        html += '</div>';
                    }
                });


                // C. Handle Salary Filter Tag
                var salaryTypeVal = $salaryTypeDropdown.val(); // PERFORMANCE FIX
                if (salaryTypeVal && salaryTypeVal !== '') {
                    hasFilters = true;
                    var salaryTagText = "Applied";
                    var curSym = '<?php echo esc_js(wpjobportal::$_config->getConfigValue("job_currency_symbol") ? wpjobportal::$_config->getConfigValue("job_currency_symbol") : "$"); ?>';

                    if (salaryTypeVal == '1') {
                        salaryTagText = "Negotiable";
                    } else if (salaryTypeVal == '2') {
                        var fVal = jQuery('.wpjp-joblisting-filter-salaryfixed').val();
                        salaryTagText = "Fixed" + (fVal ? ": " + curSym + escapeHTML(fVal) : ""); // SECURITY FIX
                    } else if (salaryTypeVal == '3') {
                        var sMinDisp = $displayMin.val();
                        var sMaxDisp = $displayMax.val();
                        if (sMinDisp || sMaxDisp) salaryTagText = curSym + escapeHTML(sMinDisp || '0') + " - " + curSym + escapeHTML(sMaxDisp || 'Max');
                        else salaryTagText = "Range";
                    }

                    html += '<div class="wpjp-filter-group">';
                    html += '<span class="wpjp-filter-group-name">'+jobsalaryLbl+': </span>';
                    html += '<span class="wpjp-filter-tag wpjp-filter-tag-salary">';
                    html += salaryTagText;
                    html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                    html += '</span></div>';
                }
/*
                // --- Handle Workplace Type ---
                var selectedWorkplace = jQuery('.wpjp-joblisting-filter-workplace:checked');
                if (selectedWorkplace.length > 0) {
                    hasFilters = true;
                    html += '<div class="wpjp-filter-group">';
                    html += '<span class="wpjp-filter-group-name">' + workplaceLbl + ': </span>';
                    selectedWorkplace.each(function() {
                        var val = jQuery(this).val();
                        var label = jQuery(this).parent().text().trim();
                        html += '<span class="wpjp-filter-tag wpjp-filter-tag-workplace" data-val="' + val + '">' + label + '<span class="wpjp-filter-tag-remove">&times;</span></span>';
                    });
                    html += '</div>';
                }
*/
                // C. Handle Sorting Tag
                var sortVal = jQuery('#wpjp-joblisting-sort').val();
                var sortText = jQuery('#wpjp-joblisting-sort option:selected').text();
                if (sortVal && sortVal !== 'newest') { // SAFEGUARD: Check if sortVal exists
                    hasFilters = true;
                    html += '<div class="wpjp-filter-group">';
                    html += '<span class="wpjp-filter-group-name">' + sortedByLbl + ': </span>';
                    html += '<span class="wpjp-filter-tag wpjp-filter-tag-sort">';
                    html += escapeHTML(sortText); // SECURITY FIX
                    html += '</span>';
                    html += '</div>';
                }


                // --- Handle Custom Tags ---
                var tagsVal = jQuery('#wpjp-active-tags').val();
                if (tagsVal && tagsVal !== '') {
                    var tagsArray = tagsVal.split(',').map(function(item) { return item.trim(); }).filter(function(item) { return item !== ''; });
                    if (tagsArray.length > 0) {
                        hasFilters = true;
                        var tagsHtml = '<div class="wpjp-filter-group">';
                        tagsHtml += '<span class="wpjp-filter-group-name">' + tagsLbl + ': </span>';

                        tagsArray.forEach(function(tag) {
                            tagsHtml += '<span class="wpjp-filter-tag wpjp-filter-tag-custom-tag" data-val="' + escapeHTML(tag) + '">';
                            tagsHtml += escapeHTML(tag); // SECURITY FIX
                            tagsHtml += '<span class="wpjp-filter-tag-remove">&times;</span>';
                            tagsHtml += '</span>';
                        });
                        tagsHtml += '</div>';
                        html += tagsHtml;
                    }
                }
                // --- Handle Urgent Filter Tag ---
                var urgentVal = jQuery('#wpjp-is-urgent-filter').val();
                if (urgentVal === '1') {
                    hasFilters = true;
                    html += '<div class="wpjp-filter-group">';
                    html += '<span class="wpjp-filter-group-name"><?php echo esc_js(esc_html__("Urgent", "wp-job-portal")); ?>: </span>';
                    html += '<span class="wpjp-filter-tag wpjp-filter-tag-urgent" data-val="1">';
                    html += '<?php echo esc_js(esc_html__("Yes", "wp-job-portal")); ?>';
                    html += '<span class="wpjp-filter-tag-remove">&times;</span>';
                    html += '</span></div>';
                }
                html += '</div>';
                if (hasFilters) {
                    $displayContainer.html(html).slideDown(200);
                } else {
                    $displayContainer.slideUp(200);
                }
            }

            // --- 2. Central Filter Data Extraction ---
            function getFilterData() {
                const selectedTypes = jQuery('.wpjp-joblisting-filter-type:checked').map(function() { return this.value; }).get();
                const selectedStatus = jQuery('.wpjp-joblisting-filter-status:checked').map(function() { return this.value; }).get();
                const selectedcareeers = jQuery('.wpjp-joblisting-filter-career:checked').map(function() { return this.value; }).get();
                const selectedSalaries = jQuery('.wpjp-joblisting-filter-salary:checked').map(function() { return this.value; }).get();
                const selectedDates = jQuery('.wpjp-joblisting-filter-date:checked').map(function() { return this.value; }).get();
                const sortBy = jQuery('#wpjp-joblisting-sort').val() || 'newest';

                const jobtitle = jQuery('input[name="jobtitle"]').val() || '';
                const city = jQuery('input[name="city"]').val() || '';
                const aijobsearcch = jQuery('input[name="aijobsearcch"]').val() || '';

                const category = jQuery('select[name="category[]"]').val() || [];
                const company = jQuery('select[name="company[]"]').val() || [];

                // NEW: Salary Values (PERFORMANCE FIX: using cached variables where applicable)
                const salarytype = $salaryTypeDropdown.val() || '';
                const salaryfixed = jQuery('.wpjp-joblisting-filter-salaryfixed').val() || '';
                const salarymin = $hiddenMin.val() || '';
                const salarymax = $hiddenMax.val() || '';
                const salaryduration = jQuery('.wpjp-joblisting-filter-salaryduration').val() || '';
                const salaryrangetype = jQuery('.wpjp-joblisting-filter-salaryrangetype').val() || ''; // LOGIC FIX: Replaced duplicate variable mapping

                const tags = jQuery('#wpjp-active-tags').val() || '';
                const selectedWorkplace = jQuery('.wpjp-joblisting-filter-workplace:checked').map(function() { return this.value; }).get();
                const is_urgent = jQuery('#wpjp-is-urgent-filter').val() || ''; // <--- NEW

                return {
                    jobtype: selectedTypes,
                    jobstatus: selectedStatus,
                    careerlevel: selectedcareeers,
                    salaries: selectedSalaries,
                    dates: selectedDates,
                    sort: sortBy,
                    jobtitle: jobtitle,
                    city: city,
                    category: category,
                    company: company,
                    aijobsearcch: aijobsearcch,
                    salarytype: salarytype,
                    salaryfixed: salaryfixed,
                    salarymin: salarymin,
                    salarymax: salarymax,
                    salaryduration: salaryduration,
                    salaryrangetype: salaryrangetype, // LOGIC FIX: Was incorrectly mapped to salaryduration
                    workplace_type: selectedWorkplace,
                    tags: tags,
                    is_urgent: is_urgent,
                };
            }

            // --- 3. UI State Management ---
            function updateGlobalClearVisibility(filters) {
                const isAnyFilterActive =
                    filters.jobtype.length > 0 ||
                    filters.jobstatus.length > 0 ||
                    filters.careerlevel.length > 0 ||
                    filters.salaries.length > 0 ||
                    filters.dates.length > 0 ||
                    filters.jobtitle !== '' ||
                    filters.city !== '' ||
                    (Array.isArray(filters.category) && filters.category.length > 0) ||
                    (Array.isArray(filters.company) && filters.company.length > 0) ||
                    filters.aijobsearcch !== '' ||
                    filters.tags !== '' ||
                    filters.is_urgent == '1' ||
                    filters.salarytype !== '';

                if (isAnyFilterActive) {
                    jQuery('#wpjp-joblisting-global-clear-container').css('display', 'block');
                } else {
                    jQuery('#wpjp-joblisting-global-clear-container').css('display', 'none');
                }
            }

            function onFiltersChanged() {
                var filters = getFilterData();
                filters.pagenum = 1;

                // Call the new extracted function
                updateGlobalClearVisibility(filters);

                updateActiveFilterTags();
                printJobsByFilter(filters);
            }

            // --- 4. AJAX Call Execution ---
            /*
            function printJobsByFilter(filters) {
                var $wrapper = jQuery(' .wjportal-jobs-list-wrapper , .wjportal-elegant-addon-jobs-list-wrapper');
                var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";
                var jppage_id = "<?php echo absint(wpjobportal::wpjobportal_getPageid()); ?>";

                $wrapper.addClass('wpjp-ajax-loading-active');

                if (jQuery('#wpjp-list-loader').length === 0) {
                    $wrapper.append('<div id="wpjp-list-loader"><div class="wpjp-spinner"></div></div>');
                }

                var postData = {
                    action: 'wpjobportal_ajax',
                    wpjobportalme: 'job',
                    task: 'getAjaxJobs',
                    wpjobportalpageid: jppage_id,
                    _wpnonce: '<?php echo esc_attr(wp_create_nonce("wpjobportal_get_ajax_jobs_list")); ?>',
                    current_url_for_pagination: <?php echo wp_json_encode(esc_url_raw(add_query_arg(array('pagenum'=>'%#%')))); ?>
                };

                jQuery.extend(postData, filters);

                jQuery.post(ajaxurl, postData, function(data) {
                    $wrapper.html(data);
                    $wrapper.removeClass('wpjp-ajax-loading-active');
                }).fail(function() {
                    $wrapper.removeClass('wpjp-ajax-loading-active');
                    jQuery('#wpjp-list-loader').remove();
                });
            }
            */
            function printJobsByFilter(filters) {
                var $wrapper = jQuery(' .wjportal-jobs-list-wrapper , .wjportal-elegant-addon-jobs-list-wrapper');
                var $wrapper2 = jQuery('.wpj-jp-list-wrp.wpj-jp-main-job-list');
                var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";
                var jppage_id = "<?php echo absint(wpjobportal::wpjobportal_getPageid()); ?>";

                $wrapper.addClass('wpjp-ajax-loading-active');
                $wrapper2.addClass('wpjp-ajax-loading-active');

                if (jQuery('#wpjp-list-loader').length === 0) {
                    $wrapper.append('<div id="wpjp-list-loader"><div class="wpjp-spinner"></div></div>');
                    $wrapper2.append('<div id="wpjp-list-loader"><div class="wpjp-spinner"></div></div>');
                }

                var postData = {
                    action: 'wpjobportal_ajax',
                    wpjobportalme: 'job',
                    task: 'getAjaxJobs',
                    wpjobportalpageid: jppage_id,
                    _wpnonce: '<?php echo esc_attr(wp_create_nonce("wpjobportal_get_ajax_jobs_list")); ?>',
                    current_url_for_pagination: <?php echo wp_json_encode(esc_url_raw(add_query_arg(array('pagenum'=>'%#%')))); ?>
                };

                jQuery.extend(postData, filters);
                jQuery.post(ajaxurl, postData, function(data) {
                    $wrapper.html(data);
                    $wrapper2.replaceWith(data);
                    $wrapper.removeClass('wpjp-ajax-loading-active');
                    $wrapper2.removeClass('wpjp-ajax-loading-active');
                }).fail(function() {
                    $wrapper.removeClass('wpjp-ajax-loading-active');
                    $wrapper2.removeClass('wpjp-ajax-loading-active');
                    jQuery('#wpjp-list-loader').remove();
                });
            }


            // // --- 4.5 NEW: Intercept AJAX Pagination Clicks ---
            // jQuery(document).on('click', '.wpjp-ajax-pagination-wrapper .page-numbers', function(e) {
            //     e.preventDefault();
            //     var $link = jQuery(this);

            //     if ($link.hasClass('current')) return;

            //     var href = $link.attr('href');
            //     if(!href) return;

            //     var pagenum = 1;
            //     var match = href.match(/pagenum=(\d+)/);
            //     if(match && match[1]) {
            //         pagenum = match[1];
            //     }

            //     var filters = getFilterData();
            //     filters.pagenum = pagenum;

            //     printJobsByFilter(filters);

            //     jQuery('html, body').animate({
            //         scrollTop: jQuery(".wjportal-jobs-list-top-filter").offset().top - 20
            //     }, 400);
            // });
            // --- 4.5 NEW: Intercept AJAX Pagination Clicks ---
            // Added #wpj-jp-pagination selectors to hijack the theme's UI pagination calls
            jQuery(document).on('click', '.wpjp-ajax-pagination-wrapper .page-numbers, #wpj-jp-pagination a, #wpj-jp-pagination .page-numbers', function(e) {
                e.preventDefault();
                var $link = jQuery(this);

                if ($link.hasClass('current')) return;

                var href = $link.attr('href');
                if(!href) return;

                var pagenum = 1;

                // Match standard query parameters (e.g., ?pagenum=2)
                var match = href.match(/pagenum=(\d+)/);

                // Fallback: Match WordPress pretty permalinks (e.g., /page/2/)
                if (!match) {
                    match = href.match(/\/page\/(\d+)/);
                }

                if(match && match[1]) {
                    pagenum = match[1];
                }

                var filters = getFilterData();
                filters.pagenum = pagenum;

                printJobsByFilter(filters);

                // Scroll to top of filters when pagination is clicked
                jQuery('html, body').animate({
                    scrollTop: jQuery(".wjportal-jobs-list-top-filter").offset().top - 20
                }, 400);
            });

            // --- 5. Intercept filter.php Form Submission & Reset ---
            jQuery('#job_form').on('submit', function(e) {
                e.preventDefault();
                onFiltersChanged();
            });

            // --- Listen for Category & Company Select Change (Chosen Plugin Support) ---
            jQuery('select[name="company[]"]').on('change', function() {
            //jQuery('select[name="category[]"], select[name="company[]"]').on('change', function() {
                onFiltersChanged();
            });

            jQuery('#reset-newest-jobfilter').on('click', function(e) {
                setTimeout(function() {
                    jQuery('input[name="aijobsearcch"]').val('');
                    jQuery('select[name="category[]"]').val([]).trigger('chosen:updated');
                    onFiltersChanged();
                }, 50);
            });

            // --- 6. Remove individual tag click handler ---
            jQuery(document).on('click', '.wpjp-filter-tag-remove', function() {
                var $tag = jQuery(this).closest('.wpjp-filter-tag');

                if ($tag.hasClass('wpjp-filter-tag-city')) {
                    var cityIdToRemove = $tag.data('val');
                    try {
                        jQuery('.wpjobportal-job-listing-city-field').tokenInput("remove", {id: parseInt(cityIdToRemove, 10)});
                        jQuery('.wpjobportal-job-listing-city-field').tokenInput("remove", {id: String(cityIdToRemove)});
                    } catch(e) {
                        jQuery('input[name="city"]').val('');
                    }
                    setTimeout(function() { onFiltersChanged(); }, 100);

                } else if ($tag.hasClass('wpjp-filter-tag-category')) {
                    var catIdToRemove = $tag.data('val');
                    var $select = jQuery('select[name="category[]"]');
                    $select.find('option[value="' + catIdToRemove + '"]').prop('selected', false);
                    $select.trigger('chosen:updated');
                    onFiltersChanged();

                } else if ($tag.hasClass('wpjp-filter-tag-salary')) {
                    // PERFORMANCE FIX: Use cached variables
                    $salaryTypeDropdown.val('');
                    $salaryHiddenFields.val('').hide();

                    $salaryRangeWrapper.hide();
                    $inputMin.val(0);
                    $inputMax.val(18);
                    updateSalarySliderUI();

                    jQuery('.wpjp-is-salary').removeClass('has-selections');
                    jQuery('.wpjp-is-salary').siblings('.wpjp-joblisting-dropdown-toggle').removeClass('wpjp-has-active-filters');

                    onFiltersChanged();

                } else if ($tag.hasClass('wpjp-filter-tag-text')) {
                    var inputName = $tag.data('input-name');
                    // SECURITY FIX: escape inputName to prevent attribute injection
                    jQuery('input[name="' + escapeHTML(inputName) + '"]').val('');
                    onFiltersChanged();
                } else if ($tag.hasClass('wpjp-filter-tag-company')) {
                    var compIdToRemove = $tag.data('val');
                    var $compSelectTag = jQuery('select[name="company[]"]');
                    $compSelectTag.find('option[value="' + compIdToRemove + '"]').prop('selected', false);
                    $compSelectTag.trigger('chosen:updated');
                    onFiltersChanged();
                } else if ($tag.hasClass('wpjp-filter-tag-custom-tag')) {
                    var tagToRemove = String($tag.data('val'));
                    var currentTags = jQuery('#wpjp-active-tags').val().split(',').map(function(item) { return item.trim(); });
                    var newTags = currentTags.filter(function(item) { return item !== tagToRemove && item !== ''; });
                    jQuery('#wpjp-active-tags').val(newTags.join(','));
                    onFiltersChanged();
                } else if ($tag.hasClass('wpjp-filter-tag-workplace')) {
                    var valToRemove = String($tag.data('val'));
                    jQuery('.wpjp-joblisting-filter-workplace[value="' + valToRemove + '"]').prop('checked', false);
                    onFiltersChanged();
                }else if ($tag.hasClass('wpjp-filter-tag-urgent')) {
                    jQuery('#wpjp-is-urgent-filter').val('');
                    onFiltersChanged();
                } else {
                    var inputClass = $tag.data('input-class');
                    var val = $tag.data('val');
                    // SECURITY FIX: Escape variables used in selectors
                    jQuery('.' + escapeHTML(inputClass) + '[value="' + escapeHTML(String(val)) + '"]').prop('checked', false).trigger('change');
                }
            });

            // --- 7. Dropdown Toggle & Click-Outside Logic ---
            jQuery('.wpjp-joblisting-dropdown-toggle').on('click', function(e) {
                e.stopPropagation();
                const $menu = jQuery(this).siblings('.wpjp-joblisting-dropdown-menu');
                const $btn = jQuery(this);

                jQuery('.wpjp-joblisting-dropdown-menu').not($menu).removeClass('is-open');
                jQuery('.wpjp-joblisting-dropdown-toggle').not($btn).removeClass('is-active');

                $menu.toggleClass('is-open');
                $btn.toggleClass('is-active');

                // A panel opened near the right edge would run off the screen:
                // measure it once open and, if so, align it to the button's right.
                $menu.removeClass('wpjp-align-end');
                if ($menu.hasClass('is-open') && $menu[0].getBoundingClientRect().right > document.documentElement.clientWidth - 8) {
                    $menu.addClass('wpjp-align-end');
                }
            });

            jQuery(document).on('click', function(e) {
                if (!jQuery(e.target).closest('.wpjp-joblisting-dropdown-container').length) {
                    jQuery('.wpjp-joblisting-dropdown-menu').removeClass('is-open');
                    jQuery('.wpjp-joblisting-dropdown-toggle').removeClass('is-active');
                }
            });

            jQuery('.wpjp-joblisting-dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });

            // --- 8. Checkbox Filters Change ---
            jQuery('.wpjp-joblisting-dropdown-menu input[type="checkbox"]').on('change', function() {
                const $menu = jQuery(this).closest('.wpjp-joblisting-dropdown-menu');
                const $toggleBtn = $menu.siblings('.wpjp-joblisting-dropdown-toggle');

                if ($menu.find('input[type="checkbox"]:checked').length > 0) {
                    $menu.addClass('has-selections');
                    $toggleBtn.addClass('wpjp-has-active-filters');
                } else {
                    $menu.removeClass('has-selections');
                    $toggleBtn.removeClass('wpjp-has-active-filters');
                }
                onFiltersChanged();
            });

            // --- 8.5 Show/Hide Fields in Salary Popup & Trigger AJAX ---
            $salaryTypeDropdown.on('change', function() { // PERFORMANCE FIX: Used cached element
                var salarytype = jQuery(this).val();
                var $menu = jQuery(this).closest('.wpjp-joblisting-dropdown-menu');
                var $toggleBtn = $menu.siblings('.wpjp-joblisting-dropdown-toggle');

                if(salarytype == 1){ //negotiable
                    $menu.find('.wpjp-joblisting-filter-salaryfixed, #wpjp-salary-range-wrapper, .wpjp-joblisting-filter-salaryduration').hide();
                } else if(salarytype == 2){ //fixed
                    $menu.find('.wpjp-joblisting-filter-salaryfixed, .wpjp-joblisting-filter-salaryduration').show();
                    $salaryRangeWrapper.hide(); // PERFORMANCE FIX
                } else if(salarytype == 3){ //range
                    $menu.find('#wpjp-salary-range-wrapper, .wpjp-joblisting-filter-salaryduration').show();
                    $menu.find('.wpjp-joblisting-filter-salaryfixed').hide();
                } else { //not selected
                    $menu.find('.wpjp-joblisting-filter-salaryfixed, #wpjp-salary-range-wrapper, .wpjp-joblisting-filter-salaryduration').hide();
                }

                if(salarytype !== '') {
                    $menu.addClass('has-selections');
                    $toggleBtn.addClass('wpjp-has-active-filters');
                } else {
                    $menu.removeClass('has-selections');
                    $toggleBtn.removeClass('wpjp-has-active-filters');
                }

                // PERFORMANCE FIX: Use cached variables to clear hidden fields immediately
                $salaryHiddenFields.val('');
                $inputMin.val(0);
                $inputMax.val(18);
                if (typeof updateSalarySliderUI === "function") updateSalarySliderUI();

                onFiltersChanged();
            });

            // Trigger filter update on changing fixed numbers/duration
            jQuery('.wpjp-joblisting-filter-salaryfixed, .wpjp-joblisting-filter-salaryduration').on('change', function() {
                onFiltersChanged();
            });

            // --- 9. Individual Dropdown Clear Buttons ---
            jQuery('.wpjp-joblisting-btn-clear').on('click', function(e) {
                e.stopPropagation();
                const $menu = jQuery(this).closest('.wpjp-joblisting-dropdown-menu');
                const $toggleBtn = $menu.siblings('.wpjp-joblisting-dropdown-toggle');

                if ($menu.hasClass('wpjp-is-salary')) {
                    // PERFORMANCE FIX: Use cached variables
                    $salaryTypeDropdown.val('');
                    $salaryHiddenFields.val('').hide();
                    $salaryRangeWrapper.hide();
                    $inputMin.val(0);
                    $inputMax.val(18);
                    updateSalarySliderUI();
                } else {
                    $menu.find('input[type="checkbox"]').prop('checked', false);
                }

                $menu.removeClass('has-selections');
                $toggleBtn.removeClass('wpjp-has-active-filters');
                onFiltersChanged();
            });

            // --- 10. Global "Clear All" Button ---
            jQuery('#wpjp-joblisting-btn-clear-all , #reset-newest-jobfilter').on('click', function() {
                jQuery('.wpjp-joblisting-dropdown-menu input[type="checkbox"]').prop('checked', false);

                // PERFORMANCE FIX: Clear salary fields safely using cache
                $salaryTypeDropdown.val('');
                $salaryHiddenFields.val('').hide();
                $salaryRangeWrapper.hide();
                $inputMin.val(0);
                $inputMax.val(18);
                updateSalarySliderUI();

                jQuery('.wpjp-joblisting-dropdown-menu').removeClass('has-selections');
                jQuery('.wpjp-joblisting-dropdown-toggle').removeClass('wpjp-has-active-filters');
                jQuery('#wpjp-joblisting-sort').val('newest');

                jQuery('input[name="jobtitle"]').val('');
                jQuery('input[name="aijobsearcch"]').val('');

                var $catSelect = jQuery('select[name="category[]"]');
                if($catSelect.length) {
                    $catSelect.val([]).trigger('chosen:updated'); // Changed '' to []
                }

                var $compSelect = jQuery('select[name="company[]"]');
                if($compSelect.length) {
                    $compSelect.val([]).trigger('chosen:updated'); // Changed '' to []
                }

                try {
                    jQuery('.wpjobportal-job-listing-city-field').tokenInput('clear', {focus: false});
                } catch(e) {
                    jQuery('input[name="city"]').val('');
                }

                jQuery('#wpjp-active-tags').val('');
                jQuery('#wpjp-is-urgent-filter').val(''); // <--- NEW: Clear urgent value
                jQuery('.wpjp-joblisting-dropdown-menu').removeClass('is-open');
                jQuery('.wpjp-joblisting-dropdown-toggle').removeClass('is-active');
                onFiltersChanged();
            });

            jQuery('#wpjp-joblisting-sort').on('change', onFiltersChanged);


            // --- 11. Salary Slider Controller ---
            const salarySteps = [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 120, 140, 160, 180, 200, 250, 300, 500];
            const maxIdx = salarySteps.length - 1;

            // (NOTE: $inputMin, $inputMax, etc., have been hoisted to the top of the ready function)

            $inputMin.attr('max', maxIdx);
            $inputMax.attr('max', maxIdx);

            let phpMin = <?php echo isset($wpjobportal_s_min) && $wpjobportal_s_min !== '' ? intval($wpjobportal_s_min) / 1000 : 0; ?>;
            let phpMax = <?php echo isset($wpjobportal_s_max) && $wpjobportal_s_max !== '' ? intval($wpjobportal_s_max) / 1000 : 500; ?>;

            let initMinIdx = 0;
            let initMaxIdx = maxIdx;

            if (phpMin > 0) {
                for(let i=0; i<salarySteps.length; i++) { if(salarySteps[i] >= phpMin) { initMinIdx = i; break; } }
            }
            if (phpMax > 0 && phpMax < 500) {
                for(let i=0; i<salarySteps.length; i++) { if(salarySteps[i] >= phpMax) { initMaxIdx = i; break; } }
            }

            if ($inputMin.length > 0) {
                $inputMin.val(initMinIdx);
                $inputMax.val(initMaxIdx);
            }

            function updateSalarySliderUI() {
                if ($inputMin.length === 0) return;

                let minIdx = parseInt($inputMin.val());
                let maxIdxVal = parseInt($inputMax.val());

                if (minIdx >= maxIdxVal) {
                    $inputMin.val(maxIdxVal - 1);
                    minIdx = maxIdxVal - 1;
                }

                let minVal = salarySteps[minIdx];
                let maxVal = salarySteps[maxIdxVal];

                let minDisplay = minVal === 0 ? "0" : minVal + "k";
                let maxDisplay = maxVal === 500 ? "500k+" : maxVal + "k";

                $displayMin.val(minDisplay);
                $displayMax.val(maxDisplay);

                let actualMin = minVal === 0 ? "" : minVal * 1000;
                let actualMax = maxVal === 500 ? "" : maxVal * 1000;

                $hiddenMin.val(actualMin);
                $hiddenMax.val(actualMax);

                const minPercent = (minIdx / maxIdx) * 100;
                const maxPercent = (maxIdxVal / maxIdx) * 100;

                $trackFill.css({
                    'left': minPercent + '%',
                    'right': (100 - maxPercent) + '%'
                });

                $thumbMin.css('left', minPercent + '%');
                $thumbMax.css('left', maxPercent + '%');
            }

            $rangeArea.on('mousedown touchstart', function(e) {
                if (jQuery(e.target).hasClass('wpjp-range-input')) return;

                const rect = this.getBoundingClientRect();
                const x = (e.clientX || (e.originalEvent.touches ? e.originalEvent.touches[0].clientX : 0)) - rect.left;
                const percent = Math.max(0, Math.min(100, (x / rect.width) * 100));
                const clickedIdx = Math.round((percent / 100) * maxIdx);

                const currentMin = parseInt($inputMin.val());
                const currentMax = parseInt($inputMax.val());

                if (Math.abs(clickedIdx - currentMin) < Math.abs(clickedIdx - currentMax)) {
                    $inputMin.val(Math.min(clickedIdx, currentMax - 1)).trigger('mousedown');
                } else {
                    $inputMax.val(Math.max(clickedIdx, currentMin + 1)).trigger('mousedown');
                }
                updateSalarySliderUI();
                onFiltersChanged();
            });

            $inputMin.on('input', updateSalarySliderUI);
            $inputMax.on('input', updateSalarySliderUI);

            $inputMin.on('change', onFiltersChanged);
            $inputMax.on('change', onFiltersChanged);

            $inputMin.on('mousedown touchstart', function() {
                jQuery(this).css('z-index', 15);
                $inputMax.css('z-index', 10);
                $thumbMin.addClass('wpjp-is-active');
            });

            $inputMax.on('mousedown touchstart', function() {
                jQuery(this).css('z-index', 15);
                $inputMin.css('z-index', 10);
                $thumbMax.addClass('wpjp-is-active');
            });

            jQuery(window).on('mouseup touchend', function() {
                jQuery('.wpjp-custom-thumb').removeClass('wpjp-is-active');
            });

            updateSalarySliderUI();

            // --- 12. Tag Click Integration from Job Listings ---
            jQuery(document).on('click', '.wpjp-clickable-tag', function(e) {
                e.preventDefault();
                var newTag = jQuery(this).data('tag') || jQuery(this).text().trim();
                if (!newTag) return;

                var currentTagsStr = jQuery('#wpjp-active-tags').val();
                var tagsArray = currentTagsStr ? currentTagsStr.split(',').map(function(item) { return item.trim(); }) : [];

                if (tagsArray.indexOf(newTag) === -1) {
                    tagsArray.push(newTag);
                    jQuery('#wpjp-active-tags').val(tagsArray.join(','));
                    onFiltersChanged();

                    jQuery('html, body').animate({
                        scrollTop: jQuery(".wjportal-jobs-list-top-filter").offset().top - 20
                    }, 400);
                }
            });

            // --- Boot Sequence ---
            initializeButtonStates();
            // Check visibility on load based on pre-populated PHP data
            updateGlobalClearVisibility(getFilterData());
            setTimeout(function() {
                updateActiveFilterTags();
            }, 100);

        });
    })(jQuery);
</script>
<style>
    /* ==========================================================================
       The tokens are not restated here
       ==========================================================================

       This block declared the whole :root set - every colour and every font
       size - inside the page. An inline <style> comes after every enqueued
       stylesheet, so on any page carrying these filters it replaced the site
       owner's saved theme with the shipped defaults: a site on Ruby Red went
       cyan here and nowhere else, and a changed body size was ignored.

       job_portal_variables.css already carries them, and is enqueued ahead of
       this page.
       ========================================================================== */

    /* ==========================================================================
       Filter Component Scoped Styles (High Specificity)
       ========================================================================== */
     .wjportal-jobs-list-top-filter {
        box-sizing: border-box;
        /* The site's typeface, like the rest of the portal. Naming Inter here
           put a second sans on every jobs list - the filter bar in one face
           and the cards beside it in another - and under a theme whose body
           font is a serif it stood out further still. */
        font-family: inherit;
        color: var(--wpjp-body-font-color);
        margin: 0 auto;
        padding: 2rem 1rem;
        background-color: transparent;
        font-size: var(--wpjp-body-font-size);
    }

     .wjportal-jobs-list-top-filter *,
     .wjportal-jobs-list-top-filter *::before,
     .wjportal-jobs-list-top-filter *::after {
        box-sizing: inherit;
    }

    /* --- Filter Buttons --- */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-filters-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-filter {
        padding: 0.5rem 1rem;
        border: 1px solid var(--wpjp-border-color);
        border-radius: 0.5rem;
        font-size: 14px;
        font-weight: 700;
        color: var(--wpjp-body-font-color);
        background: var(--wpjp-card-background);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        font-family: inherit;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-filter svg {
        transition: transform 0.2s ease;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-filter:hover {
        background: var(--wpjp-background-color);
        color: var(--wpjp-secondary-color);
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-filter.is-active {
        background: var(--wpjp-background-color);
        border-color: var(--wpjp-border-color);
        color: var(--wpjp-secondary-color);
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-filter.is-active svg {
        transform: rotate(180deg);
    }

    /* --- Dropdown Menus --- */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-container {
        position: relative;
        display: inline-block;
    }

     /*
      * A closed filter panel takes no space.
      *
      * Closed panels used to be hidden with visibility and opacity only, so the
      * fade could run - which left each one laid out at 220px under its button.
      * On a phone the right-most (Date Posted) reached past the screen and made
      * the whole jobs page scroll sideways, open or not. They are now removed
      * from layout when closed, and fade in on opening with an animation, which
      * unlike a transition runs when display changes.
      */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu {
        display: none;
        visibility: hidden;
        opacity: 0;
        transform: translateY(10px);
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 0.75rem;
        background: var(--wpjp-card-background);
        border: 1px solid var(--wpjp-border-color);
        border-radius: 1rem;
        padding: 0.75rem;
        box-shadow: var(--wpjp-card-shadow);
        z-index: 50;
        min-width: 220px;
        max-width: calc(100vw - 30px);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.is-open {
        display: block;
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
        animation: wpjpJoblistingDropdownIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

     /* Opened near the right edge, a panel lines up with its button's right side instead. */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-align-end {
        left: auto;
        right: 0;
    }

    @keyframes wpjpJoblistingDropdownIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* --- Colorful Dropdown Themes --- */
    /*
     * Sizes, not ratios, and none below the plugin's floor.
     *
     * This panel sized its labels in rem and small px - 0.7rem, 0.75rem, 11px,
     * 10px - which rendered as 11.2, 12, 11 and 10 beside a page whose
     * smallest size is 13. The rem ones are worse than small: they are read
     * against the root, so they ignore the body size the site owner set.
     */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-header {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 900;
        margin-bottom: 0.5rem;
        padding: 0 0.5rem;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-status { border-top: 4px solid var(--wpjp-primary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-status .wpjp-joblisting-dropdown-header { color: var(--wpjp-primary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-status .wpjp-joblisting-checkbox-label:hover { background: var(--wpjp-background-color); color: var(--wpjp-primary-color); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-career { border-top: 4px solid var(--wpjp-success-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-career .wpjp-joblisting-dropdown-header { color: var(--wpjp-success-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-career .wpjp-joblisting-checkbox-label:hover { background: var(--wpjp-background-color); color: var(--wpjp-success-color); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-type { border-top: 4px solid var(--wpjp-secondary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-type .wpjp-joblisting-dropdown-header { color: var(--wpjp-secondary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-type .wpjp-joblisting-checkbox-label:hover { background: var(--wpjp-background-color); color: var(--wpjp-secondary-color); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-workplace { border-top: 4px solid var(--wpjp-primary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-workplace .wpjp-joblisting-dropdown-header { color: var(--wpjp-primary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-workplace .wpjp-joblisting-checkbox-label:hover { background: var(--wpjp-background-color); color: var(--wpjp-primary-color); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-date { border-top: 4px solid var(--wpjp-danger-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-date .wpjp-joblisting-dropdown-header { color: var(--wpjp-danger-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-date .wpjp-joblisting-checkbox-label:hover { background: var(--wpjp-background-color); color: var(--wpjp-danger-color); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-salary { border-top: 4px solid var(--wpjp-warning-color, #f59e0b); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-salary .wpjp-joblisting-dropdown-header { color: var(--wpjp-warning-color, #f59e0b); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-footer {
        border-top: 1px solid var(--wpjp-border-color);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        display: none;
        justify-content: flex-end;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.has-selections .wpjp-joblisting-dropdown-footer {
        display: flex;
    }

    /* --- Actions / Sort / Clear All --- */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-actions-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-global-clear-container {
        display: none;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-clear-all {
        background: transparent;
        border: none;
        color: var(--wpjp-danger-color);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        padding: 0;
        transition: opacity 0.2s;
        text-decoration: underline;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-clear-all:hover {
        opacity: 0.8;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-sort-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-sort-label {
        font-size: 14px;
        font-weight: 700;
        color: var(--wpjp-body-font-color);
        min-width: fit-content;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-select {
        padding: 0.5rem 2.25rem 0.5rem 1rem;
        border: 1px solid var(--wpjp-border-color);
        border-radius: 0.5rem;
        font-size: 14px;
        font-weight: 700;
        color: var(--wpjp-secondary-color);
        background-color: var(--wpjp-card-background);
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23575757' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-select:hover {
        background-color: var(--wpjp-background-color);
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-select:focus {
        border-color: var(--wpjp-primary-color);
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-clear {
        font-size: 13px;
        font-weight: 700;
        color: var(--wpjp-danger-color);
        background: var(--wpjp-background-color);
        border: 1px solid var(--wpjp-border-color);
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-btn-clear:hover {
        background: var(--wpjp-danger-color);
        color: var(--wpjp-card-background);
        border-color: var(--wpjp-danger-color);
    }

    /* --- Checkboxes --- */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0.75rem;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: var(--wpjp-body-font-color);
        border-radius: 0.5rem;
        transition: background-color 0.2s, color 0.2s;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-checkbox-label input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        background-color: var(--wpjp-card-background);
        margin: 0;
        /* style.css pads every portal checkbox by 1rem; this one is a 1.25rem box. */
        padding: 0;
        box-sizing: border-box;
        font: inherit;
        color: currentColor;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid var(--wpjp-border-color);
        border-radius: 0.375rem;
        display: grid;
        place-content: center;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-checkbox-label input[type="checkbox"]::before {
        content: "";
        width: 0.65em;
        height: 0.65em;
        transform: scale(0);
        transition: 120ms transform ease-in-out;
        box-shadow: inset 1em 1em var(--wpjp-card-background);
        background-color: var(--wpjp-card-background);
        transform-origin: center;
        clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
    }

    /* Colorful Checkbox Active States using User Vars */
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-status input[type="checkbox"]:checked { background-color: var(--wpjp-primary-color); border-color: var(--wpjp-primary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-career input[type="checkbox"]:checked { background-color: var(--wpjp-success-color); border-color: var(--wpjp-success-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-type input[type="checkbox"]:checked { background-color: var(--wpjp-secondary-color); border-color: var(--wpjp-secondary-color); }
     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-date input[type="checkbox"]:checked { background-color: var(--wpjp-danger-color); border-color: var(--wpjp-danger-color); }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-checkbox-label input[type="checkbox"]:checked::before {
        transform: scale(1);
    }

    /* Specific AJAX Loading Styles */
     .wjportal-jobs-list-wrapper {
        position: relative;
        min-height: 200px;
        transition: opacity 0.3s ease, filter 0.3s ease;
    }
    .wjportal-elegant-addon-jobs-list-wrapper {
        position: relative;
        min-height: 200px;
        transition: opacity 0.3s ease, filter 0.3s ease;
    }


     .wjportal-jobs-list-wrapper.wpjp-ajax-loading-active {
        filter: blur(3px);
        opacity: 0.8;
        pointer-events: none;
    }

     .wjportal-elegant-addon-jobs-list-wrapper.wpjp-ajax-loading-active {
        filter: blur(3px);
        opacity: 0.8;
        pointer-events: none;
    }

     #wpjp-list-loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 150px;
        z-index: 999;
    }

     .wpjp-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid var(--wpjp-border-color);
        border-top: 5px solid var(--wpjp-primary-color);
        border-radius: 50%;
        animation: wpjp-spin-loader 1s linear infinite;
    }

    @keyframes wpjp-spin-loader {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* --- Active State for Trigger Buttons --- */
     .wpjp-joblisting-btn-filter.has-active-filters {
        border-color: var(--wpjp-primary-color);
        background-color: rgba(59, 174, 218, 0.05);
        color: var(--wpjp-primary-color);
    }

     .wpjp-joblisting-btn-filter.wpjp-has-active-filters {
        border-color: var(--wpjp-primary-color);
        color: #fff;
        background: var(--wpjp-primary-color);
    }

    /* --- Active Filter Tags & UI --- */
     .wpjp-active-filters-display {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
        padding-top: 1rem;
        border-top: 1px dashed var(--wpjp-border-color);
    }

     .wpjp-active-filters-label {
        font-size: 14px;
        font-weight: 700;
        color: var(--wpjp-secondary-color);
        display: block;
        margin-bottom: 0.75rem;
    }

     .wpjp-active-filters-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

     .wpjp-filter-group {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        background-color: var(--wpjp-background-color, #f8fafc);
        border: 1px solid var(--wpjp-border-color, #e9ecef);
        border-radius: 4px;
        padding: 0 0.25rem 0 0.75rem;
    }

     .wpjp-filter-group-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--wpjp-body-font-color, #333);
        margin-right: 0.25rem;
    }

     .wpjp-filter-tag {
        background-color: var(--wpjp-background-color, #f8fafc);
        color: var(--wpjp-primary-color, #3baeda);
        border: 1px solid var(--wpjp-primary-color, #3baeda);
        font-size: 13px;
        font-weight: 600;
        padding: 0.25rem 0.5rem 0.25rem 0.75rem;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

     .wpjp-filter-tag-sort {
        padding-right: 0.75rem;
    }

     .wpjp-filter-tag-remove {
        cursor: pointer;
        font-size: 1rem;
        line-height: 1;
        font-weight: normal;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.6;
        transition: opacity 0.2s;
    }

     .wpjp-filter-tag-remove:hover {
        opacity: 1;
        color: var(--wpjp-danger-color, #dc3545);
    }

    /* --- FIXED SLIDER CSS --- */
     .wpjp-inputs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
     .wpjp-input-wrapper { position: relative; }
     .wpjp-currency-symbol { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 700; z-index: 2; }
     .wpjp-display-input { width: 100%; padding: 10px 10px 10px 25px; background: var(--wpjp-background-color, #f8fafc); border: 1px solid var(--wpjp-border-color, #e2e8f0); border-radius: 8px; font-size: 14px; font-weight: 800; color: var(--wpjp-secondary-color, #1e293b); outline: none; box-sizing: border-box; }
     .wpjp-slider-label { display: block; font-size: 13px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }

     .wpjp-range-area { position: relative; width: 100%; height: 35px; margin-top: 15px; margin-bottom: 10px; display: block; }
     .wpjp-track-bg { position: absolute; top: 50%; transform: translateY(-50%); left: 0; right: 0; height: 6px; background-color: #d1d5db; border-radius: 4px; z-index: 1; }
     .wpjp-track-fill { position: absolute; top: 50%; transform: translateY(-50%); height: 6px; background-color: var(--wpjp-primary-color, #007bff); border-radius: 4px; z-index: 2; }

     .wpjp-range-input { position: absolute; top: 50%; transform: translateY(-50%); left: 0; width: 100%; height: 100%; pointer-events: none; appearance: none; -webkit-appearance: none; background: transparent !important; margin: 0; z-index: 10; opacity: 0; cursor: pointer; }
     .wpjp-range-input::-webkit-slider-thumb { pointer-events: all; width: 28px; height: 28px; appearance: none; -webkit-appearance: none; cursor: pointer; border-radius: 50%; }
     .wpjp-range-input::-moz-range-thumb { pointer-events: all; width: 28px; height: 28px; cursor: pointer; border-radius: 50%; border: none; }

     .wpjp-custom-thumb { position: absolute; top: 50%; transform: translate(-50%, -50%); width: 20px; height: 20px; background-color: #ffffff; border: 2px solid var(--wpjp-primary-color, #007bff); border-radius: 50%; z-index: 5; pointer-events: none; box-shadow: 0 1px 4px rgba(0,0,0,0.3); transition: transform 0.1s; }
     .wpjp-custom-thumb.wpjp-is-active { transform: translate(-50%, -50%) scale(1.2); z-index: 6; }

    /* Fix Chosen Plugin Width in AJAX Filters */
     .wpjp-joblisting-dropdown-container .chosen-container {
        width: 100% !important;
    }

    /* ==========================================================================
       WP Job Portal - Badges & Tags (Highly Specific)
       ========================================================================== */
     .wjportal-jobs-list .wjportal-job-title.wpjp-job-title-flex {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

     .wjportal-jobs-list .wpjp-badge-new {
        background-color: var(--wpjp-success-color, #28a745);
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        padding: 5px 8px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1;
        margin-left: 10px;
        display: inline-flex;
        align-items: center;
        box-sizing: border-box;
    }

     .wjportal-jobs-list .wpjp-badge-urgent-flag {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--wpjp-danger-color, #dc3545);
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        padding: 4px 14px;
        border-bottom-left-radius: 6px;
        border-bottom-right-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 1px;
        line-height: 1;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        display: flex;
        align-items: center;
        z-index: 10;
    }

     .wjportal-jobs-list .wpjp-badge-urgent-flag svg {
        margin-right: 4px;
    }

    /* --- Clickable Job Tags (main.php) --- */
     .wpjp-job-tags-list {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
        width: 100%;
    }

     .wpjp-clickable-tag {
        font-size: 14px;
        padding: 1px 2px;
        /*border-radius: 4px;*/
        /*background: #eff6ff;*/
        color: var(--wpjp-primary-color, #3baeda);
        /*border: 1px solid var(--wpjp-primary-color, #e9ecef);*/
        border-bottom: 1px solid var(--wpjp-primary-color, #e9ecef);
        text-decoration: none !important;
        font-weight: 600;
        transition: all 0.2s;
    }

    .wpjp-clickable-tag:hover {
        background-color: var(--wpjp-primary-color, #3baeda);
        color: #ffffff;
    }

    /* --- Salary Filter specific input layout additions --- */
     .wjportal-jobs-list-top-filter .wpjp-filter-salary-fields .wjportal-form-input-field,
     .wjportal-jobs-list-top-filter .wpjp-filter-salary-fields .wpjp-joblisting-select {
        width: 100%;
        margin-bottom: 0.5rem;
        box-sizing: border-box;
    }

     .wjportal-jobs-list-top-filter .wpjp-company-filter-container {
        min-width: 220px;
    }

    /* ==========================================================================
       WP Job Portal - Chosen Plugin Field Standardization
       ========================================================================== */
     .wjportal-jobs-list-top-filter .chosen-container-multi .chosen-choices,
     .wjportal-jobs-list-top-filter .chosen-container-single .chosen-single {
        border: 1px solid var(--wpjp-border-color, #e2e8f0) !important;
        border-radius: 6px !important;
        background: var(--wpjp-background-color, #ffffff) !important;
        background-image: none !important;
        box-shadow: none !important;
        min-height: 48px !important;
        padding: 4px 8px !important;
        box-sizing: border-box !important;
        transition: border-color 0.2s ease;
    }

     .wjportal-jobs-list-top-filter .chosen-container-active .chosen-choices,
     .wjportal-jobs-list-top-filter .chosen-container-active .chosen-single {
        border-color: var(--wpjp-primary-color, #3baeda) !important;
    }

     .chosen-container-multi .chosen-choices li.search-field input[type="text"] {
        height: 38px !important;
        line-height: 38px !important;
        color: var(--wpjp-body-font-color, #333) !important;
        font-family: inherit !important;
        margin: 0 !important;
    }

    /* The company filter sits in the row of filter buttons, so it takes their
       size and surface: it was 48px tall (45px under themes that size every
       Chosen field like a form input), grey, with its own corners and an
       extra 10px after it, and stood above the buttons beside it. Picked
       companies still wrap and the box grows to show them. */
     .wjportal-jobs-list-top-filter .wpjp-company-filter-container .chosen-container {
        height: auto;
        vertical-align: top;
    }

     .wjportal-jobs-list-top-filter .chosen-container-multi .chosen-choices {
        min-height: 0 !important;
        padding: 0 1rem !important;
        background: var(--wpjp-card-background, #ffffff) !important;
        border-radius: 0.5rem !important;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

     .wjportal-jobs-list-top-filter .chosen-container-multi .chosen-choices li.search-field input[type="text"] {
        height: calc(1rem + 14px) !important;
        line-height: calc(1rem + 14px) !important;
        padding: 0 !important;
        font-size: 14px !important;
    }

     .wjportal-jobs-list-top-filter .chosen-container-single .chosen-single span {
        line-height: 38px !important;
        color: var(--wpjp-body-font-color, #333) !important;
    }

     .wjportal-jobs-list-top-filter .chosen-container-multi .chosen-choices li.search-choice {
        background: var(--wpjp-background-color, #f8fafc) !important;
        border: 1px solid var(--wpjp-primary-color, #3baeda) !important;
        color: var(--wpjp-primary-color, #3baeda) !important;
        border-radius: 4px !important;
        padding: 6px 22px 6px 10px !important;
        margin: 5px 5px 0 0 !important;
        box-shadow: none !important;
        font-weight: 600;
    }

     .wjportal-jobs-list-top-filter .chosen-container-multi .chosen-choices li.search-choice .search-choice-close {
        top: 7px !important;
        right: 5px !important;
    }

     .wjportal-jobs-list-top-filter .chosen-container .chosen-drop {
        border: 1px solid var(--wpjp-primary-color, #3baeda) !important;
        border-top: 0 !important;
        border-radius: 0 0 6px 6px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }

     .wjportal-jobs-list-top-filter .wpjp-joblisting-dropdown-menu.wpjp-is-workplace input[type="checkbox"]:checked { background-color: var(--wpjp-primary-color); border-color: var(--wpjp-primary-color); }
    /*                */
    /*                */
    /*                */
    /*                */
    /*                */
    /* -------------------------------------------------------------
       Extraordinary Elegant Design for Job Filters (filters.php)
    ------------------------------------------------------------- */

    /* Main Filter Section Wrapper */
    .wjportal-elegant-addon-main-up-wrapper .wjportal-jobs-list-top-filter {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 20px 5px;
        box-shadow: 0 10px 30px rgba(35, 40, 45, 0.08);
        margin-bottom: 30px;
        border-left: 4px solid #4f46e5;
    }

    .wjportal-elegant-addon-main-up-wrapper .wjportal-jobs-list-top-filter h3 {
        color: #23282d;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Flex Container for Filter Items */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-filters-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-container {
        position: relative;
        flex: 1 1 auto;
        min-width: 160px;
    }

    /* Modern Filter Buttons */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 18px;
        border-radius: 8px;
        color: #23282d;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter:hover,
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter:focus {
        border-color: #4f46e5;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter svg {
        transition: transform 0.3s ease;
        stroke: #f79312;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-container:hover .wpjp-joblisting-btn-filter svg {
        transform: rotate(180deg);
    }

    /* Floating Dropdown Menus */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        width: 280px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(35, 40, 45, 0.1);
        border: 1px solid #e2e8f0;
        padding: 15px;
        z-index: 99;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Hover trigger for desktop - fallback to click if necessary via JS */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-container:hover .wpjp-joblisting-dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    /* Dropdown Content */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-header {
        font-weight: 700;
        color: #4f46e5;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        cursor: pointer;
        color: #23282d;
        transition: color 0.2s ease;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-checkbox-label:hover {
        color: #f79312;
    }

    /* Custom Salary Range Filter Elements */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-filter-salary-fields {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-slider-label {
        font-size: 13px;
        font-weight: 600;
        color: #23282d;
    }

    /* Dropdown Footer & Clear Buttons */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-footer {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear {
        background: transparent;
        border: none;
        color: #4f46e5;
        font-weight: 600;
        cursor: pointer;
        transition: color 0.2s;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear:hover {
        color: #f79312;
    }

    /* Global Actions (Clear All & Sorting) */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-actions-right {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
        width: 100%;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear-all {
        background: #23282d;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear-all:hover {
        background: #f79312;
    }

    /* Sorting Dropdown Tweaks */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-sort-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-sort-label {
        font-weight: 600;
        color: #23282d;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-select {
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #23282d;
        outline: none;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-select:focus {
        border-color: #4f46e5;
    }


    /* -------------------------------------------------------------
       Extraordinary Elegant Design for Job Filters (filters.php)
    ------------------------------------------------------------- */

    /* Main Filter Section Wrapper */
    .wjportal-elegant-addon-main-up-wrapper .wjportal-jobs-list-top-filter {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(35, 40, 45, 0.08);
        margin-bottom: 30px;
        border-left: 4px solid #4f46e5;
    }

    .wjportal-elegant-addon-main-up-wrapper .wjportal-jobs-list-top-filter h3 {
        color: #23282d;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Flex Container for Filter Items */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-filters-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-container {
        position: relative;
        flex: 1 1 auto;
        min-width: 160px;
    }

    /* Modern Filter Buttons */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 18px;
        border-radius: 8px;
        color: #23282d;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter:hover,
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter:focus {
        border-color: #4f46e5;
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-filter svg {
        transition: transform 0.3s ease;
        stroke: #f79312;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-container:hover .wpjp-joblisting-btn-filter svg {
        transform: rotate(180deg);
    }

    /* Floating Dropdown Menus */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        width: 280px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(35, 40, 45, 0.1);
        border: 1px solid #e2e8f0;
        padding: 15px;
        z-index: 99;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Hover trigger for desktop - fallback to click if necessary via JS */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-container:hover .wpjp-joblisting-dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    /* Dropdown Content */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-header {
        font-weight: 700;
        color: #4f46e5;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        cursor: pointer;
        color: #23282d;
        transition: color 0.2s ease;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-checkbox-label:hover {
        color: #f79312;
    }

    /* Custom Salary Range Filter Elements */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-filter-salary-fields {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-slider-label {
        font-size: 13px;
        font-weight: 600;
        color: #23282d;
    }

    /* Dropdown Footer & Clear Buttons */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-dropdown-footer {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear {
        background: transparent;
        border: none;
        color: #4f46e5;
        font-weight: 600;
        cursor: pointer;
        transition: color 0.2s;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear:hover {
        color: #f79312;
    }

    /* Global Actions (Clear All & Sorting) */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-actions-right {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
        width: 100%;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear-all {
        background: #23282d;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-btn-clear-all:hover {
        background: #f79312;
    }

    /* Sorting Dropdown Tweaks */
    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-sort-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-sort-label {
        font-weight: 600;
        color: #23282d;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-select {
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #23282d;
        outline: none;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .wjportal-elegant-addon-main-up-wrapper .wpjp-joblisting-select:focus {
        border-color: #4f46e5;
    }

</style>
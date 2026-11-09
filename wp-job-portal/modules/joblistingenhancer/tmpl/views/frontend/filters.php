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
        $wpjobportal_job_status = WPJOBPORTALincluder::getJSModel('jobstatus')->getJobStatusForCombo(true);
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
                    <select id="wpjp-joblisting-sort" class="wpjp-joblisting-select" aria-label="<?php echo esc_attr__('Sort by', 'wp-job-portal'); ?>">
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

<?php
// The filters' script and styles are files, so browsers cache them; they are printed here,
// where the inline blocks used to be, which keeps their place in the cascade and their timing.
$wpjobportal_filters_settings = array(
    'activeFilters' => esc_html__("Active Filters:", "wp-job-portal"),
    'sortedBy' => esc_html__("Sorted By", "wp-job-portal"),
    'aiSearch' => esc_html__("AI Search", "wp-job-portal"),
    'keyword' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['jobtitle']) ? wpjobportal::$_data['fields_titles_array']['jobtitle'] : ''),
    'city' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['city']) ? wpjobportal::$_data['fields_titles_array']['city'] : ''),
    'category' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['jobcategory']) ? wpjobportal::$_data['fields_titles_array']['jobcategory'] : ''),
    'company' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['company']) ? wpjobportal::$_data['fields_titles_array']['company'] : ''),
    'tags' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['tags']) ? wpjobportal::$_data['fields_titles_array']['tags'] : ''),
    'workplace' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['workplace_type']) ? wpjobportal::$_data['fields_titles_array']['workplace_type'] : ''),
    'salaryRange' => wpjobportal::wpjobportal_getVariableValue(isset(wpjobportal::$_data['fields_titles_array']['jobsalaryrange']) ? wpjobportal::$_data['fields_titles_array']['jobsalaryrange'] : ''),
    'currencySymbol' => wpjobportal::$_config->getConfigValue("job_currency_symbol") ? wpjobportal::$_config->getConfigValue("job_currency_symbol") : "$",
    'yes' => esc_html__("Yes", "wp-job-portal"),
    'urgent' => esc_html__("Urgent", "wp-job-portal"),
    'ajaxurl' => admin_url('admin-ajax.php'),
    'pageId' => (string) absint(wpjobportal::wpjobportal_getPageid()),
    'nonce' => wp_create_nonce("wpjobportal_get_ajax_jobs_list"),
    'paginationUrl' => esc_url_raw(add_query_arg(array('pagenum'=>'%#%'))),
    'salaryMin' => isset($wpjobportal_s_min) && $wpjobportal_s_min !== '' ? intval($wpjobportal_s_min) / 1000 : 0,
    'salaryMax' => isset($wpjobportal_s_max) && $wpjobportal_s_max !== '' ? intval($wpjobportal_s_max) / 1000 : 500,
);
wp_register_script('wpjobportal-job-filters', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/js/wpjobportal-job-filters.js', array('jquery'), wpjobportal_asset_version('includes/js/wpjobportal-job-filters.js'), false);
wp_add_inline_script('wpjobportal-job-filters', 'var wpjpJobFilters = ' . wp_json_encode($wpjobportal_filters_settings) . ';', 'before');
wp_print_scripts('wpjobportal-job-filters');
wp_register_style('wpjobportal-job-filters', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/wpjobportal-job-filters.css', array(), wpjobportal_asset_version('includes/css/wpjobportal-job-filters.css'));
wp_print_styles('wpjobportal-job-filters');
?>
/* Job list filters (Job Listing Enhancer). Values that change per page come from wpjpJobFilters, printed with the script. */
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
                var activeFiltersLbl = wpjpJobFilters.activeFilters;
                var sortedByLbl = wpjpJobFilters.sortedBy;
                var aiSearchLbl = wpjpJobFilters.aiSearch;

                var keywordLbl = wpjpJobFilters.keyword;
                var cityLbl = wpjpJobFilters.city;
                var categoryLbl = wpjpJobFilters.category;
                var compLbl = wpjpJobFilters.company;
                var tagsLbl = wpjpJobFilters.tags;
                var workplaceLbl = wpjpJobFilters.workplace;
                var jobsalaryLbl = wpjpJobFilters.salaryRange;


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
                    var curSym = wpjpJobFilters.currencySymbol;

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
                    html += '<span class="wpjp-filter-group-name">' + wpjpJobFilters.urgent + ': </span>';
                    html += '<span class="wpjp-filter-tag wpjp-filter-tag-urgent" data-val="1">';
                    html += wpjpJobFilters.yes;
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
                var ajaxurl = wpjpJobFilters.ajaxurl;
                var jppage_id = wpjpJobFilters.pageId;

                $wrapper.addClass('wpjp-ajax-loading-active');

                if (jQuery('#wpjp-list-loader').length === 0) {
                    $wrapper.append('<div id="wpjp-list-loader"><div class="wpjp-spinner"></div></div>');
                }

                var postData = {
                    action: 'wpjobportal_ajax',
                    wpjobportalme: 'job',
                    task: 'getAjaxJobs',
                    wpjobportalpageid: jppage_id,
                    _wpnonce: wpjpJobFilters.nonce,
                    current_url_for_pagination: wpjpJobFilters.paginationUrl
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
                var ajaxurl = wpjpJobFilters.ajaxurl;
                var jppage_id = wpjpJobFilters.pageId;

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
                    _wpnonce: wpjpJobFilters.nonce,
                    current_url_for_pagination: wpjpJobFilters.paginationUrl
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

            let phpMin = wpjpJobFilters.salaryMin;
            let phpMax = wpjpJobFilters.salaryMax;

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

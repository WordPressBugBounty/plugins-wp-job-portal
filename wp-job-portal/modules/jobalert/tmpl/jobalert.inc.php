<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>

<script type="text/javascript">

jQuery(document).ready(function ($) {
    jQuery("#proceedPaymentBtn").click(function(){
        jQuery("div#wjportal-popup-background").show();
        jQuery("#payment-popup").slideDown('slow');
    });

    jQuery("div#"+common.theme_chk_prefix+"-popup-background, ."+common.theme_chk_prefix+"-popup-close-icon").click(function(){
        jQuery("div#"+common.theme_chk_prefix+"-popup-background").hide();
        jQuery("#payment-popup").slideUp('slow');

    });

    $.validate();
    //Token Input
    var multicities = <?php echo isset(wpjobportal::$_data['multi_lists']) ? wp_json_encode(json_decode(wpjobportal::$_data['multi_lists'])) : "''"; ?>;
    getTokenInput(multicities);
    // --- NEW TAGS TOKEN INPUT INIT ---
    <?php if(in_array('tag', wpjobportal::$_active_addons)): ?>
    getTokenInputtags();
    <?php endif; ?>
    // ---------------------------------
    jQuery("div#wjportal-popup-background,img#wjportal-popup-close-btn").click(function () {
        closePopup();
    });
});
    var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";
    function showpopup() {
        jQuery('div#wjportal-popup-background').show();
        jQuery('div#wjportal-listpopup').slideDown('slow');
    }
    function closePopup() {
        jQuery("div#wpjp-search-popup,div#wjportal-listpopup").slideUp('slow');
        setTimeout(function () {
            jQuery("div#wjportal-popup-background").hide();
        }, 700);
    }

    function onSubmit(token) {
        document.getElementById('alert_form').submit();
    }

    // --- NEW TAGS TOKEN INPUT FUNCTION ---
    <?php if(in_array('tag', wpjobportal::$_active_addons)): ?>
    function getTokenInputtags() {
        var tagarray = '<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_tag&tagfor=1&action=wpjobportaltask&task=gettagsbytagname")); ?>';

        <?php
            // Pre-populate if editing an existing job alert with tags
            $wpjobportal_pre_populate = '';
            if (isset(wpjobportal::$_data[0]->tags) && !empty(wpjobportal::$_data[0]->tags)) {
                $wpjobportal_pre_populate = json_decode(wpjobportal::$_common->makeFilterdOrEditedTagsToReturn(wpjobportal::$_data[0]->tags));
            }
        ?>

        jQuery('#tags').tokenInput(tagarray, {
            theme: 'wpjobportal',
            preventDuplicates: true,
            tokenLimit: 5,
            <?php if (!empty($wpjobportal_pre_populate)) { echo 'prePopulate: ' . wp_json_encode($wpjobportal_pre_populate) . ','; } ?>
            hintText: "<?php echo esc_html(__('Type In A Search Term', 'wp-job-portal')); ?>",
            noResultsText: "<?php echo esc_html(__('No Results', 'wp-job-portal')); ?>",
            searchingText: "<?php echo esc_html(__('Searching', 'wp-job-portal')); ?>"
        });
    }
    <?php endif; ?>
    // -------------------------------------


    function getTokenInput(multicities) {
        var cityArray = '<?php echo esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname")); ?>';
        var city = jQuery("#cityforedit").val();
        if (city != "") {
            jQuery("#city").tokenInput(cityArray, {
                theme: "wpjobportal",
                preventDuplicates: true,
                hintText: "<?php echo esc_html__('Type In A Search Term', 'wp-job-portal'); ?>",
                noResultsText: "<?php echo esc_html__('No Results', 'wp-job-portal'); ?>",
                searchingText: "<?php echo esc_html__('Searching', 'wp-job-portal'); ?>",
                // tokenLimit: 1,
                prePopulate: multicities,
<?php $wpjobportal_newtyped_cities = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_cities');
 if ($wpjobportal_newtyped_cities == 1) { ?>

                onResult: function (item) {
                    if (jQuery.isEmptyObject(item)) {
                        return [{id: 0, name: jQuery("tester").text()}];
                    } else {
                        //add the item at the top of the dropdown
                        item.unshift({id: 0, name: jQuery("tester").text()});
                        return item;
                    }
                },
                onAdd: function (item) {
                    if (item.id > 0) {
                        return;
                    }
                    if (item.name.search(",") == -1) {
                        var input = jQuery("tester").text();
                        alert("<?php echo esc_html__("Location Format Is Not Correct Please Enter City In This Format City Name Country Name Or City Name State Name Country Name", "wp-job-portal"); ?>");
                        jQuery("#city").tokenInput("remove", item);
                        return false;
                    } else {
                        var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";
                        jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'city', task: 'savetokeninputcity', citydata: jQuery("tester").text(), '_wpnonce':'<?php echo esc_attr(wp_create_nonce("save-token-input-city"));?>'}, function (data) {
                            if (data) {
                                try {
                                    var value = jQuery.parseJSON(data);
                                    jQuery('#city').tokenInput('remove', item);
                                    jQuery('#city').tokenInput('add', {id: value.id, name: value.name});
                                }
                                catch (err) {
                                    jQuery("#city").tokenInput("remove", item);
                                    alert(data);
                                }
                            }
                        });
                    }
                }
                <?php } ?>
            });
        } else {
            jQuery("#city").tokenInput(cityArray, {
                theme: "wpjobportal",
                preventDuplicates: true,
                hintText: "<?php echo esc_html__('Type In A Search Term', 'wp-job-portal'); ?>",
                noResultsText: "<?php echo esc_html__('No Results', 'wp-job-portal'); ?>",
                searchingText: "<?php echo esc_html__('Searching', 'wp-job-portal'); ?>",
                // tokenLimit: 1,
                <?php $wpjobportal_newtyped_cities = WPJOBPORTALincluder::getJSModel('configuration')->getConfigurationByConfigName('newtyped_cities');
                if ($wpjobportal_newtyped_cities == 1) { ?>

                onResult: function (item) {
                    if (jQuery.isEmptyObject(item)) {
                        return [{id: 0, name: jQuery("tester").text()}];
                    } else {
                        //add the item at the top of the dropdown
                        item.unshift({id: 0, name: jQuery("tester").text()});
                        return item;
                    }
                },
                onAdd: function (item) {
                    if (item.id > 0) {
                        return;
                    }
                    if (item.name.search(",") == -1) {
                        var input = jQuery("tester").text();
                        alert("<?php echo esc_html__("Location Format Is Not Correct Please Enter City In This Format City Name Country Name Or City Name State Name Country Name", "wp-job-portal"); ?>");
                        jQuery("#city").tokenInput("remove", item);
                        return false;
                    } else {
                        var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";
                        jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'city', task: 'savetokeninputcity', citydata: jQuery("tester").text(), '_wpnonce':'<?php echo esc_attr(wp_create_nonce("save-token-input-city"));?>'}, function (data) {
                            if (data) {
                                try {
                                    var value = jQuery.parseJSON(data);
                                    jQuery('#city').tokenInput('remove', item);
                                    jQuery('#city').tokenInput('add', {id: value.id, name: value.name});
                                }
                                catch (err) {
                                    jQuery("#city").tokenInput("remove", item);
                                    alert(data);
                                }
                            }
                        });
                    }
                }
                <?php } ?>
            });
        }
    }
</script>

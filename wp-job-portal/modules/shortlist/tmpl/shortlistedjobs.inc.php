<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<script >
    function setrating(src, newrating) {
        jQuery("#" + src).width(parseInt(newrating * 20) + '%');
        alert(newrating);
    }
    var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";

    jQuery(document).ready(function () {
        jQuery("div#wpjobportal-popup-background,img#popup_cross,img#wjportal-popup-close-btn,div#wjportal-popup-background").click(function () {
            closePopup();
        });
    });

    function closePopup() {
        jQuery("div#wpjobportal-search-popup,div#wpjobportal-listpopup,div#wjportal-listpopup,div#"+common.theme_chk_prefix+"-popup").slideUp('slow');
        setTimeout(function () {
            jQuery("div#wpjobportal-popup-background,div#wjportal-popup-background,div#"+common.theme_chk_prefix+"-popup-background").hide();
        }, 700);
    }

    function toggleCommentsDivById(divid) {
        jQuery("div#" + divid).slideToggle();
    }

    function getPackagePopupJobView(jobid) {
        var ajaxurl = "<?php echo esc_url_raw(admin_url('admin-ajax.php')); ?>";
        var jppage_id = "<?php echo absint(wpjobportal::wpjobportal_getPageid()); ?>";
        jQuery.post(ajaxurl, {action: 'wpjobportal_ajax', wpjobportalme: 'job', task: 'getPackagePopupJobView', wpjobportalid: jobid, '_wpnonce':'<?php echo esc_attr(wp_create_nonce("get-package-popup-job-view"))?>',wpjobportalpageid:jppage_id}, function (data) {
            if (data) {
                if(jQuery("#package-popup").length)
                jQuery("#package-popup").remove();
                jQuery('body').append(data);
                jQuery("#wjportal-popup-background").show();
                jQuery("#package-popup").slideDown('slow');

            } else {
                jQuery("div.logo-container").append("<span style='color:Red;'><?php echo esc_html__('Error Deleting Logo', 'wp-job-portal'); ?>");
            }
        });
    }

     function selectPackage(packageid){
        jQuery(".package-div").css('border','1px solid #ccc');
        jQuery(".wjportal-pkg-item, .wpj-jp-pkg-item").removeClass('wjportal-pkg-selected');
        jQuery("#package-div-"+packageid).addClass('wjportal-pkg-selected');
        jQuery("#wpjobportal_packageid").val(packageid);
        jQuery("#jsre_featured_button").removeAttr('disabled');
    }
</script>

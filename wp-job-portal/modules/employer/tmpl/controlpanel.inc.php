<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_register_script( 'google-charts-handle', '' );
wp_enqueue_script( 'google-charts-handle' );
?>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function() {

        //for notifications
        jQuery('div.notifications').hide();
        jQuery('img.notifications').on('click', function(){
            jQuery('div.notifications, div.notifications').slideToggle();
        });
        jQuery('span.count_notifications').on('click', function(){
            jQuery('div.notifications, div.notifications').slideToggle();
        });
        var counter = jQuery('span.count_notifications').text();
                //for messages
                jQuery('div.messages').hide();
                jQuery('img.messages').on('click', function(){
                    jQuery('div.messages, div.messages').slideToggle();
                });
                jQuery('span.count_messages').on('click', function(){
                    jQuery('div.messages, div.messages').slideToggle();
                });
                jQuery('div#wpjobportal-popup-background, img#popup_cross').click(function(){
                    jQuery('div#wpjobportal-popup').hide();
                    jQuery('div#wpjobportal-popup-background').hide();
                });
            });
        google.load('visualization', '1', {packages:['corechart']});



        /*function showLoginPopup(){
            jQuery('div#wpjobportal-popup-background').show();
            jQuery('div#wpjobportal-popup').show();
        }*/
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

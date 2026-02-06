<?php
if (!defined('ABSPATH'))
    die('Restricted Access');

// show calender
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-ui-css', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/jquery-ui-smoothness.css');
$wpjobportal_config = wpjobportal::$_configuration;
if ($wpjobportal_config['date_format'] == 'm/d/Y' || $wpjobportal_config['date_format'] == 'd/m/y' || $wpjobportal_config['date_format'] == 'm/d/y' || $wpjobportal_config['date_format'] == 'd/m/Y') {
    $wpjobportal_dash = '/';
} else {
    $wpjobportal_dash = '-';
}
$wpjobportal_dateformat = $wpjobportal_config['date_format'];
$wpjobportal_firstdash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, 0);
$wpjobportal_firstvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, 0, $wpjobportal_firstdash);
$wpjobportal_firstdash = $wpjobportal_firstdash + 1;
$wpjobportal_seconddash = wpjobportalphplib::wpJP_strpos($wpjobportal_dateformat, $wpjobportal_dash, $wpjobportal_firstdash);
$wpjobportal_secondvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_firstdash, $wpjobportal_seconddash - $wpjobportal_firstdash);
$wpjobportal_seconddash = $wpjobportal_seconddash + 1;
$wpjobportal_thirdvalue = wpjobportalphplib::wpJP_substr($wpjobportal_dateformat, $wpjobportal_seconddash, wpjobportalphplib::wpJP_strlen($wpjobportal_dateformat) - $wpjobportal_seconddash);
$wpjobportal_js_dateformat = '%' . $wpjobportal_firstvalue . $wpjobportal_dash . '%' . $wpjobportal_secondvalue . $wpjobportal_dash . '%' . $wpjobportal_thirdvalue;
$wpjobportal_js_scriptdateformat = $wpjobportal_firstvalue . $wpjobportal_dash . $wpjobportal_secondvalue . $wpjobportal_dash . $wpjobportal_thirdvalue;
$wpjobportal_js_scriptdateformat = wpjobportalphplib::wpJP_str_replace('Y', 'yy', $wpjobportal_js_scriptdateformat);
// 
$wpjobportal_mapfield = null;
if(isset(wpjobportal::$_data[2]))
foreach(wpjobportal::$_data[2] AS $wpjobportal_key => $wpjobportal_value){
    $wpjobportal_value = (array) $wpjobportal_value;
    if(in_array('map', $wpjobportal_value)){
        $wpjobportal_mapfield = $wpjobportal_key;
        break;
    }
}

?>
<style type="text/css">
.ui-datepicker{
    float: left;
}
</style>
<?php
    wp_register_script( 'wpjobportal-inline-handle', '' );
    wp_enqueue_script( 'wpjobportal-inline-handle' );

    $wpjobportal_inline_js_script = "
        jQuery(document).ready(function (jQuery) {
            addDatePicker();
            jQuery('.wpjobportal-multiselect').chosen({
                placeholder_text_multiple: \"". esc_html(__('Select some options', 'wp-job-portal'))."\"
            });
            //Token Input
            ";
            $wpjobportal_multicities = "var multicities = '';";
            if(isset(wpjobportal::$_data[0]->multicity)) 
                if(!(wpjobportal::$_data[0]->multicity == "[]")) 
                    $wpjobportal_multicities = "var multicities = ". wpjobportal::$_data[0]->multicity.";";
            
            $wpjobportal_inline_js_script .= $wpjobportal_multicities;
            $wpjobportal_inline_js_script .= "
            getTokenInputSearch(multicities);";
            if(in_array('tag',wpjobportal::$_active_addons)){
                    $wpjobportal_inline_js_script .= "getTokenInputtags();";
           }
            if(in_array('addressdata', wpjobportal::$_active_addons)){
                if(isset(wpjobportal::$_data[2][$wpjobportal_mapfield]) && wpjobportal::$_data[2][$wpjobportal_mapfield]->published == 1){
                    //$wpjobportal_inline_js_script .= "loadMap();";
                }
            }
            $wpjobportal_inline_js_script .= "
            //Validation
            jQuery.validate();
        });
        var ajaxurl = \"". esc_url_raw(admin_url('admin-ajax.php')) ."\";

        function addDatePicker(){
            jQuery('.custom_date').datepicker({dateFormat: '". $wpjobportal_js_scriptdateformat."'});
        }
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

<style>
    div#map{
        width: 100%;
        height: 100%;
    }
    div#map_container{
        height:<?php echo esc_attr(wpjobportal::$_configuration['mapheight']) . 'px'; ?>;
        width:100%;
    }
</style>
<?php
if(isset(wpjobportal::$_data[2][$wpjobportal_mapfield]) && wpjobportal::$_data[2][$wpjobportal_mapfield]->published == 1){ ?>
<?php $wpjobportal_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://"; ?>
<?php
        $wpjobportal_mappingservice = wpjobportal::$_config->getConfigValue('mappingservice');
        if($wpjobportal_mappingservice == 'gmap'){
            wp_register_script ( 'wpjp-google-map-api', $wpjobportal_protocol . 'maps.googleapis.com/maps/api/js?key=' . wpjobportal::$_config->getConfigValue('google_map_api_key') );
            wp_enqueue_script ( 'wpjp-google-map-api' );
        }elseif($wpjobportal_mappingservice == 'osm'){
            wp_register_script ( 'wpjp-osm-map-js', esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/js/ol.min.js' );
            wp_enqueue_script( 'wpjp-osm-map-js' );
            wp_enqueue_style( 'wpjp-osm-map-css', esc_url(WPJOBPORTAL_PLUGIN_URL).'/includes/css/ol.min.css' );
        } ?>
<?php
    $wpjobportal_inline_js_script = "
        var lmap = {
            map:null,
            marker:null,
            init: function(){
                this.toggleMap();
                jQuery('#showmap1').on('change', lmap.toggleMap);
            },
            toggleMap: function(){
                if(!jQuery('#showmap1').length || jQuery('#showmap1:checked').val()){
                    lmap.showMap();
                }else{
                    lmap.hideMap();
                }
            },
            showMap: function(){
                jQuery('#map-latlng-wrap').show();
                if(!this.map){
                    this.loadMap();
                }
            },
            hideMap: function(){
                jQuery('#map-latlng-wrap').hide();
            },
            loadMap: function(){
                ";
            if($wpjobportal_mappingservice == 'osm'){
                $wpjobportal_inline_js_script .= "
                var default_latitude = parseFloat(document.getElementById('default_latitude').value);
                var default_longitude = parseFloat(document.getElementById('default_longitude').value);

                var latitude = document.getElementById('latitude').value;
                var longitude = document.getElementById('longitude').value;

                if (latitude != '' && longitude != '') {
                    default_latitude = latitude;
                    default_longitude = longitude;
                }
                lmap.map = new ol.Map({
                    target: 'map_container',
                    controls: ol.control.defaults().extend([new ol.control.FullScreen()]),
                    layers: [
                        new ol.layer.Tile({
                            source: new ol.source.OSM()
                        })
                    ],
                    view: new ol.View({
                        center: ol.proj.fromLonLat([default_longitude,default_latitude]),
                        zoom: 14,
                    }),
                });
                lmap.map.addEventListener('click',function(event){
                    lmap.addMarker(ol.proj.transform(event.coordinate, 'EPSG:3857', 'EPSG:4326'));
                }); ";
            }
            $wpjobportal_inline_js_script .= "
            },
            addMarker: function(latlang){
                if(!lmap.map){
                    return false;
                }";
                if($wpjobportal_mappingservice == 'osm'){
                    $wpjobportal_inline_js_script .= "
                    if(lmap.marker){
                        lmap.map.removeLayer(lmap.marker);
                    }
                    lmap.marker = osmAddMarker(lmap.map, latlang);
                    lmap.map.getView().setCenter(ol.proj.transform(latlang, 'EPSG:4326', 'EPSG:3857'));
                    document.getElementById('latitude').value = latlang[1];
                    document.getElementById('longitude').value = latlang[0];
                    ";
                }
                $wpjobportal_inline_js_script .= "
            }
        };


        function loadMap() {
            var default_latitude = document.getElementById('default_latitude').value;
            var default_longitude = document.getElementById('default_longitude').value;

            var latitude = document.getElementById('latitude').value;
            var longitude = document.getElementById('longitude').value;
            var latitude1 = '';
            var longitude1 = '';
            if (latitude) {
                latitude1 = latitude;            
            }
            if (longitude) {
                longitude1 = longitude;            
            }

            if (latitude != '' && longitude != '') {
                default_latitude = latitude;
                default_longitude = longitude;
            }";
            if($wpjobportal_mappingservice == "gmap"){
                $wpjobportal_inline_js_script .= "
                var latlng = new google.maps.LatLng(default_latitude, default_longitude);
                zoom = 10;
                var myOptions = {
                    zoom: zoom,
                    center: latlng,
                    scrollwheel: false,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                var map = new google.maps.Map(document.getElementById('map_container'), myOptions);
                var lastmarker = new google.maps.Marker({
                    postiion: latlng,
                    map: map,
                });
                google.maps.event.addListener(map, 'click', function (e) {
                    var latLng = new google.maps.LatLng(e.latLng.lat(), e.latLng.lng());
                    geocoder = new google.maps.Geocoder();
                    geocoder.geocode({'latLng': latLng}, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (lastmarker != '')
                                lastmarker.setMap(null);
                            var marker = new google.maps.Marker({
                                position: results[0].geometry.location,
                                map: map,
                            });
                            lastmarker = marker;
                            document.getElementById('longitude').value = marker.position.lng();
                            document.getElementById('latitude').value = marker.position.lat();
                        } else {
                            alert(\"". esc_html(__("Geocode was not successful for the following reason", 'wp-job-portal')).":\" + status);
                        }
                    });
                }); ";
            }elseif ($wpjobportal_mappingservice == "osm") {
            ///alert('abc');
            }
       $wpjobportal_inline_js_script .= "
        }
        function checkmapcooridnate() {
            var latitude = document.getElementById('latitude').value;
            var longitude = document.getElementById('longitude').value;
            var radius = document.getElementById('radius').value;
            if (latitude != '' && longitude != '') {
                if (radius != '') {
                    this.form.submit();
                } else {
                    alert(\"". esc_html(__("Please enter the coordinate radius", 'wp-job-portal'))."\");
                    return false;
                }
            }
        }
    ";
    //wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>
<?php } ?>
<?php
    $wpjobportal_inline_js_script = "
    //Token in put
        function getTokenInputSearch(multicities) {
           var cityArray = '". esc_url_raw(admin_url("admin.php?page=wpjobportal_city&action=wpjobportaltask&task=getaddressdatabycityname"))."';

            jQuery('.wpjobportal-job-search-city-field').tokenInput(cityArray, {
                theme: 'wpjobportal',
                preventDuplicates: true,
                hintText: \"". esc_html(__('Type In A Search Term', 'wp-job-portal'))."\",
                noResultsText: \"". esc_html(__('No Results', 'wp-job-portal'))."\",
                searchingText: \"". esc_html(__('Searching', 'wp-job-portal'))."\"
            });
        }

        function getTokenInputtags() {
            var tagarray = '". esc_url_raw(admin_url("admin.php?page=wpjobportal_tag&tagfor=1&action=wpjobportaltask&task=gettagsbytagname"))."';
            jQuery('#tags').tokenInput(tagarray, {
                theme: 'wpjobportal',
                preventDuplicates: true,
                tokenLimit: 5,
                hintText: \"". esc_html(__('Type In A Search Term', 'wp-job-portal'))."\",
                noResultsText: \"". esc_html(__('No Results', 'wp-job-portal'))."\",
                searchingText: \"". esc_html(__('Searching', 'wp-job-portal'))."\"
            });
        }
         jQuery(document).ready(function ($) {
            //lmap.init();
         });
    ";
    wp_add_inline_script( 'wpjobportal-inline-handle', $wpjobportal_inline_js_script );
?>

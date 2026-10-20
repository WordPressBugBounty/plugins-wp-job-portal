<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
?>
<?php
wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_register_script( 'google-charts-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script( 'google-charts-handle' );
?>
<?php
/*
 * The chart is built by the employer model, and this template is reached by
 * anyone who opens the employer control panel - a job seeker among them, who
 * that model never ran for. It read the figures regardless, which on PHP 8 is
 * two warnings a line, printed above the page. No figures, no chart.
 */
if (empty(wpjobportal::$_data['stack_chart_horizontal']['data'])
    || empty(wpjobportal::$_data['stack_chart_horizontal']['title'])) {
    return;
}
//if(WPJOBPORTALincluder::getObjectClass('user')->isemployer()){ ?>
    <div id='wpjobportal-center' class="wjportal-cp-graph-inner-wrp">
    <?php
    $wpjobportal_js_script = "

            google.charts.load('current', {'packages':['corechart']});
            google.setOnLoadCallback(drawStackChartHorizontal);
            function drawStackChartHorizontal() {
                var data = google.visualization.arrayToDataTable([
                        ". wpjobportal::$_data['stack_chart_horizontal']['title'] . ",
                        ". wpjobportal::$_data['stack_chart_horizontal']['data'] ."
    	        ]);
                var view = new google.visualization.DataView(data);
                ";
                if (wpjobportal::$wpjobportal_theme_chk == 1) {
                    $wpjobportal_js_script .= "
                    var options = {
                        title: '". esc_html(__('Job Apply', 'wp-job-portal')) ."',
                        'height':500,
                        isStacked: true,
                        legend: {position: 'top'},
                        hAxis: {title: 'Month',  titleTextStyle: {color: '#333'}},
                        vAxis: {minValue: 0},
                    };
                    ";
                } else { 
                    $wpjobportal_js_script .= "
                    var options = {
                        title: '". esc_html(__('Job Apply', 'wp-job-portal')) ."',
                        'height':400,
                        // No fixed width - see the theme branch above, which never had one.
                        curveType: 'function', // enable smooth lines
                        areaOpacity: 0.3, // semi-transparent fill
                        lineWidth: 3,
                        pointSize: 5,
                        legend: {position: 'top'},
                        hAxis: {title: 'Month',  titleTextStyle: {color: '#333'}},
                        vAxis: {minValue: 0},
                        chartArea: {
                            left: 65,
                            width: '80%',
                        }
                    };";
                } 
                $wpjobportal_js_script .= "
            
                var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));

                chart.draw(data, options);
                if (!window.wpjobportalEmpChartResizeBound) {
                    window.wpjobportalEmpChartResizeBound = true;
                    var wpjobportalEmpChartTimer;
                    window.addEventListener('resize', function () {
                        clearTimeout(wpjobportalEmpChartTimer);
                        wpjobportalEmpChartTimer = setTimeout(drawStackChartHorizontal, 200);
                    });
                }
            }
        ";
         wp_add_inline_script( 'google-charts-handle', $wpjobportal_js_script );
        ?>
        <div id="chart_div" class="wjportal-cp-graph employer"></div>
    </div>
<?php 
//}
?>
	

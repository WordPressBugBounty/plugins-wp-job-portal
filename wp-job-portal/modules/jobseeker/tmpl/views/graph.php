<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
/**
 * @param job      job object - optional
*/
?>
<?php
wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_register_script( 'google-charts-handle', '' );
wp_enqueue_script( 'google-charts-handle' );

?>

<div id='wpjobportal-center' class="wjportal-cp-graph-inner-wrp">
<?php
$wpjobportal_js_script = "
    google.charts.load('current', {'packages':['corechart']});
    google.setOnLoadCallback(drawStackChartHorizontal);

    function drawStackChartHorizontal() {
        var data = google.visualization.arrayToDataTable([
            ". wpjobportal::$_data['stack_chart_horizontal']['title'] . ",
            ". wpjobportal::$_data['stack_chart_horizontal']['data']."
        ]);

        var options = {
            title: '". esc_html(__("Job Type","wp-job-portal")) ."',
            height: 400,
            width: 734,
            legend: { position: 'top' },
                        isStacked: true, // overlap instead of stacking

            curveType: 'function', // enable smooth lines
            areaOpacity: 0.3, // semi-transparent fill
            lineWidth: 3,
            pointSize: 5,
            hAxis: { title: 'Month', titleTextStyle: { color: '#333' } },
            vAxis: { minValue: 0, format: 0, title: 'Total Jobs' },
            chartArea: { left: 65, width: 640 },
            series: {
                0: { areaOpacity: 0.3, color: '#4e79a7' },
                1: { areaOpacity: 0.3, color: '#f28e2b' },
                2: { areaOpacity: 0.3, color: '#e15759' }
            }
        };

        // ✅ Use LineChart for smooth curved areas
        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_js_script );
    ?>
    <div id="chart_div" class="wjportal-cp-graph jobseeker"></div>
</div>

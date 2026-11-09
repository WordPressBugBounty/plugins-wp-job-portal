<?php if (!defined('ABSPATH')) die('Restricted Access'); ?>
<?php
    wp_enqueue_style('status-graph', esc_url(WPJOBPORTAL_PLUGIN_URL) . 'includes/css/status_graph.css', array(), wpjobportal_asset_version('includes/css/status_graph.css'));
?>
<!-- main wrapper -->
<div id="wpjobportaladmin-wrapper" class="wjpl-page">
    <!-- left menu -->
    <div id="wpjobportaladmin-leftmenu">
        <?php  WPJOBPORTALincluder::getClassesInclude('wpjobportaladminsidemenu'); ?>
    </div>
    <div id="wpjobportaladmin-data">
        <?php
            $wpjobportal_msgkey = WPJOBPORTALincluder::getJSModel('report')->getMessagekey();
            WPJOBPORTALMessages::getLayoutMessage($wpjobportal_msgkey);
            $wpjobportal_protocol = is_ssl() ? "https://" : "http://";
        ?>
        <!-- top head -->
        <?php ob_start(); ?>
                <?php echo esc_html(__('Overall Reports', 'wp-job-portal')); ?>
            <?php WPJOBPORTAL_Admin_List_UI::page_head(trim(html_entity_decode(wp_strip_all_tags(ob_get_clean()), ENT_QUOTES))); ?>
        <!-- page content -->
        <div id="wpjobportal-admin-wrapper" class="p0 bg-n bs-n">
            <?php
            if(isset(wpjobportal::$_data['tot_jobs']) && !empty(wpjobportal::$_dat['tot_jobs'])){
                $wpjobportal_total_jobs = @round((wpjobportal::$_data['tot_jobs'] / wpjobportal::$_data['totaljobs']) * 100);
            }else{
               $wpjobportal_total_jobs = 100;
            }
            $wpjobportal_total_appliedresume = 0;
            if(isset(wpjobportal::$_data['totaljobs']) && !empty(wpjobportal::$_dat['totaljobs'])){
                $wpjobportal_total_appliedresume = @round((wpjobportal::$_data['totalappliedresume'] / wpjobportal::$_data['totaljobs']) * 100);
            }else{
               $wpjobportal_total_appliedresume = 100;
            }

            $wpjobportal_tot_comp = 0;
            if(isset(wpjobportal::$_data['tot_comp']) && !empty(wpjobportal::$_dat['tot_comp'])){
                $wpjobportal_tot_comp = @round((wpjobportal::$_data['totalcompany'] / wpjobportal::$_data['tot_comp']) * 100);
            }else{
               $wpjobportal_tot_comp = 100;
            }

            $wpjobportal_tot_resume = 0;
            if(isset(wpjobportal::$_data['presume']) && !empty(wpjobportal::$_dat['presume'])){
                $wpjobportal_tot_resume = @round((wpjobportal::$_data['presume'] / wpjobportal::$_data['totalresume']) * 100);
            }else{
               $wpjobportal_tot_resume = 100;
            }
            ?>
            <!-- count boxes -->
            <div class="wpjobportal-count-wrp">
                <div class="wpjobportal-count-link">
                    <a class="wpjobportal-count-link wpjobportal-count-jobs" href="admin.php?page=wpjobportal_job" data-tab-number="1">
                        <div class="wpjobportal-count-cricle-wrp" data-per="<?php echo esc_attr($wpjobportal_total_jobs); ?>" data-tab-number="1">
                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($wpjobportal_total_jobs); ?>">
                                <div class="circle">
                                    <div class="mask full">
                                         <div class="fill"></div>
                                    </div>
                                    <div class="mask half">
                                        <div class="fill"></div>
                                        <div class="fill fix"></div>
                                    </div>
                                    <div class="shadow"></div>
                                </div>
                                <div class="inset">
                                </div>
                            </div>
                        </div>
                        <div class="wpjobportal-count-link-text">
                            <?php
                                echo '<b class="wjpl-kpi-n">' . esc_html(number_format_i18n((float) wpjobportal::$_data['totaljobs'])) . '</b><span class="wjpl-kpi-l">' . esc_html(__('Total Jobs', 'wp-job-portal')) . '</span>';
                            ?>
                        </div>
                    </a>
                </div>
                <div class="wpjobportal-count-link">
                    <a class="wpjobportal-count-link wpjobportal-count-resume" href="admin.php?page=wpjobportal_resume" data-tab-number="2">
                        <div class="wpjobportal-count-cricle-wrp" data-per="<?php echo esc_attr($wpjobportal_tot_resume); ?>" >
                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($wpjobportal_tot_resume); ?>">
                                <div class="circle">
                                    <div class="mask full">
                                         <div class="fill"></div>
                                    </div>
                                    <div class="mask half">
                                        <div class="fill"></div>
                                        <div class="fill fix"></div>
                                    </div>
                                    <div class="shadow"></div>
                                </div>
                                <div class="inset">
                                </div>
                            </div>
                        </div>
                        <div class="wpjobportal-count-link-text">
                            <?php
                                echo '<b class="wjpl-kpi-n">' . esc_html(number_format_i18n((float) wpjobportal::$_data['totalresume'])) . '</b><span class="wjpl-kpi-l">' . esc_html(__('Total Resume', 'wp-job-portal')) . '</span>';
                            ?>
                        </div>
                    </a>
                </div>
                <div class="wpjobportal-count-link">
                    <a class="wpjobportal-count-link wpjobportal-count-companies" href="admin.php?page=wpjobportal_company" data-tab-number="3">
                        <div class="wpjobportal-count-cricle-wrp" data-per="<?php echo esc_attr($wpjobportal_tot_comp); ?>">
                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($wpjobportal_tot_comp); ?>">
                                <div class="circle">
                                    <div class="mask full">
                                         <div class="fill"></div>
                                    </div>
                                    <div class="mask half">
                                        <div class="fill"></div>
                                        <div class="fill fix"></div>
                                    </div>
                                    <div class="shadow"></div>
                                </div>
                                <div class="inset">
                                </div>
                            </div>
                        </div>
                        <div class="wpjobportal-count-link-text">
                            <?php
                                echo '<b class="wjpl-kpi-n">' . esc_html(number_format_i18n((float) wpjobportal::$_data['totalcompany'])) . '</b><span class="wjpl-kpi-l">' . esc_html(__('Total Companies', 'wp-job-portal')) . '</span>';
                            ?>
                        </div>
                    </a>
                </div>
                <div class="wpjobportal-count-link">
                    <a class="wpjobportal-count-link wpjobportal-count-active-jobs" href="admin.php?page=wpjobportal_job" data-tab-number="4">
                        <div class="wpjobportal-count-cricle-wrp" data-per="<?php echo esc_attr($wpjobportal_total_appliedresume); ?>" >
                            <div class="js-mr-rp" data-progress="<?php echo esc_attr($wpjobportal_total_appliedresume); ?>">
                                <div class="circle">
                                    <div class="mask full">
                                         <div class="fill"></div>
                                    </div>
                                    <div class="mask half">
                                        <div class="fill"></div>
                                        <div class="fill fix"></div>
                                    </div>
                                    <div class="shadow"></div>
                                </div>
                                <div class="inset">
                                </div>
                            </div>
                        </div>
                        <div class="wpjobportal-count-link-text">
                            <?php
                                echo '<b class="wjpl-kpi-n">' . esc_html(number_format_i18n((float) wpjobportal::$_data['totalappliedresume'])) . '</b><span class="wjpl-kpi-l">' . esc_html(__('Applied Resume', 'wp-job-portal')) . '</span>';
                            ?>
                        </div>
                    </a>
                </div>
            </div>
            <div class="wpjobportal-report">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Overall Statistics','wp-job-portal')); ?>
                </div>
                <div id="curve_chart" class="wpjobportal-report-chart"></div>
            </div>
            <div class="wpjobportal-report halfwidth">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Report By Job Categories','wp-job-portal')); ?>
                </div>
                <div id="catbar1" class="wpjobportal-report-chart"></div>
            </div>
            <div class="wpjobportal-report halfwidth">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Report By Resume Categories','wp-job-portal')); ?>
                </div>
                <div id="catbar2" class="wpjobportal-report-chart"></div>
            </div>
            <div class="wpjobportal-report halfwidth">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Report By Job Cities','wp-job-portal')); ?>
                </div>
                <div id="citybar1" class="wpjobportal-report-chart"></div>
            </div>
            <div class="wpjobportal-report halfwidth">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Report By Company Cities','wp-job-portal')); ?>
                </div>
                <div id="citypie" class="wpjobportal-report-chart"></div>
            </div>
            <?php if(in_array('advanceresumebuilder', wpjobportal::$_active_addons)){ ?>
                <div class="wpjobportal-report halfwidth">
                    <div class="wpjobportal-report-heading">
                        <?php echo esc_html(__('Report By Resume Cities','wp-job-portal')); ?>
                    </div>
                    <div id="citybar2" class="wpjobportal-report-chart"></div>
                </div>
            <?php } ?>
            <div class="wpjobportal-report halfwidth">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Report By Job Types','wp-job-portal')); ?>
                </div>
                <div id="jobtypebar1" class="wpjobportal-report-chart"></div>
            </div>
            <div class="wpjobportal-report halfwidth">
                <div class="wpjobportal-report-heading">
                    <?php echo esc_html(__('Report By Resume Types','wp-job-portal')); ?>
                </div>
                <div id="jobtypebar2" class="wpjobportal-report-chart"></div>
            </div>
        </div>
    </div>
</div>
<?php
wp_enqueue_script( 'jp-google-charts', esc_url(WPJOBPORTAL_PLUGIN_URL).'includes/js/google-charts.js', array(), '1.1.1', false );
wp_register_script( 'google-charts-handle', '', array(), WPJOBPORTAL_VERSION, false );
wp_enqueue_script( 'google-charts-handle' );
$wpjobportal_drawChartTop_js_script = "
    google.charts.load('current', {'packages':['corechart']});
    google.setOnLoadCallback(drawChartTop);
            function drawChartTop() {
            var data = new google.visualization.DataTable();
                    data.addColumn('date', '". esc_html(__("Dates", 'wp-job-portal'))."');
                    data.addColumn('number', '". esc_html(__("Jobs", 'wp-job-portal')) ."');
                    data.addColumn('number', '". esc_html(__("Resume", 'wp-job-portal'))."');
                    data.addColumn('number', '". esc_html(__("Company", 'wp-job-portal'))."');
                    data.addColumn('number', '". esc_html(__("Applied Resume", 'wp-job-portal'))."');
                    data.addRows([
                        ".wpjobportal::$_data['line_chart_json_array']."
                    ]);
                    var options = {
                    colors:['#1EADD8', '#179650', '#D98E11', '#5F3BBB', '#DB624C'],
                            curveType: 'function',
                            legend: { position: 'bottom' },
                            pointSize: 6,
                            height: 400,
                            width: '100%',
                            // This line will make you select an entire row of data at a time
                            focusTarget: 'category',
                            chartArea: {width:'90%', top:50}
                    };
                    var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
                    chart.draw(data, options);
            }

";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_drawChartTop_js_script );

$wpjobportal_drawChartCatBar1_js_script = "
    google.setOnLoadCallback(drawChartCatBar1);
    function drawChartCatBar1(){
    var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Categories', 'wp-job-portal')) ."', '". esc_html(__('Jobs', 'wp-job-portal')) ."', { role: 'style' }, { role: 'annotation' } ],
    ". wpjobportal::$_data['catbar1'] ."
    ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
            { calc: 'stringify',
                    sourceColumn: 1,
                    type: 'string',
                    role: 'annotation' },
                    2]);
            var options = {
            title: '',
                    width:'100%',
                    height: 300,
                    bar: {groupWidth: '80%'},
                    legend: { position: 'none' },
                    chartArea: {width:'90%', top:50}
            };
            var chart = new google.visualization.BarChart(document.getElementById('catbar1'));
            chart.draw(view, options);
    }
";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_drawChartCatBar1_js_script );

$wpjobportal_drawChartCatBar2_js_script = "
    google.setOnLoadCallback(drawChartCatBar2);
    function drawChartCatBar2(){
    var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Categories', 'wp-job-portal'))."', '". esc_html(__('Resume', 'wp-job-portal')) ."', { role: 'style' }, { role: 'annotation' } ],
    ". wpjobportal::$_data['catbar2'] ."
    ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
            { calc: 'stringify',
                    sourceColumn: 1,
                    type: 'string',
                    role: 'annotation' },
                    2]);
            var options = {title: '', width:'100%', height: 300, bar: {groupWidth: '80%'}, legend: { position: 'none' }};
            var chart = new google.visualization.ColumnChart(document.getElementById('catbar2'));
            chart.draw(view, options);
    }

    google.setOnLoadCallback(drawChart);
    function drawChart() {
    // No catpie box is printed on this page; drawing into nothing threw 'Container is not defined'.
    if (!document.getElementById('catpie')) { return; }
    var piedata = google.visualization.arrayToDataTable([
            ['". esc_html(__('Categories', 'wp-job-portal')) ."', '". esc_html(__('Companies', 'wp-job-portal')) ."'],
    ". wpjobportal::$_data['catpie'] ."
    ]);
            var pieoptions = {title: '', width:'100%', height:300, legend: {position:'bottom'}, pieHole: 0.4, };
            var piechart = new google.visualization.PieChart(document.getElementById('catpie'));
            piechart.draw(piedata, pieoptions);
    }
";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_drawChartCatBar2_js_script );

$wpjobportal_drawChartCatBar3_js_script = "

    google.setOnLoadCallback(drawChartCityBar1);
    function drawChartCityBar1(){
    var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Cities', 'wp-job-portal')) ."', '". esc_html(__('Jobs', 'wp-job-portal')) ."', { role: 'style' }, { role: 'annotation' } ],
    ". wpjobportal::$_data['citybar1'] ."
    ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
            { calc: 'stringify',
                    sourceColumn: 1,
                    type: 'string',
                    role: 'annotation' },
                    2]);
            var options = {
            title: '',
                    width:'100%',
                    height: 300,
                    bar: {groupWidth: '80%'},
                    legend: { position: 'none' },
                    chartArea: {width:'90%', top:50}
            };
            var chart = new google.visualization.BarChart(document.getElementById('citybar1'));
            chart.draw(view, options);
    }
";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_drawChartCatBar3_js_script );

$wpjobportal_drawChartCatBar4_js_script = "

    google.setOnLoadCallback(drawChartCityBar2);
    //4
    function drawChartCityBar2(){
    // Its box is only printed with the resume builder add-on.
    if (!document.getElementById('citybar2')) { return; }
    var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Cities', 'wp-job-portal')) ."', '". esc_html(__('Resume', 'wp-job-portal')) ."', { role: 'style' }, { role: 'annotation' } ],
    ". wpjobportal::$_data['citybar2'] ."
    ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
            { calc: 'stringify',
                    sourceColumn: 1,
                    type: 'string',
                    role: 'annotation' },
                    2]);
            var options = {title: '', width:'100%', height: 300, bar: {groupWidth: '80%'}, legend: { position: 'none' }};
            var chart = new google.visualization.ColumnChart(document.getElementById('citybar2'));
            chart.draw(view, options);
    }

    google.setOnLoadCallback(drawChartCity);
    function drawChartCity() {
    var piedata = google.visualization.arrayToDataTable([
            ['". esc_html(__('Cities', 'wp-job-portal')) ."', '". esc_html(__('Companies', 'wp-job-portal')) ."'],
    ". wpjobportal::$_data['citypie'] ."
    ]);
            var pieoptions = {title: '', width:'100%', height:300, legend: {position:'bottom'}, pieHole: 0.4, };
            var piechart = new google.visualization.PieChart(document.getElementById('citypie'));
            piechart.draw(piedata, pieoptions);
    }
";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_drawChartCatBar4_js_script );

$wpjobportal_drawChartCatBar5_js_script = "

    google.setOnLoadCallback(drawChartJobtypeBar1);
    function drawChartJobtypeBar1(){
    var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Job type', 'wp-job-portal')) ."', '". esc_html(__('Jobs', 'wp-job-portal')) ."', { role: 'style' }, { role: 'annotation' } ],
     ". wpjobportal::$_data['jobtypebar1'] ."
    ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
            { calc: 'stringify',
                    sourceColumn: 1,
                    type: 'string',
                    role: 'annotation' },
                    2]);
            var options = {
            title: '',
                    width:'100%',
                    height: 300,
                    bar: {groupWidth: '80%'},
                    legend: { position: 'none' },
                    chartArea: {width:'90%', top:50}
            };
            var chart = new google.visualization.BarChart(document.getElementById('jobtypebar1'));
            chart.draw(view, options);
    }

    google.setOnLoadCallback(drawChartJobtypeBar2);
    function drawChartJobtypeBar2(){
    var data = google.visualization.arrayToDataTable([
            ['". esc_html(__('Job type', 'wp-job-portal')) ."', '". esc_html(__('Resume', 'wp-job-portal')) ."', { role: 'style' }, { role: 'annotation' } ],
    ". wpjobportal::$_data['jobtypebar2'] ."
    ]);
            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
            { calc: 'stringify',
                    sourceColumn: 1,
                    type: 'string',
                    role: 'annotation' },
                    2]);
            var options = {title: '', width:'100%', height: 300, bar: {groupWidth: '80%'}, legend: { position: 'none' }};
            var chart = new google.visualization.ColumnChart(document.getElementById('jobtypebar2'));
            chart.draw(view, options);
    }
";
wp_add_inline_script( 'google-charts-handle', $wpjobportal_drawChartCatBar5_js_script );

?>

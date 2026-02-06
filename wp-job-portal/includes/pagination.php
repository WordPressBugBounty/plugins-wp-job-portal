<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALpagination {

    static $_limit;
    static $_offset;
    static $_currentpage;

    static function setLimit($limit){
        if(is_numeric($limit))
            self::$_limit = $limit;
    }

    static function getLimit(){
        return (int) self::$_limit;
    }


    static function getPagination($wpjobportal_total,$wpjobportal_searchlayout = null){
        if(!is_numeric($wpjobportal_total)) return false;
        $wpjobportal_maybe_page_num = 0;
        if(!empty(wpjobportal::$_data['sanitized_args']) && !empty(wpjobportal::$_data['sanitized_args']['backlink_pagenum'])){
            $wpjobportal_maybe_page_num = wpjobportal::$_data['sanitized_args']['backlink_pagenum'];
            $wpjobportal_pagenum = $wpjobportal_maybe_page_num;
        }
        if($wpjobportal_maybe_page_num == 0){
            $wpjobportal_pagenum = absint(wpjobportal::wpjobportal_sanitizeData(WPJOBPORTALrequest::getVar('pagenum','get',1)));
        }
        if(!self::getLimit()){
            $limit = wpjobportal::$_configuration['pagination_default_page_size'];
            if($limit != ''){
                $limit = ceil(wpjobportal::$_configuration['pagination_default_page_size']);
            }else{
                $limit = 10;
            }
            self::setLimit($limit); // number of rows in page
        }
        self::$_offset = ( $wpjobportal_pagenum - 1 ) * self::$_limit;
        self::$_currentpage = $wpjobportal_pagenum;
        $wpjobportal_num_of_pages = ceil($wpjobportal_total / self::$_limit);
        $wpjobportal_result = '';
        if(is_admin()){
            $wpjobportal_result = paginate_links(array(
                'base' => add_query_arg('pagenum', '%#%'),
                'format' => '',
                'prev_next' => true,
                'prev_text' => esc_html(__('Previous', 'wp-job-portal')),
                'next_text' => esc_html(__('Next', 'wp-job-portal')),
                'total' => $wpjobportal_num_of_pages,
                'current' => $wpjobportal_pagenum,
                'add_args' => false,
            ));
        }else{
            if(wpjobportal::$wpjobportal_theme_chk == 1) {
                $wpjobportal_links = paginate_links( array(
                    'type' => 'array',
                    'base' => add_query_arg('pagenum', '%#%'),
                    'format' => '',
                    'prev_next' => true,
                    'prev_text' => esc_html(__('Previous', 'wp-job-portal')),
                    'total' => $wpjobportal_num_of_pages,
                    'current' => $wpjobportal_pagenum,
                    'next_text' => esc_html(__('Next', 'wp-job-portal')),
                    'add_args' => false,
                ));
                if(!empty($wpjobportal_links) && is_array($wpjobportal_links)){
                    $wpjobportal_result = '<ul class="pagination pagination-lg">';
                    foreach($wpjobportal_links AS $wpjobportal_link){
                        if(wpjobportalphplib::wpJP_strstr($wpjobportal_link, 'current')){
                            $wpjobportal_result .= '<li class="active">'.$wpjobportal_link.'</li>';
                        }else{
                            $wpjobportal_result .= '<li>'.$wpjobportal_link.'</li>';
                        }
                    }
                    $wpjobportal_result .= '</ul>';
                }
            }else{
                if($wpjobportal_searchlayout != null && get_option( 'permalink_structure' ) != ""){
                    $layargs = add_query_arg(array('pagenum'=>'%#%' , 'wpjobportallay'=>$wpjobportal_searchlayout));
                }else{
                    $layargs = add_query_arg(array('pagenum'=>'%#%'));
                }
                $wpjobportal_result = paginate_links(array(
                            'base' => $layargs,
                            'format' => '',
                            'prev_next' => true,
                            'prev_text' => esc_html(__('Previous', 'wp-job-portal')),
                            'next_text' => esc_html(__('Next', 'wp-job-portal')),
                            'total' => $wpjobportal_num_of_pages,
                            'current' => $wpjobportal_pagenum,
                            'add_args' => false,
                        ));
           }

        }
        return $wpjobportal_result;
    }

    static function isLastOrdering($wpjobportal_total, $wpjobportal_pagenum) {
        $wpjobportal_maxrecord = $wpjobportal_pagenum * wpjobportal::$_configuration['pagination_default_page_size'];
        if ($wpjobportal_maxrecord >= $wpjobportal_total)
            return false;
        else
            return true;
    }

}

?>

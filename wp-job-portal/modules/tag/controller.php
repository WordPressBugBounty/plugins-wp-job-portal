<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALTagController {

    private $_msgkey;

    function __construct() {

        self::handleRequest();

        $this->_msgkey = WPJOBPORTALincluder::getJSModel('tag')->getMessagekey();  
    }

    function handleRequest() {
        $layout = WPJOBPORTALrequest::getLayout('wpjobportallt', null, 'tags');
        if (self::canaddfile($layout)) {
            switch ($layout) {
                case 'admin_tags':
                    WPJOBPORTALincluder::getJSModel('tag')->getAllTags();
                    break;
                case 'admin_formtag':
                    $id = WPJOBPORTALrequest::getVar('wpjobportalid');
                    WPJOBPORTALincluder::getJSModel('tag')->getTagbyId($id);
                    break;
            }
            $module = (wpjobportal::$_common->wpjp_isadmin()) ? 'page' : 'wpjobportalme';
            $module = WPJOBPORTALrequest::getVar($module, null, 'tag');
            $module = wpjobportalphplib::wpJP_str_replace('wpjobportal_', '', $module);
            WPJOBPORTALincluder::include_file($layout, $module);
        }
    }

    function canaddfile($layout) {
        if (WPJOBPORTALrequest::getVar('form_request', 'post', '') == 'wpjobportal')
            return false;
        elseif (WPJOBPORTALrequest::getVar('action', 'get', '') == 'wpjobportaltask')
            return false;
        else{
                if(!is_admin() && strpos($layout, 'admin_') === 0){
                    return false;
                }
                return true;
            }
    }

    function gettagsbytagname() {
        $tagname = WPJOBPORTALrequest::getVar('q');
        $tagfor = WPJOBPORTALrequest::getVar('tagfor');
        $result = WPJOBPORTALincluder::getJSModel('tag')->getTagsByTagName($tagname, $tagfor);
        echo wp_json_encode($result);
        exit();
    }

    function removetag() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_tag_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $result = WPJOBPORTALincluder::getJSModel('tag')->deleteTags($ids);
        $msg = WPJOBPORTALMessages::getMessage($result, 'tag');
        WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        $url = admin_url("admin.php?page=wpjobportal_tag&wpjobportallt=tags");
        wp_safe_redirect($url);
        die();
    }

    function publish() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_tag_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $result = WPJOBPORTALincluder::getJSModel('tag')->publishUnpublish($ids, 1); //  for publish
        $msg = WPJOBPORTALMessages::getMessage($result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        $url = admin_url("admin.php?page=wpjobportal_tag&wpjobportallt=tags");
        if ($pagenum)
            $url .= "&pagenum=" . $pagenum;
        wp_safe_redirect($url);
        die();
    }

    function unpublish() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_tag_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $pagenum = WPJOBPORTALrequest::getVar('pagenum');
        $ids = WPJOBPORTALrequest::getVar('wpjobportal-cb');
        $result = WPJOBPORTALincluder::getJSModel('tag')->publishUnpublish($ids, 0); //  for unpublish
        $msg = WPJOBPORTALMessages::getMessage($result, 'record');
        WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        $url = admin_url("admin.php?page=wpjobportal_tag&wpjobportallt=tags");
        if ($pagenum)
            $url .= "&pagenum=" . $pagenum;
        wp_safe_redirect($url);
        die();
    }

    function savetag() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'wpjobportal_tag_nonce') ) {
             die( 'Security check Failed' );
        }
        if(!wpjobportal::$_common->wpjp_isadmin())
            return false;
        $url = admin_url("admin.php?page=wpjobportal_tag&wpjobportallt=tags");
        $data = WPJOBPORTALrequest::get('post');
        // var_dump($data);
        $result = WPJOBPORTALincluder::getJSModel('tag')->storeTag($data);
        $msg = WPJOBPORTALMessages::getMessage($result, 'tag');
        WPJOBPORTALMessages::setLayoutMessage($msg['message'], $msg['status'],$this->_msgkey);
        wp_safe_redirect($url);
        die();
    }

}

$WPJOBPORTALTagController = new WPJOBPORTALTagController();
?>

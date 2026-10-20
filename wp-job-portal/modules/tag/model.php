<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALTagModel {

    function getTagbyId($id) {
        if ($id) {
            if (!is_numeric($id))
                return false;
            $query = "SELECT * FROM " . wpjobportal::$_db->prefix . "wj_portal_tags WHERE id =" . (int) ($id);
            wpjobportal::$_data[0] = wpjobportaldb::get_row($query);
        }
        return;
    }

    function getAllTags() {
        //Filter
        $searchname = wpjobportal::$_search['search_filter']['searchname'];
        $status = wpjobportal::$_search['search_filter']['status'];

        // $formsearch = WPJOBPORTALrequest::getVar('WPJOBPORTAL_form_search', 'post');
        // if ($formsearch == 'WPJOBPORTAL_SEARCH') {
        //     $_SESSION['WPJOBPORTAL_SEARCH']['title'] = $title;
        //     $_SESSION['WPJOBPORTAL_SEARCH']['status'] = $status;
        // }
        // if (WPJOBPORTALrequest::getVar('pagenum', 'get', null) != null) {
        //     $title = (isset($_SESSION['WPJOBPORTAL_SEARCH']['title']) && $_SESSION['WPJOBPORTAL_SEARCH']['title'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['title']) : null;
        //     $status = (isset($_SESSION['WPJOBPORTAL_SEARCH']['status']) && $_SESSION['WPJOBPORTAL_SEARCH']['status'] != '') ? sanitize_key($_SESSION['WPJOBPORTAL_SEARCH']['status']) : null;
        // } elseif ($formsearch !== 'WPJOBPORTAL_SEARCH') {
        //     if (isset($_SESSION['WPJOBPORTAL_SEARCH'])) {
        //         unset($_SESSION['WPJOBPORTAL_SEARCH']);
        //     }
        // }
        $inquery = '';
        $clause = ' WHERE ';
        if ($searchname != null) {
            $inquery .= $clause . " tag LIKE '%" . esc_sql($searchname) . "%'";
            $clause = ' AND ';
        }

        if ($status != null AND is_numeric($status)) {
            $inquery .=$clause . " status = " . (int) ($status);
        }

        wpjobportal::$_data['filter']['searchname'] = $searchname;
        wpjobportal::$_data['filter']['status'] = $status;

        //Pagination
        $query = "SELECT COUNT(id) FROM `" . wpjobportal::$_db->prefix . "wj_portal_tags`";
        $query .= $inquery;
        $total = wpjobportaldb::get_var($query);
        wpjobportal::$_data['total'] = $total;
        wpjobportal::$_data[1] = WPJOBPORTALpagination::getPagination($total);

        //Data
        $query = "SELECT * FROM `" . wpjobportal::$_db->prefix . "wj_portal_tags`";
        $query .=$inquery;
        // Through the accessors, which cast. Reading the statics directly is what
        // put "LIMIT , " into this query when pagination had not initialised.
        $query .= " ORDER BY tag ASC LIMIT " . WPJOBPORTALpagination::getOffset() . " , " . WPJOBPORTALpagination::getLimit();
        wpjobportal::$_data[0] = wpjobportaldb::get_results($query);

        return;
    }

    function storeTag($data) {
        if (empty($data))
            return false;


        if ($data['id'] == '') {
            $result = $this->isTagExist($data['tag']);
            if ($result == true) {
                return WPJOBPORTAL_ALREADY_EXIST;
            }
        }

        $jobalias = WPJOBPORTALincluder::getJSModel('common')->removeSpecialCharacter($data['tag']);
        $jobalias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace(' ', '-', $jobalias));

        $data['alias'] = $jobalias;

        $user = WPJOBPORTALincluder::getObjectClass('user');

        $data['createdby'] = $user->uid();

        $row = WPJOBPORTALincluder::getJSTable('tag');
        if (!$row->bind($data)) {
            return WPJOBPORTAL_SAVE_ERROR;
        }
        if (!$row->store()) {
            return WPJOBPORTAL_SAVE_ERROR;
        }

        return WPJOBPORTAL_SAVED;
    }

    function getTagsByTagName($tagname, $tagfor) {
        if (!is_numeric($tagfor))
            return null;
        $query = "SELECT tag AS id , tag AS name FROM `" . wpjobportal::$_db->prefix . "wj_portal_tags`
                    WHERE tag LIKE '" . esc_sql($tagname.'%') . "'
                    AND status = 1 AND (tagfor = 3 OR tagfor = 0 OR tagfor = " . (int) ($tagfor) . ") LIMIT " . WPJOBPORTALincluder::getJSModel('configuration')->getConfigValue("number_of_tags_for_autocomplete");

        $result = wpjobportaldb::get_results($query);
        if (empty($result))
            return null;
        else
            return $result;
    }

    function deleteTags($ids) {
        if (empty($ids))
            return false;
        $row = WPJOBPORTALincluder::getJSTable('tag');
        $notdeleted = 0;
        foreach ($ids as $id) {
            if (!$row->delete($id)) {
                $notdeleted += 1;
            }
        }
        if ($notdeleted == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            return WPJOBPORTAL_DELETED;
        } else {
            WPJOBPORTALMessages::$wpjobportal_counter = $notdeleted;
            return WPJOBPORTAL_DELETE_ERROR;
        }
    }

    function publishUnpublish($ids, $status) {
        if (empty($ids))
            return false;
        if (!is_numeric($status))
            return false;

        $row = WPJOBPORTALincluder::getJSTable('tag');
        $total = 0;
        if ($status == 1) {
            foreach ($ids as $id) {
                if (!$row->update(array('id' => $id, 'status' => $status))) {
                    $total += 1;
                }
            }
        } else {
            foreach ($ids as $id) {
                if ($this->tagCanUnpublish($id)) {
                    if (!$row->update(array('id' => $id, 'status' => $status))) {
                        $total += 1;
                    }
                } else {
                    $total += 1;
                }
            }
        }
        if ($total == 0) {
            WPJOBPORTALMessages::$wpjobportal_counter = false;
            if ($status == 1)
                return WPJOBPORTAL_PUBLISHED;
            else
                return WPJOBPORTAL_UN_PUBLISHED;
        }else {
            WPJOBPORTALMessages::$wpjobportal_counter = $total;
            if ($status == 1)
                return WPJOBPORTAL_PUBLISH_ERROR;
            else
                return WPJOBPORTAL_UN_PUBLISH_ERROR;
        }
    }

    function tagCanUnpublish($tagid) {
        return true;
    }

    function isTagExist($title) {
        $query = "SELECT COUNT(id) FROM " . wpjobportal::$_db->prefix . "wj_portal_tags WHERE LOWER(tag) = '" . wpjobportalphplib::wpJP_strtolower($title) . "'";
        $result = wpjobportaldb::get_var($query);
        if ($result > 0)
            return true;
        else
            return false;
    }

    function storeTokenInputTag($tagname) {
        if (empty($tagname))
            return 2;
        $user = WPJOBPORTALincluder::getObjectClass('user');
        $tagfor = 0;
        if ($user->isemployer()) {
            $tagfor = 1;
        } elseif ($user->isjobseeker()) {
            $tagfor = 2;
        }

        $jobalias = WPJOBPORTALincluder::getJSModel('common')->removeSpecialCharacter($tagname);
        $jobalias = wpjobportalphplib::wpJP_strtolower(wpjobportalphplib::wpJP_str_replace(' ', '-', $jobalias));

        $data = array();
        $data['id'] = null;
        $data['tag'] = $tagname;
        $data['alias'] = $jobalias;
        $data['tagfor'] = $tagfor;
        $data['status'] = 1;
        $data['created'] = gmdate('Y-m-d');
        $data['createdby'] = $user->uid();
        $row = WPJOBPORTALincluder::getJSTable('tag');
        if (!$row->bind($data)) {
            return 2;
        }
        if (!$row->store()) {
            return 2;
        }

        $result = array();
        $result[0] = $row->tag;

        return $result;
    }

    public function saveTokenInputTag() {
        $nonce = WPJOBPORTALrequest::getVar('_wpnonce');
        if (! wp_verify_nonce( $nonce, 'save-token-input-tag') ) {
            die( 'Security check Failed' );
        }
        $tag_string = WPJOBPORTALrequest::getVar('tagdata');
        $result = $this->storeTokenInputTag($tag_string);
        if (is_array($result)) {
            echo wp_json_encode(array('id' => $result[0], 'name' => $result[0])); // send back the tag newely created
        } elseif ($result == 2) {
            echo esc_html__('Error in saving records please try again', 'wp-job-portal');
        }
        exit();
    }

    function makeanchorfortags($tags) {
        if (empty($tags)) {
            $anchor = '<div id="jsresume-tags-wrapper"></div>';
            return $anchor;
        }
        $array = wpjobportalphplib::wpJP_explode(',', $tags);
        if (wpjobportal::$wpjobportal_theme_chk == 1) {
            $anchor = '';
            for ($i = 0; $i < count($array); $i++) {
                $with_spaces = wpjobportal::tagfillin($array[$i]);
                $anchor .= '<a class="wpj-jp-tag" href="' . esc_url(wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'tags'=>$with_spaces))) . '" title="' . wpjobportal::wpjobportal_getVariableValue( $array[$i]) . '">' . wpjobportal::wpjobportal_getVariableValue( $array[$i]) . '</a>';
            }
        } else {
            $anchor = '<div id="jsresume-tags-wrapper" class="wjportal-resume-section-wrp">';
            $anchor .= '<div class="jsresume-tags-title wjportal-resume-section-title">' . wpjobportal::$wpjobportal_data['fieldtitles']['tags'] . '</div>';
            $anchor .= '<div class="tags-wrapper-border wjportal-tags-wrp">';
            for ($i = 0; $i < count($array); $i++) {
                $with_spaces = wpjobportal::tagfillin($array[$i]);
                $anchor .= '<a class="wpjobportal_tags_a wjportal-tags wjportal-tags-item" href="' . wpjobportal::wpjobportal_makeUrl(array('wpjobportalme'=>'resume', 'wpjobportallt'=>'resumes', 'tags'=>$with_spaces)) . '" title="' . wpjobportal::wpjobportal_getVariableValue($array[$i]) . '">' . wpjobportal::wpjobportal_getVariableValue($array[$i]) . '</a>';
            }
            $anchor .= '</div>';
            $anchor .= '</div>';
        }
        return $anchor;
    }

    function getMessagekey(){
        $key = 'tag';if(wpjobportal::$_common->wpjp_isadmin()){$key = 'admin_'.$key;}return $key;
    }

}
?>

<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class WPJOBPORTALapprovalsModel {

    /**
     * Everything that waits for a decision, newest first: items waiting for
     * approval and featured requests, from every active queue (the same rules
     * as the queue screens and the menu count). Up to 50 per queue; each queue's
     * own tab has the full list.
     *
     * Leaves the result in wpjobportal::$_data['approvals'].
     *
     * @param string $type One queue's key (jobs, companies, …) for its own view:
     *                     the whole queue, not 50. Empty for everything.
     * @param string $q    Words that must all appear in the title or "from".
     */
    function getEverythingWaiting($type = '', $q = '') {
        $db = wpjobportal::$_db;
        $p = $db->prefix . 'wj_portal_';
        $queues = WPJOBPORTAL_Admin_Menu_Service::approval_queues();
        $type = isset($queues[$type]) ? $type : '';
        $all_queues = $queues;
        if ($type !== '') {
            $queues = array($type => $queues[$type]);
        }
        $has = function ($addon) {
            return in_array($addon, (array) wpjobportal::$_active_addons, true);
        };
        $sql = array();
        if (isset($queues['jobs'])) {
            $sql[] = "SELECT 'jobs' AS type, 'approval' AS reason, job.id, job.title, company.name AS context, job.created FROM `{$p}jobs` AS job LEFT JOIN `{$p}companies` AS company ON company.id = job.companyid WHERE job.status = 0";
            if ($has('featuredjob')) {
                $sql[] = "SELECT 'jobs' AS type, 'feature' AS reason, job.id AS id, job.title AS title, company.name AS context, job.created AS created FROM `{$p}jobs` AS job LEFT JOIN `{$p}companies` AS company ON company.id = job.companyid WHERE job.isfeaturedjob = 0 AND job.status <> 0";
            }
        }
        if (isset($queues['companies'])) {
            $sql[] = "SELECT 'companies' AS type, 'approval' AS reason, company.id AS id, company.name AS title, CONCAT_WS(' ', owner.first_name, owner.last_name) AS context, company.created AS created FROM `{$p}companies` AS company LEFT JOIN `{$p}users` AS owner ON owner.id = company.uid WHERE company.status = 0";
            if ($has('featuredcompany')) {
                $sql[] = "SELECT 'companies' AS type, 'feature' AS reason, company.id AS id, company.name AS title, CONCAT_WS(' ', owner.first_name, owner.last_name) AS context, company.created AS created FROM `{$p}companies` AS company LEFT JOIN `{$p}users` AS owner ON owner.id = company.uid WHERE company.isfeaturedcompany = 0 AND company.status <> 0";
            }
        }
        if (isset($queues['resumes'])) {
            $sql[] = "SELECT 'resumes' AS type, 'approval' AS reason, resume.id AS id, COALESCE(NULLIF(resume.application_title, ''), CONCAT_WS(' ', resume.first_name, resume.last_name)) AS title, CONCAT_WS(' ', resume.first_name, resume.last_name) AS context, resume.created AS created FROM `{$p}resume` AS resume WHERE resume.status = 0 AND resume.quick_apply <> 1";
        }
        if (isset($queues['departments'])) {
            $sql[] = "SELECT 'departments' AS type, 'approval' AS reason, department.id AS id, department.name AS title, company.name AS context, department.created AS created FROM `{$p}departments` AS department LEFT JOIN `{$p}companies` AS company ON company.id = department.companyid WHERE department.status = 0";
        }
        if (isset($queues['coverletters'])) {
            $sql[] = "SELECT 'coverletters' AS type, 'approval' AS reason, coverletter.id AS id, coverletter.title AS title, '' AS context, coverletter.created AS created FROM `{$p}coverletters` AS coverletter WHERE coverletter.status = 0";
        }
        if (isset($queues['folders'])) {
            $sql[] = "SELECT 'folders' AS type, 'approval' AS reason, folder.id AS id, folder.name AS title, '' AS context, folder.created AS created FROM `{$p}folders` AS folder WHERE folder.status = 0";
        }
        if (isset($queues['messages'])) {
            $sql[] = "SELECT 'messages' AS type, 'approval' AS reason, message.id AS id, message.subject AS title, '' AS context, message.created AS created FROM `{$p}messages` AS message WHERE message.status = 0";
        }
        $rows = array();
        foreach ($sql as $query) {
            $rows = array_merge($rows, (array) $db->get_results($query . ' ORDER BY created DESC LIMIT ' . ($type !== '' ? 1000 : 50)));
        }
        $words = array_filter(preg_split('/\s+/', strtolower(trim((string) $q))));
        if ($words) {
            $rows = array_values(array_filter($rows, function ($row) use ($words) {
                $hay = strtolower($row->title . ' ' . $row->context);
                foreach ($words as $word) {
                    if (strpos($hay, $word) === false) {
                        return false;
                    }
                }
                return true;
            }));
        }
        usort($rows, function ($a, $b) {
            return strcmp((string) $b->created, (string) $a->created);
        });
        wpjobportal::$_data['approvals'] = array('rows' => $rows, 'queues' => $all_queues, 'type' => $type, 'q' => (string) $q);
    }

    function getMessagekey() {
        return 'admin_approvals';
    }
}

?>

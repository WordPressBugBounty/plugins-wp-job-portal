REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.5.1', 'default');

INSERT IGNORE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES 
('show_job_listing_top_filter', '1', 'job', ''),
('show_top_filter_title', '1', 'job', ''),
('show_top_filter_location', '1', 'job', ''),
('show_top_filter_category', '0', 'job', ''),
('job_new_badge_days', '3', 'job', ''),
('joblisting_ajax_filter_tags', '[jobtype][workplacetype][jobsalaryrange][dateposted]', 'job', 'joblistingenhancer'),
('joblisting_ajax_show_sorting', '1', 'job', 'joblistingenhancer');

ALTER TABLE `#__wj_portal_jobs` ADD `workplace_type` tinyint(1) NULL DEFAULT '0';
ALTER TABLE `#__wj_portal_jobs` ADD `is_urgent` tinyint(1) NULL DEFAULT '0';

INSERT IGNORE INTO `#__wj_portal_fieldsordering` 
(`field`, `fieldtitle`, `ordering`, `section`, `is_section_headline`, `placeholder`, `description`, `fieldfor`, `published`, `isvisitorpublished`, `sys`, `cannotunpublish`, `required`, `isuserfield`, `userfieldtype`, `userfieldparams`, `search_user`, `search_visitor`, `search_ordering`, `cannotsearch`, `showonlisting`, `cannotshowonlisting`, `depandant_field`, `readonly`, `size`, `maxlength`, `cols`, `rows`, `j_script`, `visible_field`, `visibleparams`) 
VALUES 
('workplace_type', 'Workplace Type', 36, '', 0, NULL, 'Specify if the job is On-site, Hybrid, or Remote', 2, 1, 1, 0, 0, 0, 0, '', '', 1, 1, NULL, 0, 1, 0, '', 0, 0, 0, 0, 0, '', NULL, NULL),
('is_urgent', 'Is Urgent', 35, '', 0, NULL, 'Check if this job is urgently hiring', 2, 1, 1, 0, 0, 0, 0, '', '', 1, 1, NULL, 0, 1, 0, '', 0, 0, 0, 0, 0, '', NULL, NULL);

UPDATE `#__wj_portal_fieldsordering` SET `cannotshowonlisting` = 0 WHERE `field` = 'tags' AND `fieldfor` = 2;
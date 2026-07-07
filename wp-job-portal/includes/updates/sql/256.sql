REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.5.6', 'default');

INSERT IGNORE INTO `#__wj_portal_config` (configname, configvalue, configfor, addon) VALUES
('enable_company_copilot', '1', 'company', NULL),
('enable_job_copilot', '1', 'job', NULL),
('enable_resume_copilot', '1', 'resume', NULL),
('enable_coverletter_copilot', '1', 'coverletter', NULL),
('enable_cover_letter_quick_apply', '0', 'coverletter', 'coverletter'),
('enable_jobapply_copilot', '1', 'jobapply', NULL);

INSERT IGNORE INTO `#__wj_portal_config` (configname, configvalue, configfor, addon) VALUES
('show_match_score_job_list', '1', 'job', 'smartmatching'),
('show_match_score_resume_list', '1', 'resume', 'smartmatching'),
('show_match_score_applied_resume', '1', 'jobapply', 'smartmatching');
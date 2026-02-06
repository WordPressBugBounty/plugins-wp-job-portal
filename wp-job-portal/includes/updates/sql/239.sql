REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.3.9', 'default');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('jobseeker_show_resume_status_section', 1, 'jobseeker', 'advanceresumebuilder');

UPDATE `#__wj_portal_fieldsordering` SET cannotshowonlisting = '0' WHERE id = 76;

UPDATE `#__wj_portal_fieldsordering` SET cannotshowonlisting = '0' WHERE id = 31;

UPDATE `#__wj_portal_fieldsordering` SET cannotshowonlisting = '0' WHERE id = 7;
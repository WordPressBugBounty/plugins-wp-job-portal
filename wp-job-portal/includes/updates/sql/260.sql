REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES
('versioncode', '2.6.0', 'default');

INSERT IGNORE INTO `#__options` (`option_name`, `option_value`, `autoload`) SELECT 'wpjobportal_product_mode', 'careers_ats', 'off' FROM DUAL WHERE EXISTS (SELECT 1 FROM (SELECT `option_value` AS wpjp_flag FROM `#__options` WHERE `option_name` = 'wpjobportal_multiple_employers') AS wpjp_src WHERE wpjp_src.wpjp_flag = '0') AND NOT EXISTS (SELECT 1 FROM (SELECT `configvalue` AS wpjp_sub FROM `#__wj_portal_config` WHERE `configname` = 'submission_type') AS wpjp_sub_src WHERE wpjp_sub_src.wpjp_sub <> '1');
INSERT IGNORE INTO `#__options` (`option_name`, `option_value`, `autoload`) SELECT 'wpjobportal_product_mode', 'marketplace', 'off' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM (SELECT `option_value` AS wpjp_flag FROM `#__options` WHERE `option_name` = 'wpjobportal_multiple_employers') AS wpjp_src WHERE wpjp_src.wpjp_flag = '0');

UPDATE `#__wj_portal_config` SET `configvalue` = '1' WHERE `configname` = 'jobautoapprove' AND EXISTS (SELECT 1 FROM `#__options` WHERE `option_name` = 'wpjobportal_product_mode' AND `option_value` = 'careers_ats');
UPDATE `#__wj_portal_config` SET `configvalue` = '1' WHERE `configname` = 'companyautoapprove' AND EXISTS (SELECT 1 FROM `#__options` WHERE `option_name` = 'wpjobportal_product_mode' AND `option_value` = 'careers_ats');
UPDATE `#__wj_portal_config` SET `configvalue` = '0' WHERE `configname` = 'showemployerlink' AND EXISTS (SELECT 1 FROM `#__options` WHERE `option_name` = 'wpjobportal_product_mode' AND `option_value` = 'careers_ats');
UPDATE `#__wj_portal_config` SET `configvalue` = '0' WHERE `configname` = 'visitor_can_post_job' AND EXISTS (SELECT 1 FROM `#__options` WHERE `option_name` = 'wpjobportal_product_mode' AND `option_value` = 'careers_ats');
UPDATE `#__wj_portal_config` SET `configvalue` = '1' WHERE `configname` = 'submission_type' AND EXISTS (SELECT 1 FROM `#__options` WHERE `option_name` = 'wpjobportal_product_mode' AND `option_value` = 'careers_ats');
UPDATE `#__wj_portal_config` SET `configvalue` = '1' WHERE `configname` = 'showemployerlink' AND EXISTS (SELECT 1 FROM `#__options` WHERE `option_name` = 'wpjobportal_product_mode' AND `option_value` = 'marketplace');

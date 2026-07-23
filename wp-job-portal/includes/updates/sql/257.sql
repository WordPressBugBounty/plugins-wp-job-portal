REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.5.7', 'default');

INSERT IGNORE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES
('job_sitemap_enable', '0', 'seo', NULL),
('job_sitemap_limit', '5000', 'seo', NULL);


-- =========================================================================
-- 2.9.1
-- =========================================================================
--
-- Runs once, when the plugin moves to 2.9.1. Idempotent: INSERT IGNORE and
-- REPLACE INTO only, and no setting an owner has chosen is changed.

-- ----- The two settings 2.9.1 relies on -----
-- Every site has had both rows since activation created them; these only fill
-- them in on a site that somehow lost one. "Job Expiry Days (Free)" stays empty
-- on an upgrade, on purpose: Core then keeps a job 730 days when no Stop
-- Publishing date is given, as before (only fresh installs start at 30).
-- "User can add new city" keeps its add-on tag so the absorbed-config guard
-- still protects it.
INSERT IGNORE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('newtyped_cities', '0', 'city', 'addressdata');
INSERT IGNORE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`) VALUES ('jobexpiry_days_free', '', 'paidsubmission', NULL);

-- ---------------------------------------------------------------------------
-- Last of all, the version (see 270.sql for why it comes last).
REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.9.1', 'default');

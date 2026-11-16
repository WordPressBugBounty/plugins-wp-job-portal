-- =========================================================================
-- 2.9.0
-- =========================================================================
--
-- Runs once, when the plugin moves to 2.9.0. Idempotent: CREATE TABLE IF NOT
-- EXISTS and REPLACE INTO only.

-- ----- The AI layer: one usage table for every AI request -----
-- Counts only (task, engine, model, tokens, cost, time, result); never the text
-- that was sent or written. WPJOBPORTAL_AI_Usage reads and prunes it.
CREATE TABLE IF NOT EXISTS `#__wj_portal_ai_usage` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `engine` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(100) NOT NULL DEFAULT '',
  `funded` varchar(10) NOT NULL DEFAULT 'byok',
  `feature` varchar(60) NOT NULL DEFAULT '',
  `itemid` bigint(20) NOT NULL DEFAULT '0',
  `userid` bigint(20) NOT NULL DEFAULT '0',
  `intokens` int(11) NOT NULL DEFAULT '0',
  `outtokens` int(11) NOT NULL DEFAULT '0',
  `tokensknown` tinyint(1) NOT NULL DEFAULT '1',
  `cost` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `priced` tinyint(1) NOT NULL DEFAULT '1',
  `ms` int(11) NOT NULL DEFAULT '0',
  `ok` tinyint(1) NOT NULL DEFAULT '1',
  `error` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `wpjp_ai_when` (`created`),
  KEY `wpjp_ai_user` (`userid`, `created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Last of all, the version (see 270.sql for why it comes last).
REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.9.0', 'default');

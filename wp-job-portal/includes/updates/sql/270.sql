
-- =========================================================================
-- 2.7.0
-- =========================================================================
--
-- Everything below shipped as part of 2.7.0. It was written across four files
-- while 2.7.0 was in development, and each of those bumped the version code a
-- step further — 2.7.1, 2.7.2, 2.7.3 — for releases that will never exist,
-- because the product ships as 2.7.0. A customer upgrading would have been
-- told they were on 2.7.3 by a build whose header reads 2.7.0.
--
-- They are one migration now. Nothing was dropped in the merge: every
-- statement from 271, 272 and 273 is reproduced below, in order.
--
-- All of it is idempotent — CREATE TABLE IF NOT EXISTS, REPLACE INTO, and a
-- DELETE that matches nothing once it has run — so a site part-way through the
-- old sequence lands in the same place as a site that never ran any of it.

-- ----- from 271.sql -----

-- Core absorption: Shortlist and Tags move from paid add-ons into the free core.
--
-- Both tables already exist on any site that owned the add-on, and they hold
-- live customer data: saved jobs with ratings and comments, and the tag
-- vocabulary attached to jobs. IF NOT EXISTS adopts those in place and creates
-- nothing. On a clean install these are new.
--
-- Schemas are taken verbatim from the 2.6.0 add-on originals. Charset and
-- collation are left to the site default, matching how the add-ons created them.

CREATE TABLE IF NOT EXISTS `#__wj_portal_jobshortlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `jobid` int(11) NOT NULL,
  `comments` text NOT NULL,
  `rate` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `serverstatus` varchar(255) DEFAULT NULL,
  `serverid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__wj_portal_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(300) NOT NULL,
  `alias` varchar(300) NOT NULL,
  `tagfor` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `createdby` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----- from 272.sql -----

-- Job Alert moves into the free core as a tier-split feature.
--
-- Core owns the implementation so the free tier works with no suite installed;
-- Pro raises the limits rather than re-implementing it. That means Core must be
-- able to create these tables on a site that has never had the add-on or Pro.
--
-- On every other site they already exist and hold live alert subscriptions, so
-- IF NOT EXISTS adopts them untouched. Pro's own migrations 1006 and 1007 remain
-- in place and are equally idempotent: a site with both simply adopts twice.
--
-- Schema taken verbatim from the 2.6.0 add-on original.

CREATE TABLE IF NOT EXISTS `#__wj_portal_jobalertsetting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `subcategoryid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contactemail` varchar(255) NOT NULL,
  `country` varchar(100) NOT NULL,
  `state` varchar(60) DEFAULT NULL,
  `county` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zipcode` varchar(60) DEFAULT NULL,
  `sendtime` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `price` int(20) NOT NULL,
  `userpackageid` int(11) NOT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `company` int(11) DEFAULT NULL,
  `jobtype` int(11) DEFAULT '0',
  `workplace_type` int(11) DEFAULT '0',
  `is_urgent` tinyint(1) DEFAULT '0',
  `tags` varchar(255) DEFAULT NULL,
  `alerttype` tinyint(1) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `coordinatesradius` int(11) NOT NULL,
  `lastmailsend` datetime NOT NULL,
  `serverid` int(11) DEFAULT NULL,
  `serverstatus` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__wj_portal_jobalertcities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alertid` int(11) NOT NULL,
  `cityid` int(11) NOT NULL,
  `serverid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alertid` (`alertid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----- from 273.sql -----

-- The newest-jobs slug row, shipped twice
--
-- activation.php inserted two rows for the same page. Row 60 is correct. Row 87
-- was malformed as well as duplicated: its `description` column held '1' — the
-- value belonging in `status` — and its `status` was NULL, so the columns after
-- `filename` were written one place to the left.
--
-- Two other filenames appear twice, `controlpanel` and `mystats`, and those are
-- legitimate: there is one row per module, employer and jobseeker, which is
-- exactly why getPageTitleByFileName() adds a modulename condition for those two
-- and only those two. `newestjobs` has no such reason.
--
-- WHAT IT COST
--
-- Nothing visible, because both rows carried the same title and the lookup takes
-- the first. The hazard was the Slug admin screen: an administrator editing the
-- newest-jobs slug or its title would edit one row while the front end read the
-- other, and the change would appear to do nothing.
--
-- The duplicate insert has been removed from activation.php, so new installs get
-- one row. This is for the installs that already have two.
--
-- SAFETY
--
-- Keeps the lowest id and removes the rest, so it is idempotent: on a site that
-- already has one row it matches nothing. Scoped to this one filename rather
-- than deduplicating the table generally, because two filenames are supposed to
-- appear twice and a general rule would delete half of each.

DELETE FROM `#__wj_portal_slug`
 WHERE `filename` = 'newestjobs'
   AND `id` NOT IN (
       SELECT `keep` FROM (
           SELECT MIN(`id`) AS `keep`
             FROM `#__wj_portal_slug`
            WHERE `filename` = 'newestjobs'
       ) AS `survivor`
   );


-- VERIFY
--
-- Expect exactly one row, with a real description and status 1.
--
--   SELECT `id`, `slug`, `description`, `status`
--     FROM `#__wj_portal_slug`
--    WHERE `filename` = 'newestjobs';
--
-- And the two that are meant to appear twice, still twice:
--
--   SELECT `filename`, COUNT(*) FROM `#__wj_portal_slug`
--    GROUP BY `filename` HAVING COUNT(*) > 1;


-- ---------------------------------------------------------------------------
-- Replace the old job save-search slug.
--
-- Its route called getMyJobSaveSearchbyUid() on the jobsearch model. That
-- method was not defined there, and was not defined anywhere else either:
-- across Core and all 41 add-ons of the original distribution the name appeared
-- once, on the line that called it. The page had therefore never rendered for
-- anyone in any version - it fataled - and nothing ever linked to it, so it was
-- reachable only by typing the URL.
--
-- Saved job searches now exist, so the row is not simply dropped: it is deleted
-- here and inserted again further down, pointing at a page that works. The two
-- statements are in this one file and in this order, so a site upgrading in a
-- single step never sees the gap.
--
-- It is matched on filename rather than id because deleting a slug removes a
-- URL, and a site that renumbered its slugs must not lose a different page.
DELETE FROM `#__wj_portal_slug` WHERE `filename` = 'jobsavesearch';

-- =========================================================================
-- Spam checks: Cloudflare Turnstile, and a score for reCAPTCHA v3
-- =========================================================================
--
-- Three new settings. Nothing is altered and nothing is removed: the rows the
-- captcha already had keep their values, and a site that has configured Google
-- reCAPTCHA carries on using it untouched.
--
-- Inserted conditionally rather than with REPLACE, because REPLACE would
-- overwrite whatever an administrator had already typed if this file were ever
-- applied twice.
--
--   turnstile_sitekey / turnstile_secretkey
--       Cloudflare Turnstile. Empty until somebody fills them in, and the
--       captcha service treats a provider with no keys as not configured and
--       falls back to the built-in check rather than rendering a widget that
--       can never resolve - which would make the form unsubmittable.
--
--   recaptcha_score_threshold
--       reCAPTCHA v3 does not challenge anybody. It returns a score between 0
--       and 1, and the code never read it, so every site set to v3 was
--       accepting everything. 0.5 is Google's own suggested starting point.
--
-- A site that upgrades without running this is not broken: every one of these
-- has the same default in code as it has here.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`)
SELECT 'turnstile_sitekey', '', 'captcha' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'turnstile_sitekey');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`)
SELECT 'turnstile_secretkey', '', 'captcha' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'turnstile_secretkey');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`)
SELECT 'recaptcha_score_threshold', '0.5', 'captcha' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'recaptcha_score_threshold');

-- The arithmetic captcha's own settings are deliberately left in place. They
-- are no longer read by anything, and deleting them would be the one change
-- here that could not be undone if somebody wanted them back.
--
-- Verify:
--
--   SELECT configname, configvalue FROM `#__wj_portal_config`
--    WHERE configname IN ('turnstile_sitekey','turnstile_secretkey','recaptcha_score_threshold');
--
-- Expect three rows.

-- Saved job searches.
--
-- A job seeker runs a search, names it, and re-runs it later without filling
-- the form in again. The employer side has had this since Pro's 1002.sql
-- (`#__wj_portal_resumesearches`). This is the job seeker half, and the
-- columns deliberately match `includes/tables/jobsearch.php`, which has
-- declared this table for years without anything ever creating it.
--
-- `searchparams` holds the same array the job search form produces, so
-- replaying a saved search runs the ordinary job query rather than a second,
-- hand-written copy of it.

CREATE TABLE IF NOT EXISTS `#__wj_portal_jobsearches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `searchname` varchar(50) NOT NULL,
  `searchparams` longtext NOT NULL,
  `params` longtext NOT NULL,
  `created` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The page that lists them. Added by id so a site that already has 93 keeps
-- what it has rather than having a row replaced underneath it.

INSERT INTO `#__wj_portal_slug` (`id`, `slug`, `defaultslug`, `filename`, `description`, `status`, `pagetitle`, `defaultpagetitle`, `modulename`, `titleoptions`)
SELECT 93, 'job-save-searches', 'job-save-searches', 'jobsavesearch', 'slug for job save search page', 1, 'Job Save Searches [separator] [sitename]', 'Job Save Searches [separator] [sitename]', 'jobsearch', '[separator],[sitename]' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_slug` WHERE `defaultslug` = 'job-save-searches');

-- Verify:
--
--   SELECT COUNT(*) FROM `#__wj_portal_jobsearches`;        -- expect 0, not an error
--   SELECT slug FROM `#__wj_portal_slug` WHERE defaultslug = 'job-save-searches';
--
-- Rewrite rules are built from the slug table, so the route appears after
-- permalinks are flushed once.

-- ---------------------------------------------------------------------------
-- Make the visitor resume settings mean what they say.
--
-- Two settings decided who could see resumes, in two different sections of the
-- settings screen, and they contradicted each other on every install:
--
--   Resume Settings  "Allow Resume Search"         Allowed only to employers
--   Content Access   "Visitor Can Search Resumes"  Yes
--
-- "Allow Resume Search" was consulted first and won, so the Content Access
-- toggles never had any effect. An administrator could read that page, see that
-- visitors were allowed, and be wrong.
--
-- They are now sub-switches of "Allowed only to employers" and are asked. That
-- makes the stored value matter for the first time, and the stored value is a
-- Yes that nobody chose: it is the shipped default of a control that has never
-- done anything. Honouring it would put every upgrading site's resumes in front
-- of the public without anybody asking for that.
--
-- So the toggles are turned off wherever "Allow Resume Search" is anything but
-- "Allowed to all", which is exactly the set of sites where they were being
-- ignored. Behaviour after this upgrade is identical to behaviour before it.
-- An administrator who wants visitors to search resumes now has a switch that
-- works, and turns it on deliberately.
--
-- Sites set to "Allowed to all" are left alone: there the visitor toggle is not
-- consulted, and visitors could already search.

-- Guarded on the version, because this is the one statement here that is not
-- reversible by re-running it. Everything else settles on the same answer
-- however many times it is applied; this one reads a setting and turns it off,
-- so applying the file again after an administrator has deliberately switched
-- the toggle back on would switch it off under them. The runner applies each
-- migration once, so that only reaches somebody re-applying the file by hand -
-- which the VERIFY blocks in this file invite people to do.
--
-- The version is compared the way WPJOBPORTAL_Migration_Service reads it: dots
-- removed, as a number. It is still the pre-upgrade value at this point,
-- because the versioncode row is written at the very end of this file.

UPDATE `#__wj_portal_config`
   SET `configvalue` = '0'
 WHERE `configname` IN ('visitorview_emp_resumesearch', 'visitorview_emp_resumecat')
   AND (SELECT `configvalue` FROM (SELECT `configvalue` FROM `#__wj_portal_config` WHERE `configname` = 'allow_search_resume') AS `current_setting`) <> '1'
   AND (SELECT CAST(REPLACE(`configvalue`, '.', '') AS UNSIGNED) FROM (SELECT `configvalue` FROM `#__wj_portal_config` WHERE `configname` = 'versioncode') AS `installed_version`) < 270;

-- Verify:
--
--   SELECT configname, configvalue FROM `#__wj_portal_config`
--    WHERE configname IN ('allow_search_resume','visitorview_emp_resumesearch','visitorview_emp_resumecat');
--
-- On a site set to "employers only" expect 2, 0, 0 - the same pages visible to
-- the same people as before the upgrade.

-- ---------------------------------------------------------------------------
-- Settings the screen offered but the database never had.
--
-- The AI and matching settings are declared in activation.php and in the .sql
-- file for the version that introduced them. Neither reaches a site that was
-- already past that version: activation inserts every default in one statement
-- keyed on configname, so until that statement was made to IGNORE duplicates it
-- failed on the first row that already existed and wrote nothing.
--
-- The settings screen renders from its own list, not from the table, so all
-- eleven appeared with a switch. Turning one on did nothing, because saving
-- updates rows and there was no row to update - and the administrator was told
-- the save had succeeded. Every symptom reported against the matching suite
-- traces back to here: match scores that never appeared, dashboard sections
-- that never rendered, AI search fields that were never shown.
--
-- Values are the shipped defaults, so a site lands exactly where a fresh
-- install of the same version would. Rows that exist are left alone.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT * FROM (
    SELECT 'job_search_ai_form'               AS c, '0' AS v, 'job'      AS f, 'aijobsearch'        AS a
    UNION ALL SELECT 'job_list_ai_filter',               '0', 'job',      'aijobsearch'
    UNION ALL SELECT 'resume_search_ai_form',            '0', 'resume',   'airesumesearch'
    UNION ALL SELECT 'resume_list_ai_filter',            '0', 'resume',   'airesumesearch'
    UNION ALL SELECT 'show_suggested_jobs_button',       '1', 'job',      'aisuggestedjobs'
    UNION ALL SELECT 'show_suggested_jobs_dashboard',    '1', 'job',      'aisuggestedjobs'
    UNION ALL SELECT 'show_suggested_resumes_button',    '1', 'resume',   'aisuggestedresumes'
    UNION ALL SELECT 'show_suggested_resumes_dashboard', '1', 'resume',   'aisuggestedresumes'
    UNION ALL SELECT 'show_match_score_job_list',        '1', 'job',      'smartmatching'
    UNION ALL SELECT 'show_match_score_resume_list',     '1', 'resume',   'smartmatching'
    UNION ALL SELECT 'show_match_score_applied_resume',  '1', 'jobapply', 'smartmatching'
) AS `shipped_defaults`
WHERE NOT EXISTS (
    SELECT 1 FROM `#__wj_portal_config` AS `existing` WHERE `existing`.`configname` = `shipped_defaults`.`c`
);

-- ---------------------------------------------------------------------------
-- And file the ones that already exist under the right section.
--
-- The insert above skips any name already present, which is right for the
-- value - an administrator's choice must not be overwritten - but leaves the
-- row's `configfor` and `addon` as whatever put it there. One route does not
-- know them: storeConfig() creates a missing setting when the settings screen
-- saves it, and writes 'wpjobportal' and NULL, because at that point nothing
-- tells it where the setting belongs.
--
-- The value is read by name, so such a row works. What it breaks is anything
-- that groups settings by section or by add-on - including this file's own
-- verify query below, which counts by `addon` and would report ten of eleven
-- on a site where the screen had created one.
--
-- Only the two metadata columns are touched. `configvalue` is never written
-- here.

UPDATE `#__wj_portal_config` SET `configfor` = 'job',      `addon` = 'aijobsearch'        WHERE `configname` IN ('job_search_ai_form', 'job_list_ai_filter');
UPDATE `#__wj_portal_config` SET `configfor` = 'resume',   `addon` = 'airesumesearch'     WHERE `configname` IN ('resume_search_ai_form', 'resume_list_ai_filter');
UPDATE `#__wj_portal_config` SET `configfor` = 'job',      `addon` = 'aisuggestedjobs'    WHERE `configname` IN ('show_suggested_jobs_button', 'show_suggested_jobs_dashboard');
UPDATE `#__wj_portal_config` SET `configfor` = 'resume',   `addon` = 'aisuggestedresumes' WHERE `configname` IN ('show_suggested_resumes_button', 'show_suggested_resumes_dashboard');
UPDATE `#__wj_portal_config` SET `configfor` = 'job',      `addon` = 'smartmatching'      WHERE `configname` = 'show_match_score_job_list';
UPDATE `#__wj_portal_config` SET `configfor` = 'resume',   `addon` = 'smartmatching'      WHERE `configname` = 'show_match_score_resume_list';
UPDATE `#__wj_portal_config` SET `configfor` = 'jobapply', `addon` = 'smartmatching'      WHERE `configname` = 'show_match_score_applied_resume';

-- Verify:
--
--   SELECT COUNT(*) FROM `#__wj_portal_config` WHERE addon IN
--     ('aijobsearch','airesumesearch','aisuggestedjobs','aisuggestedresumes','smartmatching');
--
-- Expect 11 or more. Fewer means the screen is still offering switches that
-- cannot be turned on.

-- ---------------------------------------------------------------------------
-- List cards can carry a description excerpt.
--
-- They always could, but only inside the Elegant Design add-on, which had to
-- carry its own copy of the job, resume and company cards to do it - and a
-- forked card means every rule on that card is written twice. The excerpt is
-- Core's now, under one setting for all three, so one card serves both looks.
--
-- Default 0, because switching it on is a visible change to a site that has
-- never shown descriptions in a list. Elegant Design sites turn it on to keep
-- the cards they already have.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'show_job_description_in_list', '0', 'job', '' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'show_job_description_in_list');

-- ---------------------------------------------------------------------------
-- Category aliases with a space.
--
-- Four of the categories installed with the plugin were shipped with a space in
-- the alias (mortgage-real estate, air host-hostess, automotive equipment-parts,
-- shppping-distrubution companies). The link to each became .../jobs/mortgage-
-- real%20estate_1039, which the router does not match: a 404. Categories saved
-- from the admin form never have one (the form turns spaces into dashes), and
-- the install data is corrected too. Safe to run again: it only touches aliases
-- that still contain a space.

UPDATE `#__wj_portal_categories`
   SET `alias` = REPLACE(`alias`, ' ', '-')
 WHERE `alias` LIKE '% %';

-- ---------------------------------------------------------------------------
-- "has beed received" in three e-mail subjects.
--
-- The new resume, new visitor job and new visitor resume templates were
-- installed with the typo "has beed received". The install data is corrected;
-- here only a subject that is still exactly the installed text is changed, so a
-- template the administrator has edited is left alone. Safe to run again.

UPDATE `#__wj_portal_emailtemplates`
   SET `subject` = 'WP Job Portal : New resume {RESUME_TITLE} has been received'
 WHERE `templatefor` = 'resume-new'
   AND `subject` = 'WP Job Portal : New resume {RESUME_TITLE} has beed received';

UPDATE `#__wj_portal_emailtemplates`
   SET `subject` = 'WP Job Portal : New Visitor Job {JOB_TITLE} has been received of {COMPANY_NAME} company'
 WHERE `templatefor` = 'job-new-vis'
   AND `subject` = 'WP Job Portal : New Visitor Job {JOB_TITLE} has beed received of {COMPANY_NAME} company';

UPDATE `#__wj_portal_emailtemplates`
   SET `subject` = 'WP Job Portal :  New resume {RESUME_TITLE} has been received'
 WHERE `templatefor` = 'resume-new-vis'
   AND `subject` = 'WP Job Portal :  New resume {RESUME_TITLE} has beed received';

-- ---------------------------------------------------------------------------
-- The employer menu's Recruiting and Marketplace sections, as settings.
--
-- Every other section of the employer's dashboard menu has a switch under
-- Configurations > Employer > Dashboard Navigation Links; the two sections the
-- suites add had none, so an administrator could hide Messages but not
-- Recruiting. One switch each. Both start on, which is what every site shows
-- today, so adding them changes nothing until somebody turns one off; and
-- WHERE NOT EXISTS leaves a site's choice alone however often this runs.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruiting_menu', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruiting_menu');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_marketplace_menu', '1', 'emcontrolpanel', 'marketplacesuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_marketplace_menu');

-- ---------------------------------------------------------------------------
-- A switch for each link in those sections, and for the two links the Recruit
-- Suite adds to the job seeker's menu.
--
-- The section switches above hide all of Recruiting or all of Marketplace; an
-- employer who uses the Pipeline but not Scorecards had no way to keep one and
-- drop the other, where My Jobs, Messages and the rest each have their own.
-- All start on, as above, and WHERE NOT EXISTS keeps a site's choice.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_overview', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_overview');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_jobs', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_jobs');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_candidates', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_candidates');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_pipeline', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_pipeline');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_tasks', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_tasks');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_interviews', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_interviews');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_scorecards', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_scorecards');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_offers', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_offers');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_notifications', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_notifications');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_insights', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_insights');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_pools', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_pools');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruit_team', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruit_team');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_company_verification', '1', 'emcontrolpanel', 'marketplacesuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_company_verification');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_promotion_schedule', '1', 'emcontrolpanel', 'marketplacesuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_promotion_schedule');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'js_recruitment_center', '1', 'jscontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'js_recruitment_center');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'js_recruiting_center', '1', 'jscontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'js_recruiting_center');

-- The Recruit Suite's dashboard boxes - Recruiting Center for the employer,
-- Recruitment updates for the job seeker - with a switch each under Dashboard
-- Layout & Widgets, like the graphs and boxes Core puts there. Hiding the menu
-- link left the box and its "Open Recruitment Center" button in place.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'em_recruiting_dashboard', '1', 'emcontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'em_recruiting_dashboard');

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'js_recruitment_dashboard', '1', 'jscontrolpanel', 'recruitsuite' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'js_recruitment_dashboard');

-- ---------------------------------------------------------------------------
-- Whether people may create accounts, as WP Job Portal's own setting.
--
-- The registration page followed WordPress's "Anyone can register", which is
-- off on every new site and never mentioned by the setup wizard. The new
-- setting starts at 'wordpress' - follow that option exactly as before - so an
-- existing site behaves the same until its administrator chooses otherwise.

INSERT INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`, `addon`)
SELECT 'account_signup', 'wordpress', 'register', NULL FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `#__wj_portal_config` WHERE `configname` = 'account_signup');

-- ---------------------------------------------------------------------------
-- Last of all, the version.
--
-- It used to be the first statement in this file. That told a site it was on
-- 2.7.0 before any of the work had been done, so an upgrade that failed
-- halfway left a database claiming a version it did not have - and left the
-- statements below it unable to tell where the site had come from. Written
-- here, a failure leaves the old version in place and the migration is retried.

REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.7.0', 'default');

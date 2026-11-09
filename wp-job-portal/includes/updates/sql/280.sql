
-- =========================================================================
-- 2.8.0
-- =========================================================================
--
-- Runs once, when the plugin moves to 2.8.0. Every statement changes only rows
-- that still hold a known-bad value, so running it again matches nothing, and a
-- site whose owner already changed a setting keeps their value.

-- ----- D-4: wrong defaults written by activation -----

-- Featured companies lasted 34,400 days (94 years); featured jobs default to 30.
UPDATE `#__wj_portal_config` SET `configvalue` = '30'
WHERE `configname` = 'featuredcompany_days_perlisting' AND `configvalue` = '34400';

-- The job-alert price pointed at currency 45, which was never created (the seed
-- has 1-4). Use the site's default currency, or the first one.
UPDATE `#__wj_portal_config` AS config
LEFT JOIN `#__wj_portal_currencies` AS currency ON currency.`id` = config.`configvalue`
SET config.`configvalue` = COALESCE(
    (SELECT id FROM (SELECT `id` FROM `#__wj_portal_currencies` ORDER BY `default` DESC, `ordering` ASC, `id` ASC LIMIT 1) AS first_currency),
    '1')
WHERE config.`configname` = 'job_currency_jobalert_perlisting' AND currency.`id` IS NULL;

-- Label typos in the seeded currencies, left alone once a site renamed them.
UPDATE `#__wj_portal_currencies` SET `title` = 'US Dollar' WHERE `title` = 'US Doller';
UPDATE `#__wj_portal_currencies` SET `symbol` = '€' WHERE `symbol` = ' €';

-- The default map centre was meant to be Los Angeles (34.0549, -118.2426); the
-- minus sign was missing, so maps opened in eastern China.
UPDATE `#__wj_portal_config` AS lng
JOIN `#__wj_portal_config` AS lat ON lat.`configname` = 'default_latitude' AND lat.`configvalue` = '34.0549'
SET lng.`configvalue` = '-118.2426'
WHERE lng.`configname` = 'default_longitude' AND lng.`configvalue` = '118.2426';

-- ----- D-10: the shared Google Maps key -----

-- Every install shipped with the same key, so all sites shared one daily quota.
-- Clear it; a site that entered its own key keeps it. Maps stay hidden until
-- the owner adds one (the admin pages say so, and say why only where this ran).
INSERT IGNORE INTO `#__options` (`option_name`, `option_value`, `autoload`)
SELECT 'wpjobportal_280_shared_maps_key_removed', '1', 'off' FROM `#__wj_portal_config`
WHERE `configname` = 'google_map_api_key' AND `configvalue` = 'AIzaSyCZcnAK0DiGg8lAXej74e7PlrhkfCM86-M';
UPDATE `#__wj_portal_config` SET `configvalue` = ''
WHERE `configname` = 'google_map_api_key' AND `configvalue` = 'AIzaSyCZcnAK0DiGg8lAXej74e7PlrhkfCM86-M';

-- ----- D-11: page settings an untouched Save pointed at the Sample Page -----

-- These dropdowns had no "None", so a setting left at 0 was saved as the first
-- page in the list (WordPress's Sample Page) the next time anyone pressed Save.
-- Put back to none where they point at the Sample Page or at a page that is
-- gone or unpublished; a real page the owner chose (a terms page has no plugin
-- shortcode, so that is not used as the test) is kept. The names reset are
-- noted for a one-time admin notice.
INSERT IGNORE INTO `#__options` (`option_name`, `option_value`, `autoload`)
SELECT 'wpjobportal_280_page_settings_reset', GROUP_CONCAT(c.`configname`), 'off'
FROM `#__wj_portal_config` AS c LEFT JOIN `#__posts` AS p ON p.`ID` = c.`configvalue`
WHERE c.`configname` IN ('terms_and_conditions_page_company','terms_and_conditions_page_job','terms_and_conditions_page_resume','register_employer_redirect_page','register_jobseeker_redirect_page','visitor_add_job_redirect_page','visitor_add_resume_redirect_page') AND c.`configvalue` NOT IN ('', '0')
  AND (p.`ID` IS NULL OR p.`post_type` <> 'page' OR p.`post_status` <> 'publish' OR p.`post_name` = 'sample-page')
HAVING COUNT(*) > 0;

UPDATE `#__wj_portal_config` AS c LEFT JOIN `#__posts` AS p ON p.`ID` = c.`configvalue`
SET c.`configvalue` = '0'
WHERE c.`configname` IN ('terms_and_conditions_page_company','terms_and_conditions_page_job','terms_and_conditions_page_resume','register_employer_redirect_page','register_jobseeker_redirect_page','visitor_add_job_redirect_page','visitor_add_resume_redirect_page') AND c.`configvalue` NOT IN ('', '0')
  AND (p.`ID` IS NULL OR p.`post_type` <> 'page' OR p.`post_status` <> 'publish' OR p.`post_name` = 'sample-page');

-- ----- D-9: slugs with a leading or trailing hyphen -----

-- Titles ending in non-Latin letters left a slug like "...-unicode-", so the
-- URL ended in "--7". Trim the hyphens (the id after the last hyphen is what
-- finds the record, so old links keep working). Full regeneration of existing
-- slugs is left to the batched runner (D-13).
UPDATE `#__wj_portal_jobs` SET `alias` = TRIM(BOTH '-' FROM `alias`) WHERE `alias` LIKE '-%' OR `alias` LIKE '%-';
UPDATE `#__wj_portal_companies` SET `alias` = TRIM(BOTH '-' FROM `alias`) WHERE `alias` LIKE '-%' OR `alias` LIKE '%-';
UPDATE `#__wj_portal_resume` SET `alias` = TRIM(BOTH '-' FROM `alias`) WHERE `alias` LIKE '-%' OR `alias` LIKE '%-';

-- ----- W-2: hiring status apart from approval and what visitors see -----

-- Each hiring status says whether its jobs take applications; until now only
-- id 3 was treated as closed, in code.
ALTER TABLE `#__wj_portal_jobstatus` ADD COLUMN `acceptsapplications` tinyint(1) NOT NULL DEFAULT 1;
UPDATE `#__wj_portal_jobstatus` SET `acceptsapplications` = 0 WHERE `id` = 3 OR (`id` = 6 AND `title` IN ('Hold', 'On hold'));

-- The seeded statuses become Open / On hold / Closed; renamed ones are kept.
UPDATE `#__wj_portal_jobstatus` SET `title` = 'Open' WHERE `id` = 1 AND `title` = 'Sourcing';
UPDATE `#__wj_portal_jobstatus` SET `title` = 'Closed' WHERE `id` = 3 AND `title` = 'Closed to New Applicants';
UPDATE `#__wj_portal_jobstatus` SET `title` = 'On hold', `ordering` = 2 WHERE `id` = 6 AND `title` = 'Hold';

-- "Pending Approval" read like moderation, and Interviewing / Finalists
-- Identified are pipeline stages (Recruit Suite keeps those per application).
-- Where they still carry the seeded names, their jobs move to Open - they took
-- applications before and still do - and the statuses are unpublished, not
-- deleted, so an owner who wants one back can publish it again.
UPDATE `#__wj_portal_jobs` AS job
JOIN `#__wj_portal_jobstatus` AS st ON st.`id` = job.`jobstatus`
JOIN `#__wj_portal_jobstatus` AS open_status ON open_status.`id` = 1
SET job.`jobstatus` = 1
WHERE (st.`id`, st.`title`) IN ((2, 'Interviewing'), (4, 'Finalists Identified'), (5, 'Pending Approval'));
UPDATE `#__wj_portal_jobstatus` SET `isactive` = 0, `isdefault` = 0
WHERE (`id`, `title`) IN ((2, 'Interviewing'), (4, 'Finalists Identified'), (5, 'Pending Approval'));
UPDATE `#__wj_portal_jobstatus` AS st
JOIN (SELECT COUNT(*) AS n FROM `#__wj_portal_jobstatus` WHERE `isdefault` = 1 AND `isactive` = 1) AS defaults ON defaults.`n` = 0
SET st.`isdefault` = 1
WHERE st.`id` = 1;

-- Two job form fields were both labelled "Status".
UPDATE `#__wj_portal_fieldsordering` SET `fieldtitle` = 'Hiring status' WHERE `field` = 'jobstatus' AND `fieldfor` = 2 AND `fieldtitle` = 'Status';

-- ---------------------------------------------------------------------------
-- W-4: a first resume should not stop on a salary the seeker may not want to
-- state. Field 85 is the seeded field; an owner can make it required again.
UPDATE `#__wj_portal_fieldsordering` SET `required` = 0 WHERE `field` = 'salaryfixed' AND `fieldfor` = 3;


-- ---------------------------------------------------------------------------
-- W-12: default emails in the site's own name with correct wording. Only
-- templates still exactly as shipped change; an edited one is left alone.
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Company {COMPANY_NAME} has been {COMPANY_STATUS}' WHERE `templatefor` = 'company-status' AND MD5(`subject`) = 'e108adb6ba3901f38573136a64d2b9ae';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Company Status', 'Company status') WHERE `templatefor` = 'company-status' AND MD5(`body`) = '7a5fc9579e6987fff165cfbbb4ebf908';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your company {COMPANY_NAME} has been deleted' WHERE `templatefor` = 'company-delete' AND MD5(`subject`) = 'a54c26ef2c67d391bbbec12611be42a7';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved &#8211; Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Delete Company', 'Company deleted') WHERE `templatefor` = 'company-delete' AND MD5(`body`) = 'd9a5c3385156756a1ec46e4cd7ee2a0e';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your job {JOB_TITLE} has been {JOB_STATUS}' WHERE `templatefor` = 'job-status' AND MD5(`subject`) = 'd066eace74869e24b8d2143eb3065a1d';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job Status', 'Job status') WHERE `templatefor` = 'job-status' AND MD5(`body`) = '05589a7cbc07bbc1fcc50c6ffaf46fb6';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your job {JOB_TITLE} has been deleted' WHERE `templatefor` = 'job-delete' AND MD5(`subject`) = '48900e083a2be71d2a61d0f10cb3e378';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job Delete', 'Job deleted') WHERE `templatefor` = 'job-delete' AND MD5(`body`) = 'b1799c6d0ad8c7d3f6f9f9153229527e';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your resume {RESUME_TITLE} has been {RESUME_STATUS}' WHERE `templatefor` = 'resume-status' AND MD5(`subject`) = 'ab274f9de9abf3a093f18f0bcec04178';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Resume Status', 'Resume status') WHERE `templatefor` = 'resume-status' AND MD5(`body`) = '9bd8b088bdf9b676fd23297d6a029e45';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: You purchased the package {PACKAGE_NAME}' WHERE `templatefor` = 'employer-purchase-credit-pack' AND MD5(`subject`) = '63e15896cfc6ff7c6525177c24e3ab84';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Employer Purchase Credits Pack', 'Package purchased'), 'You have purchased new package', 'You have purchased the package'), 'Package purchased date', 'Purchase date:') WHERE `templatefor` = 'employer-purchase-credit-pack' AND MD5(`body`) = '6376616d324746102a24097b71b2c2e7';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: We received your application for {JOB_TITLE}' WHERE `templatefor` = 'jobapply-jobseeker' AND MD5(`subject`) = 'f0ccd5cf8cb87768bfd6746c10ea05c5';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job Apply Jobseeker', 'Application received'), 'You have to applied for', 'We received your application for'), ' job in ', ' at '), ' company by ', ', sent with your resume '), ' resume.', '.'), 'your resume has been', 'Its status is') WHERE `templatefor` = 'jobapply-jobseeker' AND MD5(`body`) = 'b9db9839c3444102550ae0d2242ff6b5';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New company {COMPANY_NAME}' WHERE `templatefor` = 'company-new' AND MD5(`subject`) = '7926d7d03047c45b4f8425ddeada394d';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Company', 'New company'), 'You have Created New Company {COMPANY_NAME},', 'Your new company has been added.'), 'We recieve new ', 'We received '), ' company.', '.') WHERE `templatefor` = 'company-new' AND MD5(`body`) = '01de61f3864c317d32b43ee3cba0838b';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New job {JOB_TITLE} at {COMPANY_NAME}' WHERE `templatefor` = 'job-new' AND MD5(`subject`) = '514015f05d31fca1352315a92e2a8f5e';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Job', 'New job'), 'We receive new ', 'We received your new job '), ' job of your ', ' for '), ' company.', '.'), 'Your job status is', 'Its status is') WHERE `templatefor` = 'job-new' AND MD5(`body`) = 'e5cb57f43c7c30b748beada7474b2262';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New resume {RESUME_TITLE}' WHERE `templatefor` = 'resume-new' AND MD5(`subject`) = '77740cc37000deaf6e77033a62c1c8b5';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Resume', 'New resume'), 'We receive new resume', 'We received your resume') WHERE `templatefor` = 'resume-new' AND MD5(`body`) = '40a06751a8a431c595d2dd79bd1c5560';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your package {PACKAGE_NAME} has expired' WHERE `templatefor` = 'jobseeker-package-expire' AND MD5(`subject`) = '578cdc1f2e1737d8e9bda096b42c4b45';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job seeker Package Expiry', 'Package expired'), 'has been expired', 'has expired'), 'You had purchased this package at', 'You purchased this package on') WHERE `templatefor` = 'jobseeker-package-expire' AND MD5(`body`) = 'e9554f001016829ee0ea01045cf0e1be';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: You purchased the package {PACKAGE_NAME}' WHERE `templatefor` = 'jobseeker-purchase-credit-pack' AND MD5(`subject`) = 'd714ca7cce79082a9badf6869b2f61a6';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job seeker Purchase Credits Pack', 'Package purchased'), 'You have purchased new package', 'You have purchased the package'), 'Package purchased date', 'Purchase date:') WHERE `templatefor` = 'jobseeker-purchase-credit-pack' AND MD5(`body`) = 'ff53022b22bff1594aaeec79361ba1ed';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your package {PACKAGE_NAME} has expired' WHERE `templatefor` = 'employer-package-expire' AND MD5(`subject`) = '578cdc1f2e1737d8e9bda096b42c4b45';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Employer Package Expiry', 'Package expired'), 'has been expired', 'has expired'), 'You had purchased this package at', 'You purchased this package on') WHERE `templatefor` = 'employer-package-expire' AND MD5(`body`) = '5c7f67af7378a44744e9a6ebc38a4f3b';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New application for {JOB_TITLE}' WHERE `templatefor` = 'jobapply-employer' AND MD5(`subject`) = '6f1aeae5d94eb63354709d2dee0dc145';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job Apply Employer', 'New application'), 'Mr/Mrs ', ''), 'Current Applied Resume status is', 'Current application status:') WHERE `templatefor` = 'jobapply-employer' AND MD5(`body`) = '7cce10c3ae50d45db98075001ff08eeb';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New visitor job {JOB_TITLE} at {COMPANY_NAME}' WHERE `templatefor` = 'job-new-vis' AND MD5(`subject`) = 'e82fda2bbbecdae8d2ae70243a7aeddd';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Visitor Job', 'New job'), 'We receive new ', 'We received your new job '), ' job of your ', ' for '), ' company.', '.'), 'Your new added job status is', 'Its status is') WHERE `templatefor` = 'job-new-vis' AND MD5(`body`) = '3818bf4c3a99d0fb9dca80d0fb705f24';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New employer registered' WHERE `templatefor` = 'employer-new' AND MD5(`subject`) = '5bb7ae163c7f025a5fe3b68c7b1b1866';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Employer', 'Welcome'), 'You are registered as', 'You have registered as'), ' in this application.', ' on {SITETITLE}.'), 'View Control Panel', 'Open your dashboard') WHERE `templatefor` = 'employer-new' AND MD5(`body`) = '5da6bed455416600eaa72c98e6a846ee';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New job seeker registered' WHERE `templatefor` = 'jobseeker-new' AND MD5(`subject`) = '8f9fef6eb73d96c0c9581f0ec5b8db75';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Jobseeker', 'Welcome'), 'You are registered as', 'You have registered as'), ' in this application.', ' on {SITETITLE}.'), 'View Control Panel', 'Open your dashboard') WHERE `templatefor` = 'jobseeker-new' AND MD5(`body`) = '2aaf6bea8ef71e2f49a73176e5828197';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New resume {RESUME_TITLE}' WHERE `templatefor` = 'resume-new-vis' AND MD5(`subject`) = '6c1827ff14bca98ab3c7611190325d22';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Visitor Resume', 'New resume'), 'We receive new resume', 'We received your resume') WHERE `templatefor` = 'resume-new-vis' AND MD5(`body`) = 'f221161f48bf9efb4ebf5f185fb5baf9';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: {JOBSEEKER_NAME} applied for {JOB_TITLE}' WHERE `templatefor` = 'jobapply-jobapply' AND MD5(`subject`) = 'c3061c049ef8484b7ae3cb071d762ff8';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job Apply Admin', 'New application'), 'Mr/Mrs ', ''), ' from Employer ', ' from employer ') WHERE `templatefor` = 'jobapply-jobapply' AND MD5(`body`) = '05102fc6f4963870fbdba1a6b9598290';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your resume {RESUME_TITLE} has been deleted' WHERE `templatefor` = 'resume-delete' AND MD5(`subject`) = 'fa6e7e4cd5fa4055662057151f977cda';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Delete Resume', 'Resume deleted') WHERE `templatefor` = 'resume-delete' AND MD5(`body`) = '5ab30e7f85dc8d25db9080584ceefd29';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Your application status has changed' WHERE `templatefor` = 'applied-resume_status' AND MD5(`subject`) = '483dd1f2c9935f62ee43d76f0488a179';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Applied Resume Status Change', 'Application status'), 'You are applied for job', 'You applied for the job'), 'Your resume has been mark as', 'Your application status is now') WHERE `templatefor` = 'applied-resume_status' AND MD5(`body`) = '80defa97351fb5e3d24c8f37982d154c';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New jobs for you' WHERE `templatefor` = 'job-alert' AND MD5(`subject`) = '02745dfb3966d0c3731b4d9041925ea1';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Job Alert', 'New jobs for you'), 'We receive new job.', 'New jobs matching your alert have been posted.'), 'Login and view detail at.', 'Log in to see the details.') WHERE `templatefor` = 'job-alert' AND MD5(`body`) = 'fc9315049d6b4e563d568bcf8ba26656';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: A friend sent you a job' WHERE `templatefor` = 'job-to-friend' AND MD5(`subject`) = 'c7ab4e39382633625a539e7d2a70cef1';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Tell To Friend', 'A job from a friend'), 'Dear,', 'Hello,'), 'Your Friend', 'Your friend'), ' will send you this mail through our site ', ' sent you this through our site '), ' to inform you for a job.', ' to tell you about a job.') WHERE `templatefor` = 'job-to-friend' AND MD5(`body`) = '1c62a63ea12e7baf5b94cff74a064036';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Package purchased' WHERE `templatefor` = 'package-purchase-admin' AND MD5(`subject`) = 'c53e595b6fe4d0920a061868ef8c334d';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Purchase Package Admin', 'Package purchased'), ' have purchased the package', ' has purchased the package') WHERE `templatefor` = 'package-purchase-admin' AND MD5(`body`) = '40123c230729dae563b8abc30cc51879';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Package status' WHERE `templatefor` = 'package-status' AND MD5(`subject`) = '6d0ee8584e75a6043201a8fa6f8da3d3';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(`body`, 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Purchase Status', 'Package status') WHERE `templatefor` = 'package-status' AND MD5(`body`) = '619acd1da8df46034f0236493bf350f1';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: Package purchased' WHERE `templatefor` = 'package-purchase' AND MD5(`subject`) = 'e6947ec513cffcc3dd79e25407cb0ecf';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(`body`, 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: Purchase Package', 'Package purchased') WHERE `templatefor` = 'package-purchase' AND MD5(`body`) = '66fde1a4c15a1fd5e736438549c4f800';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: New message' WHERE `templatefor` = 'new-message' AND MD5(`subject`) = 'c30292f6c0520da11f966d69c56ffd99';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`body`, '(<strong', '<strong'), '</strong>)', '</strong>'), 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: New Message', 'New message'), 'clicking the below button', 'clicking the button below') WHERE `templatefor` = 'new-message' AND MD5(`body`) = '3aec28f817d9dc2ee4b025c3bb67d838';
UPDATE `#__wj_portal_emailtemplates` SET `subject` = '{SITETITLE}: {SYSTEM_SUBJECT}' WHERE `templatefor` = 'system-email' AND MD5(`subject`) = 'd19b1d4fed31c8fa6d93b1a2e4e0b669';
UPDATE `#__wj_portal_emailtemplates` SET `body` = REPLACE(REPLACE(REPLACE(REPLACE(`body`, 'All rights reserved – Wp Job Portal WordPress Plugin', '{SITETITLE}'), 'Wp Job Portal System', '{SITETITLE}'), '>Wp Job Portal<', '>{SITETITLE}<'), '{SITETITLE}: {NOTIFICATION_HEADING}', '{NOTIFICATION_HEADING}') WHERE `templatefor` = 'system-email' AND MD5(`body`) = '8f548249f7c805bbbb4e5faa0fc2a2a5';

-- W-12: the applicant hears their application arrived, members hear about new
-- messages, and the admin (not the employer) hears about a package purchase.
-- Only rows still at the shipped values change.
UPDATE `#__wj_portal_emailtemplates_config` SET `jobseeker` = 1 WHERE `emailfor` = 'jobapply_jobapply' AND `admin` = 0 AND `employer` = 1 AND `jobseeker` = 0;
UPDATE `#__wj_portal_emailtemplates_config` SET `employer` = 1, `jobseeker` = 1 WHERE `emailfor` = 'new_message' AND `admin` = 0 AND `employer` = 0 AND `jobseeker` = 0;
UPDATE `#__wj_portal_emailtemplates_config` SET `admin` = 1, `employer` = 0 WHERE `emailfor` = 'package-purchase-admin' AND `admin` = 0 AND `employer` = 1;

-- ---------------------------------------------------------------------------
-- W-9: a job alert no longer needs a category.
UPDATE `#__wj_portal_fieldsordering` SET `required` = 0 WHERE `field` = 'categoryid' AND `fieldfor` = 6;

-- ---------------------------------------------------------------------------
-- U-2: the date fields' placeholder.
UPDATE `#__wj_portal_fieldsordering` SET `placeholder` = 'Enter date' WHERE `placeholder` = 'Enter Data';
-- U-2: the resume's salary is free text; ask for the currency and period with it
UPDATE `#__wj_portal_fieldsordering` SET `placeholder` = 'e.g. USD 3,500 per month' WHERE `field` = 'salaryfixed' AND `fieldfor` = 3 AND (`placeholder` IS NULL OR `placeholder` = '');

-- ---------------------------------------------------------------------------
-- W-15: deleting the plugin keeps the data unless the owner asks otherwise. The
-- character set and money column work runs after this update, in small steps
-- (WPJOBPORTAL_Table_Upgrade).
INSERT IGNORE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('delete_data_on_uninstall', '0', 'default');

-- ---------------------------------------------------------------------------
-- Last of all, the version (see 270.sql for why it comes last).

-- U-2: the Saved Searches page was titled "Job Save Searches"; a title the owner changed is kept
UPDATE `#__wj_portal_slug` SET `pagetitle` = 'Saved Searches [separator] [sitename]' WHERE `filename` = 'jobsavesearch' AND `pagetitle` = 'Job Save Searches [separator] [sitename]';
UPDATE `#__wj_portal_slug` SET `defaultpagetitle` = 'Saved Searches [separator] [sitename]' WHERE `filename` = 'jobsavesearch' AND `defaultpagetitle` = 'Job Save Searches [separator] [sitename]';

REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.8.0', 'default');

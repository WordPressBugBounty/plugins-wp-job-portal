REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.4.9', 'default');

CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_use_cases` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `code` varchar(255) NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` text,
              `category_code` varchar(255) DEFAULT NULL,
              `schema_data` json DEFAULT NULL,
              `status` tinyint(1) DEFAULT 1,
              `ordering` bigint DEFAULT NULL,
              UNIQUE KEY `code` (`code`),
              PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



ALTER TABLE `#__wj_portal_zywrap_wrappers`
          DROP FOREIGN KEY `fk_jp_zywrap_wrappers_cat`;

ALTER TABLE `#__wj_portal_zywrap_wrappers`
          CHANGE `category_code` `use_case_code` varchar(255) DEFAULT NULL;

ALTER TABLE `#__wj_portal_zywrap_wrappers`
          MODIFY `ordering` bigint DEFAULT NULL;
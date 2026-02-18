REPLACE INTO `#__wj_portal_config` (`configname`, `configvalue`, `configfor`) VALUES ('versioncode', '2.4.7', 'default');



CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `ordering` int(11) DEFAULT NULL,
    `status` tinyint(1) DEFAULT 1,
    UNIQUE KEY `code` (`code`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_languages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(10) NOT NULL,
    `name` varchar(255) NOT NULL,
    `ordering` int(11) DEFAULT NULL,
    `status` tinyint(1) DEFAULT 1,
    UNIQUE KEY `code` (`code`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_ai_models` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `provider_id` varchar(255) DEFAULT NULL,
    `ordering` int(11) DEFAULT NULL,
    `status` tinyint(1) DEFAULT 1,
    UNIQUE KEY `code` (`code`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_wrappers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `category_code` varchar(255) DEFAULT NULL,
    `featured` tinyint(1) DEFAULT NULL,
    `base` tinyint(1) DEFAULT NULL,
    `ordering` int(11) DEFAULT NULL,
    `status` tinyint(1) DEFAULT 1,
    UNIQUE KEY `code` (`code`),
    KEY `category_code` (`category_code`),
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_jp_zywrap_wrappers_cat`
        FOREIGN KEY (`category_code`)
        REFERENCES `#__wj_portal_zywrap_categories` (`code`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_block_templates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(50) NOT NULL,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `status` tinyint(1) DEFAULT 1,
    UNIQUE KEY `type_code` (`type`,`code`),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `#__wj_portal_zywrap_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `timestamp` datetime NOT NULL,
    `user_id` bigint(20) DEFAULT NULL,
    `status` varchar(50) NOT NULL,
    `action` varchar(100) NOT NULL,
    `wrapper_code` varchar(255) DEFAULT NULL,
    `model_code` varchar(255) DEFAULT NULL,
    `http_code` int(11) DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `prompt_tokens` int(11) DEFAULT NULL,
    `completion_tokens` int(11) DEFAULT NULL,
    `total_tokens` int(11) DEFAULT NULL,
    `token_data` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `action_status` (`action`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

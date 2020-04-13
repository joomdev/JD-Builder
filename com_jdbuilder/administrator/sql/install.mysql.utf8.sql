CREATE TABLE IF NOT EXISTS `#__jdbuilder_pages` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`asset_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL ,
`title` VARCHAR(255)  NOT NULL ,
`category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
`layout_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL ,
`access` INT(11)  NOT NULL ,
`language` VARCHAR(5)  NOT NULL ,
`params` MEDIUMTEXT NOT NULL ,
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdbuilder_configs` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`type` VARCHAR(255)  NOT NULL ,
`item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
`config` MEDIUMTEXT NOT NULL ,
`created_on` INT(11)  NOT NULL ,
`modified_on` INT(11)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;
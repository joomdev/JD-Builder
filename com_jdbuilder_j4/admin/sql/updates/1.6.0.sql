CREATE TABLE IF NOT EXISTS `#__jdbuilder_configs` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`type` VARCHAR(255)  NOT NULL ,
`item_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
`config` MEDIUMTEXT NOT NULL ,
`created_on` INT(11)  NOT NULL ,
`modified_on` INT(11)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci
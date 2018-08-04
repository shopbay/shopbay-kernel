DROP TABLE IF EXISTS `Image`;

CREATE TABLE `Image` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`parentId` int(10) unsigned NOT NULL,
	`parent` varchar(255) NOT NULL,
	`filename` varchar(255) NOT NULL,
	`extension` varchar(255) NOT NULL,
	`byteSize` int(10) unsigned NOT NULL,
	`mimeType` varchar(255) NOT NULL,
	`created` timestamp NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
);
ALTER TABLE `twitter` CHANGE `prefix` `prefix` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `twitter` CHANGE `postfix` `postfix` VARCHAR(50) NULL;
ALTER TABLE `twitter` CHANGE `songtypes` `songtypes` VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE `twitter` CHANGE `field_order` `field_order` VARCHAR(12) NULL DEFAULT NULL;
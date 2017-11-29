-- Add 'is_active' column into Pivot Cache table.
ALTER TABLE `civicrm_pivotreportcache` ADD `is_active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `expired_date`;

-- Update 'is_active' value to 1 for each currently existing Pivot Cache row.
UPDATE `civicrm_pivotreportcache` SET is_active = 1;

-- Drop existing unique indexes to allow having the same group name and path.
ALTER TABLE `civicrm_pivotreportcache` DROP INDEX `UI_group_path`;
ALTER TABLE `civicrm_pivotreportcache` DROP INDEX `UI_group_path_date`;

-- Add unique index to require unique group name, path and is_active values.
ALTER TABLE `civicrm_pivotreportcache` ADD UNIQUE INDEX `UI_group_path_active` (`group_name`, `path`, `is_active`);

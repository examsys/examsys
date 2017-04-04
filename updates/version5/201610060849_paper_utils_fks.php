<?php
/**
 * Add missing indexes and forign keys used by the PaperUtils class.
 */
if ($updater_utils->check_version("6.3.0") and !$updater_utils->has_updated('rogo1984_paper_utils_fk')) {
  // Add index to paperID in the paper_feedback table.
  $index1 = "ALTER TABLE `paper_feedback` "
      . "ADD INDEX `idx_paperID` (`paperID`)";
  $updater_utils->execute_query($index1, true);

  // Add forign key on paperID to the paper_feedback table.
  $fk1 = "ALTER TABLE `paper_feedback` "
      . "ADD CONSTRAINT `paper_feedback_fk1` FOREIGN KEY (`paperID`) REFERENCES `properties` (`property_id`)";
  $updater_utils->execute_query($fk1, true);

  // Do paper metadata must be linked to a paper.
  $metadatalinks = "ALTER TABLE `paper_metadata_security` "
      . "CHANGE COLUMN `paperID` `paperID` MEDIUMINT(8) UNSIGNED NOT NULL, "
      . "ADD INDEX `idx_paperID` (`paperID`)";
  $updater_utils->execute_query($metadatalinks, true);

  // Add forign key on paperID to the paper_metadata_security table.
  $fk2 = "ALTER TABLE `paper_metadata_security` "
      . "ADD CONSTRAINT `paper_metadata_security_fk1` FOREIGN KEY (`paperID`) REFERENCES `properties` (`property_id`)";
  $updater_utils->execute_query($fk2, true);

  $updater_utils->record_update('rogo1984_paper_utils_fk');
}

<?php
/**
 * Adds a foreign key to the metadataID field of the review_comments table.
 */
if ($updater_utils->check_version("6.4.0") and !$updater_utils->has_updated('review_comments_fk')) {
  $sql = "ALTER TABLE review_comments ADD CONSTRAINT `metadata_fk0` FOREIGN KEY (`metadataID`) REFERENCES `review_metadata` (`id`)";
  $updater_utils->execute_query($sql, true);
  $updater_utils->record_update('review_comments_fk');
}

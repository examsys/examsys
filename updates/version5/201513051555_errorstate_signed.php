<?php

// error state should be a signed integer - ROGO-1516
if (!$updater_utils->has_updated('errorstate_signed')) {

	$delete = $mysqli->prepare("ALTER TABLE log2 MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed');
}

?>


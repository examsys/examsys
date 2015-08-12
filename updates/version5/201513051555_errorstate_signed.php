<?php

// error state should be a signed integer - ROGO-1516
if (!$updater_utils->has_updated('errorstate_signed_log0')) {

	$delete = $mysqli->prepare("ALTER TABLE log0 MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log0');
}
if (!$updater_utils->has_updated('errorstate_signed_log0_deleted')) {

	$delete = $mysqli->prepare("ALTER TABLE log0_deleted MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log0_deleted');
}
if (!$updater_utils->has_updated('errorstate_signed_log1')) {

	$delete = $mysqli->prepare("ALTER TABLE log1 MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log1');
}
if (!$updater_utils->has_updated('errorstate_signed_log1_deleted')) {

	$delete = $mysqli->prepare("ALTER TABLE log1_deleted MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log1_deleted');
}
if (!$updater_utils->has_updated('errorstate_signed_log2')) {

	$delete = $mysqli->prepare("ALTER TABLE log2 MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log2');
}
if (!$updater_utils->has_updated('errorstate_signed_log3')) {

	$delete = $mysqli->prepare("ALTER TABLE log3 MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log3');
}
if (!$updater_utils->has_updated('errorstate_signed_log_late')) {

	$delete = $mysqli->prepare("ALTER TABLE log_late MODIFY errorstate tinyint(3) DEFAULT 0 NOT NULL");
	$delete->execute();
	$delete->close();

	$updater_utils->record_update('errorstate_signed_log_late');
}

?>


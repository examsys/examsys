<?php
/**
 * Updates any references in question leadins and scenarios to the new media directory.
 */
if (!$updater_utils->has_updated('update_media_location_2')) {
  // Regular expression to match the old media directory location.
  // it will match the directory ../../media/ and grab the filename.
  $regexp = '#src="..\/..\/media\/(.*?)""#';
  // The substitution will replace the old src tag with a new one that.
  $webroot = $configObject->get('cfg_root_path');
  // Ensure there is a trailing slash.
  if (substr($webroot, -1) !== '/') {
    $webroot .= '/';
  }
  $substitution = 'src="' . $webroot . 'getfile.php?type=media&amp;filename=$1"';
  // Get question leadins with images in them. We may need to update them.
  $result = $mysqli->prepare("SELECT q_id, leadin FROM questions WHERE leadin LIKE '%<img%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $leadin);
  while ($result->fetch()) {
    $newleadin = preg_replace($regexp, $substitution, $leadin);
    if ($newleadin != $leadin) {
      // There was a change, so update the record.
      $update = $mysqli->prepare("UPDATE questions SET leadin = ? WHERE q_id = ?");
      $update->bind_param('si', $newleadin, $id);
      $update->execute();
    }
  }

  // Get question scenarios with images in them. We may need to update them.
  $result = $mysqli->prepare("SELECT q_id, scenario FROM questions WHERE scenario LIKE '%<img%'");
  $result->execute();
  $result->store_result();
  $result->bind_result($id, $scenario);
  while ($result->fetch()) {
    $newscenario = preg_replace($regexp, $substitution, $scenario);
    if ($newscenario != $scenario) {
      $update = $mysqli->prepare("UPDATE questions SET scenario = ? WHERE q_id = ?");
      $update->bind_param('si', $newscenario, $id);
      $update->execute();
    }
  }
  
  $updater_utils->record_update('update_media_location_2');
}

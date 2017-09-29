<?php
  if (isset($low_bandwidth) and $low_bandwidth == 1) {
    // Lowbandwidth
    
    //enable output compression
    ob_start('ob_gzhandler');
    
    $top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
    $logo_html = '<td></td></tr>';
    $bottom_html = '<br /><table cellpadding="2" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  } else {
    // Full-fat
    $top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
    $themedirectory = rogo_directory::get_directory('theme');
    $logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_main'));
    $logo_html = '<td width="160" style="vertical_align:bottom"><img src="' . $logo_path . '" width="160" height="67" alt="Logo" /></td></tr>';
    $bottom_html = '<br /><table cellpadding="2" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
  }
  $properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
  if ($properties->get_fullscreen() == 1) {
    $leaving_rules = $string['msg2'];
  } else {
    $leaving_rules = $string['msg2short'];
  }
?>
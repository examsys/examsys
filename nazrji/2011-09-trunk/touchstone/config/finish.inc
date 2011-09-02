<?php
  if (isset($low_bandwidth) and $low_bandwidth == 1) {
    // Lowbandwidth
    
    //enable output compression
    ob_start('ob_gzhandler');
    
    $top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #164994; background-color:#2765AB">';
    $logo_html = '<td></td></tr>';
    $bottom_html = '<br /><table cellpadding="2" cellspacing="0" border="0" style="width:100%; border-top:1px solid #164994; background-color:#2765AB">';
  } else {
    // Full-fat
    $top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #164994; background-color:#2765AB; background-image:url(\'../artwork/title_gradient.png\'); background-repeat:repeat-y; background-position:center">';
    $logo_html = '<td width="160" style="vertical_align:bottom"><img src="../artwork/uni_logo.png" width="160" height="67" alt="Logo" /></td></tr>';
    $bottom_html = '<br /><table cellpadding="2" cellspacing="0" border="0" style="width:100%; border-top:1px solid #164994; background-color:#2765AB; background-image:url(\'../artwork/title_gradient.png\'); background-repeat:repeat-y; background-position:center">';
  }
  $leaving_rules = '<strong>University Rules</strong><br />1) no leaving in first hour, 2) no leaving in last 15 minutes.<br /><br />If you comply with the first two rules and there is only one sitting of your exam then you may click \'Close Window\' and then press &lt;CTRL&gt; &lt;ALT&gt; and &lt;DELETE&gt; to log out of your workstation.';
?>
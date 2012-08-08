<?php
if (isset($low_bandwidth) and $low_bandwidth == 1) {
  // Lowbandwidth
  ob_start('ob_gzhandler');   // enable compression
  
  $top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%;border-bottom:1px solid #164994;background-color:#3D7ABC">';
  $logo_html = '<td width="160"></td></tr></table>';
  $bottom_html = '<table cellpadding="2" cellspacing="0" border="0" style="width:100%;border-top:1px solid #164994; background-color:#3D7ABC">';
  $bottom_html .= '<tr><td style="width:30px"><img src="../artwork/fire_exit.png" width="26" height="25" alt="' . $string['fireexit'] . '" style="cursor:pointer" onclick="fire()" /></td><td style="color:white;font-size:80%;width:250px">&nbsp;&#169; 2012, The University of Nottingham</td><td style="color:white;width:75px;text-align:center">';
} else {
  // Full-fat
  $top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%;border-bottom:1px solid #164994;background-color:#2765AB;background-image:url(\'../artwork/title_gradient.png\');background-repeat:repeat-y;background-position:center">';
  $logo_html = '<td width="160"><img src="../config/logo.png" width="160" height="67" alt="Logo" /></td></tr></table>';
  $bottom_html = '<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-top:1px solid #164994;background-color:#2765AB;background-image:url(\'../artwork/title_gradient.png\');background-repeat:repeat-y;background-position:center">';
  $bottom_html .= '<tr><td style="width:30px"><img src="../artwork/fire_exit.png" width="26" height="25" alt="' . $string['fireexit'] . '" style="cursor:pointer" onclick="fire()" /></td><td style="color:white;font-size:80%;width:250px">&nbsp;&#169; 2012, The University of Nottingham</td><td style="color:white;width:75px;text-align:center">';
}
?>
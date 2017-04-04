<?php
if (isset($low_bandwidth) and $low_bandwidth == 1) {
  // Lowbandwidth
  ob_start('ob_gzhandler');   // enable compression
}
$top_table_html = '<table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#5590CF">';
$logo_html = '<td width="160"><img src="../config/logo.png" width="160" height="67" alt="Logo" /></td></tr></table>';
$bottom_html = '<table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#5590CF; min-height:29px">';
if ($original_paper_type == '2') {
  $fire = '<td id="fire_exit"></td>';
} else { 
  $fire = '';
}
$bottom_html .= '<tr>' . $fire . '<td class="copyright">&#169; 2017, e-Assessment University</td><td style="width:14em;text-align:center">';

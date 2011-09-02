<?php
  require '../touchstone/include/staff_student_auth.inc';
  $compressed = ob_start("ob_gzhandler");
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="apple-touch-icon-precomposed" href="./mobile_logo2.png"/>
<link rel="apple-touch-startup-image" href="./splash.png" />
<title>TouchStone Mobile</title>
<style>
body {font-family:Arial,sans-serif; background-color:white; color:black; margin:0px}
p, table {margin-left:3%; margin-right:3%; border-collapse:collapse; width:94%}
td.o {border:1px solid #C0C0C0; padding-top:2px; padding-bottom:2px; padding-left:4px; padding-right:4px}
td.l {width:20px; border:1px solid #C0C0C0; padding-top:2px; padding-bottom:2px; padding-left:4px; padding-right:4px}
.tc {width:20px}
</style>
<script language="JavaScript">
  tick = new Image(17,16); 
  tick.src = "../touchstone/artwork/tick.gif";
  cross = new Image(17,16); 
  cross.src = "../touchstone/artwork/cross.gif";
  
  function chk(mk, qID) {
    if (mk == 0) {
      document.getElementById(qID).innerHTML = '<img src="../touchstone/artwork/cross.gif" width="17" height="16" alt="Cross" />';
    } else {
      document.getElementById(qID).innerHTML = '<img src="../touchstone/artwork/tick.gif" width="17" height="16" alt="Tick" />';
    }
  }
</script>
</head>

<body onload="setTimeout(function() { window.scrollTo(0, 1) }, 100);">

<table style="width:100%; margin:0px; font-size:140%; font-weight:bold; background-color:#316EB3; color:white; border-bottom:2px solid #164994">
<tr><td style="padding-left:3%">Quiz</td></tr>
</table>

<p>What is the capital of England?</p>
<table>
<tr onclick="chk(0,'a1_1')"><td id="a1_1" class="tc"></td><td class="l">a.</td><td class="o">Edinburgh</td></tr>
<tr onclick="chk(1,'a1_2')"><td id="a1_2" class="tc"></td><td class="l">b.</td><td class="o">London</td></tr>
<tr onclick="chk(0,'a1_3')"><td id="a1_3" class="tc"></td><td class="l">c.</td><td class="o">Birmingham</td></tr>
<tr onclick="chk(0,'a1_4')"><td id="a1_4" class="tc"></td><td class="l">d.</td><td class="o">Glasgow</td></tr>
<tr onclick="chk(0,'a1_5')"><td id="a1_5" class="tc"></td><td class="l">e.</td><td class="o">Sheffield</td></tr>
</table>

<p>What are the top <strong>two</strong> fastest forms of transport?</p>
<table>
<tr onclick="chk(0,'a2_1')"><td id="a2_1" class="tc"></td><td class="l">a.</td><td class="o">Hovercraft</td></tr>
<tr onclick="chk(1,'a2_2')"><td id="a2_2" class="tc"></td><td class="l">b.</td><td class="o">Plane</td></tr>
<tr onclick="chk(0,'a2_3')"><td id="a2_3" class="tc"></td><td class="l">c.</td><td class="o">Bus</td></tr>
<tr onclick="chk(0,'a2_4')"><td id="a2_4" class="tc"></td><td class="l">d.</td><td class="o">Car</td></tr>
<tr onclick="chk(1,'a2_5')"><td id="a2_5" class="tc"></td><td class="l">e.</td><td class="o">Train</td></tr>
</table>
<br />
</body>
</html>
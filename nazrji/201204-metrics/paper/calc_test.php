<?php
  require '../include/staff_auth.inc';
?>
<html>
<head>
<title>Calculator Test</title>
<script language="JavaScript">
function openCalc() {
  calc=window.open("../tools/sCal-8-9/sCal-09.php?calc=1&form=undefined&field=undefined", "win_ch", "width=230,height=385,help=no,status=no,scrollbars=no,resizable=no,toolbar=no,location=no,scrollbars=no,directories=no,status=no,menubar=no,resizable=no,location=no,directories=no,status=no,menubar=no,top=10,left=10,dependent=yes,alwaysRaised=yes", true);
  if (window.focus) {
    calc.focus();
  }
}
function openOldCalc() {
  oldcalc=window.open("../tools/calc98/jcalc98.php","calculator","width=250,height=391,top=10,left=10scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=no,menubar=no");
  if (window.focus) {
    oldcalc.focus();
  }
}
</script>
</head>

<body style="font-family:Arial,sans-serif">

<div><a href="#" onclick="openOldCalc();">Old Calculator</a></div>
<br />
<div><a href="#" onclick="openCalc();">New Calculator</a></div>

</body>
</html>
<?php
$root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root . 'config/config.inc.php';
require_once $root . 'classes/lang.class.php';

echo "<html>
<head>
<title>" . $string['preview'] . "</title>
</head>
<body style='background-color:white; color:#808080; font-family:Arial,sans-serif; font-size:100%'>

<p>" . $string['previewmsg'] . "</p>

</body>
</html>";
?>
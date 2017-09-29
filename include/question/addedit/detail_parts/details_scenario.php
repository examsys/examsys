<?php
require 'details_editor.php';
if (in_array($cfg_editor_name, $configObject->get_setting('core', 'paper_editor_supports_mathjax')) and $configObject->get_setting('core', 'paper_mathjax')) {
  echo "<tr>";
  echo "<th>" . $string['previewmathjax'] . "</th>";
  echo "<td><div id=\"MathPreviewscenario\" style=\"border:1px solid; padding: 3px; width:50%; margin-top:5px\"></div><div id=\"MathBufferscenario\" style=\"border:1px solid; padding: 3px; width:50%; margin-top:5px; visibility:hidden; position:absolute; top:0; left: 0\"></div></td>";
  echo "<script>Previewscenario.Init();</script>";
  echo "</tr>";
}
?>
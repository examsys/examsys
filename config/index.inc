<?php
  $themedirectory = rogo_directory::get_directory('theme');
  $logo_path = $themedirectory->url($configObject->get_setting('core', 'misc_logo_main'));
  $logo_html = '<img src="' . $logo_path . '" width="167" height="70" alt="Logo" border="0" />';
?>
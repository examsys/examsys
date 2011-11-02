<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Utility class for language related functionality
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/


Class LangUtils {

  static function getLang($web_root) {
    $langs = explode(',',strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
    $language = '';
    $i = 0;

    while ($i < count($langs) and $language == '') {
      $parts = explode(';',$langs[$i]);
      $test_lang = $parts[0];
      if (file_exists($web_root . "/lang/" . substr($test_lang,0,5) . "/")) {
        $language = substr($test_lang,0,5);
      } elseif (file_exists($web_root . "/lang/" . substr($test_lang,0,2) . "/")) {
        $language = substr($test_lang,0,2);
      }
      $i++;
    }
    
    if ($language == '') $language = 'en';   // Default to English if no languages found
    
    return $language;
  }
}

$language = LangUtils::getLang($cfg_web_root);

if (file_exists("$cfg_web_root/lang/$language" . $_SERVER['PHP_SELF'])) {
  require $cfg_web_root . "lang/$language" . $_SERVER['PHP_SELF'];
}
?>
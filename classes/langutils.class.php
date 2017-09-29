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
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */


Class LangUtils {

  static function getLang($web_root) {
    $language = '';

    if (isset($_SESSION['ROGO_language'])) {
      $langs[] = $_SESSION['ROGO_language'];
    } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {  
      // Check this is set as some webservices do not have this data.
      $langs = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
    }
    
    if (isset($langs) and is_array($langs)) {
      $i = 0;
      // Use first supported language found.
      while ($i < count($langs) and $language == '') {
        $parts = explode(';', $langs[$i]);
        $test_lang = $parts[0];
        $lang = substr($test_lang, 0, 2);
        if (LangUtils::supportedLang($lang)) {
          $language = $lang;
        }
        $i++;
      }
    }
    // Default to English language not supplied.
    if ($language == '') {
      $language = 'en';
    }
    return $language;
  }

  static function loadlangfile ($file, $str = null) {
    // The language file may also contain a jstring variable. We should load this as well.
    global $jstring;
    if (is_null($str)) {
      global $string;
    } else {
      $string = $str;
    }
    $configObject = Config::get_instance();
    $cfg_web_root = $configObject->get('cfg_web_root');
    $language = LangUtils::getLang($cfg_web_root);
    $lang_path = "{$cfg_web_root}lang/$language/" . $file;
    if (file_exists($lang_path)) {
      require $lang_path;
    }
    return $string;
  }
  
  /**
   * Check if language is supported
   * @param string $lang lang code
   * @return boolean
   */
  static function supportedLang($lang) {
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'languages.xml';
    if (file_exists($file)) {
      $xmldata = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
      $languages = $xmldata->languages; 
      foreach ($languages->lang as $supported) {
        if ($lang === (string) $supported) {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Check if language pack is installed
   * @param string $lang lang code
   * @return boolean
   */
  static function langPackInstalled($lang) {
    $configObject = Config::get_instance();
    $web_root = $configObject->get('cfg_web_root');
    if (file_exists($web_root . "/lang/" . $lang . "/")) {
      return true;
    }
    return false;
  }
}
?>

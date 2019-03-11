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
 * A class for getting the values from GET, POST and REQUEST and ensuring that they are correctly sanatised.
 * 
 * Direct use of GET, POST and REQUEST in new code is strictly forbidden.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package core
 */
class param {
  /** A string consiting only of letters. */
  const ALPHA = 1;

  /** A string consiting only of letters and numbers. */
  const ALPHANUM = 2;
  
  /**
   * A boolean value, "1", "true", "on" and "yes" are treated as true,
   * "0", "false", "off", "no", and "", and null are treated as false.
   * All other values are invalid.
   */
  const BOOLEAN = 3;
  
  /** An e-mail address. */
  const EMAIL = 4;
  
  /** Floating point number, i.e. 2.5 */
  const FLOAT = 5;
  
  /** HTML. */
  const HTML = 6;
  
  /** An octal, decimal or hexidecimal integer, i.e. 017, 14, 0xFF. */
  const INT = 7;
  
  /** A IPv4 or IPv6 address. */
  const IP_ADDRESS = 8;
  
  /** A url for the current Rogo site. */
  const LOCAL_URL = 6;
  
  /** Any input is valid. */
  const RAW = 10;
  
  /** Plain text. HTML will be stripped. */
  const TEXT = 11;
  
  /** A RFC-2396 URL. */
  const URL = 12;
  
  /** A regular expression. */
  const REGEXP = 13;

  /** A special datatime format, yyyymmddhhmmss. */
  const SQLDATETIME = 14;

  /** A safe file name */
  const FILENAME = 15;

  /** Find the named variable in the Get array. */
  const FETCH_GET = '_GET';
  
  /** Find the named variable in the Post array. */
  const FETCH_POST = '_POST';
  
  /** Find the named variable in the Request array. */
  const FETCH_REQUEST = '_REQUEST';
  
  /**
   * Ensures that the value is of the correct type.
   * 
   * @param mixed $value The value to clean
   * @param int $type The type of value the value should be.
   * @param array $opt Cleaning options.
   * @return mixed The cleaned string or null if it does not match the type defined.
   */
  public static function clean($value, $type, $opt = array('default' => null)) {
    // Setup the parameters for the filter_var function.
    switch ($type) {
      case self::ALPHA:
        $filter = FILTER_SANITIZE_STRING;
        $options = array(
          'options' => $opt,
          'flags' => FILTER_FLAG_NO_ENCODE_QUOTES,
        );
        break;
      case self::ALPHANUM:
        $filter = FILTER_SANITIZE_STRING;
        $options = array(
          'options' => $opt,
          'flags' => FILTER_FLAG_NO_ENCODE_QUOTES,
        );
        break;
      case self::BOOLEAN:
        $filter = FILTER_VALIDATE_BOOLEAN;
        $options = array(
          'options' => $opt,
          'flags' => FILTER_NULL_ON_FAILURE,
        );
        break;
      case self::EMAIL:
        $filter = FILTER_VALIDATE_EMAIL;
        $options = array(
          'options' => $opt,
        );
        break;
      case self::FLOAT:
        $filter = FILTER_VALIDATE_FLOAT;
        $options = array(
          'options' => $opt,
        );
        break;
      case self::HTML:
        $filter = FILTER_UNSAFE_RAW;
        $options = array(
          'options' => $opt,
        );
        break;
      case self::INT:
        $filter = FILTER_VALIDATE_INT;
        $options = array(
          'options' => $opt,
          'flags' => FILTER_FLAG_ALLOW_OCTAL | FILTER_FLAG_ALLOW_HEX,
        );
        break;
      case self::IP_ADDRESS:
        $filter = FILTER_VALIDATE_IP;
        $options = array(
          'options' => $opt,
          'flags' => FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6,
        );
        break;
      case self::RAW:
      case self::TEXT:
      case self::FILENAME:
        $filter = FILTER_UNSAFE_RAW;
        $options = array(
          'options' => $opt,
        );
        break;
      case self::URL:
      case self::LOCAL_URL:
        $filter = FILTER_VALIDATE_URL;
        $options = array(
          'options' => $opt,
        );
        break;
      case self::REGEXP:
        $filter = FILTER_VALIDATE_REGEXP;
         $options = array(
          'options' => $opt,
        );
        break;
      case self::SQLDATETIME:
        $filter = FILTER_VALIDATE_REGEXP;
        $options = array( 'options' =>array('regexp' => '/^([12]\d{3}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])([01][0-9]|2[0-3])[0-5]\d[0-5]\d)$/',));
        break;
      default:
        throw new coding_exception('invalid_type');
        break;
    }
    // Filter the input.
    $return = filter_var($value, $filter, $options);

    // Do any additional cleaning that may be needed.
    switch ($type) {
      case self::ALPHA:
        $cleaned = preg_replace('#[^\p{L}\p{M}\p{Zs}]#u', '', $return);
        if ($cleaned === '' and $cleaned !== $return) {
          $return = null;
        } else {
          $return = $cleaned;
        }
        break;
      case self::ALPHANUM:
        $cleaned = preg_replace('#[^\p{L}\p{M}\p{Zs}0-9]#u', '', $return);
        if ($cleaned === '' and $cleaned !== $return) {
          $return = null;
        } else {
          $return = $cleaned;
        }
        break;
      case self::HTML:
        $return = self::purify_html($return);
        break;
      case self::TEXT:
        $return = self::strip_tags($return);
        break;
      case self::FILENAME:
        // Remove anything which isn't a word, number or fullstop.
        $cleaned = preg_replace('#[^\w\d\.]#u', '', $return);
        if ($cleaned === '' and $cleaned !== $return) {
          $return = null;
        } else {
          $return = $cleaned;
        }
        break;
      case self::LOCAL_URL:
        $rogo_url = Config::get_instance()->get('cfg_web_host');
        // We now know if it is a valid ULR ot not, we just need to ensure it is for the local instance of Rogo.
        $filter = FILTER_VALIDATE_REGEXP;
        $options = array(
          'options' => array(
            'default' => null,
            'regexp' => "#^https?://$rogo_url(/.*)?$#",
          ),
        );
        $return = filter_var($return, $filter, $options);
        break;
    }

    return $return;
  }

  /**
   * Recursively ensures that all the values in an array are of the specified type.
   *
   * @param array $value The value to clean
   * @param int $type The type of value the value should be.
   * @param bool $required When true throw an exception if the result is filtered to be an empty string or null.
   * @param array $opt Cleaning options.
   * @return array The array containing only cleaned values or null if it does not match the type defined.
   */
  public static function clean_array(array $value, $type, $required = false, $opt = array('default' => null)) {
    $return = array();
    foreach ($value as $key => $part) {
      if (!is_array($part)) {
        $clean = self::clean($part, $type, $opt);
        if ($required and (is_null($clean) or $clean === '')) {
          // Nothing valid passed, throw an exception.
          throw new MissingParameter();
        }
        $return[$key] = $clean;
      } else {
        $return[$key] = self::clean_array($part, $type, false, $opt);
      }
    }
    return $return;
  }

  /**
   * A wrapper for php's string_tags function.
   *
   * It is here to smooth over any edge cases.
   *
   * @param string $text
   * @return string
   */
  protected static function strip_tags($text) {
    if ($text === '' or preg_match('#<.*>#', $text) === 0) {
      // No html.
      return $text;
    }
    $postfix = '';
    if (substr($text, -1) === '<') {
      // strip_tags will remove a less than if it is the final character. We wish to leave it in.
      $postfix = '<';
    }
    $return = strip_tags($text);
    return $return . $postfix;
  }

  /**
   * Strips out unsafe html tags.
   *
   * @param string $html
   * @return string
   */
  protected static function purify_html($html) {
    // We use the html purifier library for this (http://htmlpurifier.org/)
    // First we setup the purifier.
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    // Then we clean the text and return it.
    return $purifier->purify($html);
  }

  /**
   * Gets the named parameter, returns the default value if it is not present or invalid.
   * 
   * @param string $name The name of the parameter to retrieve.
   * @param mixed $default The default value for the parameter.
   * @param int $type The type of value the parameter should contain.
   * @param string $from Should be param::FETCH_REQUEST (default), param::FETCH_GET or param::FETCH_POST
   * @param array $opt Cleaning options.
   * @return mixed
   */
  public static function optional($name, $default, $type, $from = self::FETCH_REQUEST, $opt = array('default' => null)) {
    $value = self::fetch($name, $from);
    if (is_array($value)) {
      $clean = self::clean_array($value, $type, false, $opt);
    } else {
      $clean = self::clean($value, $type, $opt);
    }
    if (is_null($clean) or $clean === '') {
      $clean = $default;
    }
    return $clean;
  }
  
  /**
   * Gets the named parameter, if it is invalid or does not exisit an error is generated.
   * 
   * @param string $name The name of the parameter to retrieve.
   * @param int $type The type of value the parameter should contain.
   * @param string $from Should be param::FETCH_REQUEST (default), param::FETCH_GET or param::FETCH_POST
   * @param array $opt Cleaning options.
   * @return mixed
   * @throws MissingParameter
   */
  public static function required($name, $type, $from = self::FETCH_REQUEST, $opt = array('default' => null)) {
    $value = self::fetch($name, $from);
    if (is_array($value)) {
      $clean = self::clean_array($value, $type, true, $opt);
    } else {
      $clean = self::clean($value, $type, $opt);
    }
    if (is_null($clean) or $clean === '') {
      // Nothing valid passed, throw an exception.
      throw new MissingParameter();
    }
    return $clean;
  }

  /**
   * Gets the named parameter.
   *
   * @param string $name The name of the parameter to retrieve.
   * @param string $from Should be param::FETCH_REQUEST (default), param::FETCH_GET or param::FETCH_POST
   * @return mixed
   */
  protected static function fetch($name, $from = self::FETCH_REQUEST) {
    if ($from === self::FETCH_GET and isset($_GET[$name])) {
      $return = $_GET[$name];
    } else if ($from === self::FETCH_POST and isset($_POST[$name])) {
      $return = $_POST[$name];
    } else if ($from === self::FETCH_REQUEST and isset($_REQUEST[$name])) {
      $return = $_REQUEST[$name];
    } else {
      $return = null;
    }
    return $return;
  }
}

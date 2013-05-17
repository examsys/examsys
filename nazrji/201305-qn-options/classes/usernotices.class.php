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
* A Class to hold functions designed to display notices to users. Including  
* access denied messages
*
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'classes/rogostaticsingleton.class.php';
require_once $cfg_web_root . 'classes/logger.class.php';
require_once $cfg_web_root . 'classes/userobject.class.php';

Class UserNotices extends RogoStaticSingleton {
  public static $inst = NULL;
  public static $class_name = 'user_notices';

  /**
  * constructor
  */
  private function __construct() {}
}

Class user_notices extends RogoStaticSingleton {

  /**
  * constructor
  */
  public function __construct() {}

  /**
   * This function will output a message to the user 
   *
   * @param string $title       - title to display
   * @param string $msg         - string the message
   * @param string $icon        - name of the icon image file
   * @param string $title_color - color of the tile text
   *
   */
  public function display_notice($title, $msg, $icon, $title_color = 'black', $output_header = true, $output_footer = true) {
    $configObject = Config::get_instance();
    $rp = $configObject->get('cfg_root_path');
    $cs = $configObject->get('cfg_page_charset');
    
    if ($output_header == true) {
      echo "<html>\n";
      echo "<head>\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
      echo "<meta http-equiv=\"content-type\" content=\"text/html;charset={$cs}\" />\n";
      echo "<title>$title</title>\n";
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$rp}/css/body.css\" />\n";
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$rp}/css/notice.css\" />\n";
      echo "</head>\n<body>\n";
    }
    echo '<div class="notice">';
    echo "<div style=\"float:left; padding-left:10px;width:60px\"><img src=\"$rp" . $icon . "\" width=\"48\" height=\"48\" /></div>\n";
    echo "<div><h1 style=\"color:$title_color\">$title</h1>\n";
    echo "<hr />\n<p>$msg</p></div>";
    echo '</div>';

    if ($output_footer == true) {
      echo "\n</body>\n</html>";
    }
  }
  
  /**
   * This function will output a message to the user and exit php; 
   *
   * @param string $title       - string title to display
   * @param string $msg         - string the message displayed on screen
   * @param string $reason      - string the message displayed in the database
   * @param string $icon        - name of the icon image file
   * @param string $title_color - color of the tile text
   *
   */
  public function display_notice_and_exit($mysqli, $title, $msg, $reason, $icon, $title_color = 'black', $output_header = true, $output_footer = true) {
    $user = UserObject::get_instance();
    if ($user !== NULL and $user->get_user_ID() > 0) {
      $logger = new Logger($mysqli);
      $logger->record_access_denied($user->get_user_ID(), $title, $reason);  // Record attempt in access denied log against userID.
    } else {
      $logger = new Logger($mysqli);
      $logger->record_access_denied(0, $title, $reason);                     // Record attempt in access denied log, userID set to zero.
    }

    $this->display_notice($title, $msg, $icon, $title_color, $output_header, $output_footer);
    exit;
  }
  
  /**
   * This function will exit php without notice; 
   *
   * @param string $title       - string title to display
   * @param string $msg         - string the message
   * @param string $icon        - name of the icon image file
   * @param string $title_color - color of the tile text
   *
   */
  public function exit_php() {
    exit;
  }
  /**
   * This function will output an access denied warning and terminate script 
   * execution
   *
   * @param string $message       - message to display
   * @param string $output_header - if true output 401 headers
   *
   */
  public function access_denied($db, $string, $message, $output_header = false, $output_footer = true) {
    $this->display_notice_and_exit($db, $string['accessdenied'], $message, $string['accessdenied'], '/artwork/access_denied.png', '#C00000', $output_header, $output_footer);
  }

}

?>
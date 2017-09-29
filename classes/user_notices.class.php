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
* access denied messages.
*
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
Class user_notices extends RogoStaticSingleton {

  /**
  * constructor
  */
  public function __construct() {}

  /**
   * Displays a wide information bar with a yellow bar at the top.
	 * Used to display to the user that no questions were found in a
	 * search, for example.
   *
   * @param string $msg    - The message to convey.
   * @param int $font-size - An optional font size to use for the message.
	 * @return string - HTML to be echoed to the screen.
	 *
   */
	public function info_strip($msg, $font_size = 85) {
    $configObject = Config::get_instance();

		$html = '<div class="info_bar" style="font-size:' . $font_size . '%">';
		$html .= '<div class="info_bar_yellow"></div>';
		$html .= '<img src="' . $configObject->get('cfg_root_path') . '/artwork/info_icon.gif" alt="i" />' . $msg;
		$html .= '</div>';

		return $html;
	}

  /**
   * This function will output a message to the user
   *
   * @param string $title       - title to display
   * @param string $msg         - string the message
   * @param string $icon        - name of the icon image file
   * @param string $title_color - color of the tile text
   * @param bool $output_header - if true output opening HTML tags
   * @param bool $output_footer - if true output closing HTML tags
   */
  public function display_notice($title, $msg, $icon, $title_color = 'black', $output_header = true, $output_footer = true) {
    $configObject = Config::get_instance();
    $root = str_replace('/classes', '/', str_replace('\\', '/', dirname(__FILE__)));

    if (file_exists($root . 'config/config.inc.php')) {
      $rp = $configObject->get('cfg_root_path');
      $cs = $configObject->get('cfg_page_charset');
    } else {          // If we have not installed there is no config.inc.php file.
      $rp = rtrim('/' . trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $root), '/'), '/');
      $cs = 'utf-8';
    }

    if ($output_header == true) {
      echo "<html>\n";
      echo "<head>\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
      echo "<meta http-equiv=\"content-type\" content=\"text/html;charset={$cs}\" />\n";
      echo "<title>$title</title>\n";
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$rp}/css/body.css\" />\n";
      echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$rp}/css/notice.css\" />\n";
      echo "<script type=\"text/javascript\" src=\"{$rp}/js/start.js\"></script>";
      echo "</head>\n<body>\n";
    }
    echo '<div class="notice">';
    echo "<div style=\"float:left; padding-left:10px;width:60px\"><img src=\"$rp" . $icon . "\" width=\"48\" height=\"48\" /></div>\n";
    echo "<div><h1 style=\"color:$title_color\">$title</h1>\n";
    echo "<hr style=\"width:300px\"/>\n<p>$msg</p></div>";
    echo '</div>';

    if ($output_footer == true) {
      echo "\n</body>\n</html>";
    }
  }

  /**
   * This function will output a message to the user and exit php;
   *
   * @param mysqli|null $mysqli - gloable database object for add log data into database, if null, exception message will only be displayed then exit 
   * @param string $title       - string title to display
   * @param string $msg         - string the message displayed on screen
   * @param string $reason      - string the message displayed in the database
   * @param string $icon        - name of the icon image file
   * @param string $title_color - color of the tile text
   * @param bool $output_header - if true output opening HTML tags
   * @param bool $output_footer - if true output closing HTML tags
   *
   */
  public function display_notice_and_exit($mysqli, $title, $msg, $reason, $icon, $title_color = 'black', $output_header = true, $output_footer = true) {
    if (!is_null($mysqli)) {
      $userObj = UserObject::get_instance();  
      if ($userObj !== null and $userObj->get_user_ID() > 0) {
        $logger = new Logger($mysqli);
        $logger->record_access_denied($userObj->get_user_ID(), $title, $reason); // Record attempt in access denied log against userID.
      } else {
        $logger = new Logger($mysqli);
        $logger->record_access_denied(0, $title, $reason); // Record attempt in access denied log, userID set to zero.
      }
    }
    $this->display_notice($title, $msg, $icon, $title_color, $output_header, $output_footer);
    exit;
  }

  /**
   * This function will exit php without notice.
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

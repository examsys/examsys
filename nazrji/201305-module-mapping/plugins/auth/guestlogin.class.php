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
 * Handles Guest account access in rogo
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require_once 'outline_authentication.class.php';


class guestlogin_auth extends outline_authentication {

  public $impliments_api_auth_version = 1;
  public $version = 0.9;

  function register_callback_routines() {

    $callbackarray[] = array(array($this, 'loginbutton'), 'displaystdform', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'gotoguestaccount'), 'preauth', $this->number, $this->name);
    $callbackarray[] = array(array($this, 'errordisp'), 'displayerrform', $this->number, $this->name);

    return $callbackarray;
  }

  function gotoguestaccount($preauthobj) {
    if (isset($this->request['guestlogin'])) {
      header("Location: guest_account.php");
      exit();

    }

    return $preauthobj;

  }

  function errordisp($displayerrformobj) {
    global $string;
    if ($_SERVER['PHP_SELF'] == '/index.php') {
      $this->savetodebug('adding temp account notice to error screen');
      $message2 = $string['ifstuckinvigilator'] . " <a href=\"guest_account.php\" style=\"color:blue\"><strong>" . $string['tempaccount'] . "</strong></a>";
      $displayerrformobj->li[] = $message2;
    }

    return $displayerrformobj;
  }

  function loginbutton($displaystdformobj) {
    global $string;
    
    $this->savetodebug('Button Check');
    $labs_list = '';
    // detect if we should display login button
    $paper_match = false;
    $ip_match = false;
    $query = "SELECT labs FROM properties WHERE start_date < DATE_ADD(NOW(), interval 15 minute) AND end_date > NOW() AND paper_type IN ('1','2') AND labs != ''";
    $results = $this->db->prepare($query);
    if ($this->db->error) {
      try {
        $e = $this->db->error;
        $en = $this->db->errno;
        throw new Exception("MySQL error $e <br> Query:<br> $query", $en);
      } catch (Exception $e) {
        echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        echo nl2br($e->getTraceAsString());
      }
    }
    $results->execute();
    $results->store_result();
    $results->bind_result($labs);
    while ($results->fetch()) {
      $paper_match = true;
      $query = "SELECT address FROM ip_addresses WHERE lab IN ($labs)";
      $sub_results = $this->db->prepare($query);
      if ($this->db->error) {
        try {
          $e = $this->db->error;
          $en = $this->db->errno;
          throw new Exception("MySQL error $e <br> Query:<br> $query", $en);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
          echo nl2br($e->getTraceAsString());
        }
      }
      $sub_results->execute();
      $sub_results->store_result();
      $sub_results->bind_result($address);
      while ($sub_results->fetch()) {
        $labs_list = $labs_list . ' ' . $address;
        if (NetworkUtils::get_ipaddress() == $address) $ip_match = true;
      }
      $sub_results->close();
    }
    $results->close();

    $this->savetodebug('Status paper_match:' . var_export($paper_match, true) . ' ip_match:' . var_export($ip_match, true) . ' ip address:' . var_export(NetworkUtils::get_ipaddress(), true) . ' <br /> ' . $labs . ' ' . $labs_list);
    if ($paper_match === true and $ip_match === true) { //($displaybutton === true) {
      $this->savetodebug('Adding New Button');
      $newbutton = new displaystdformobjbutton();
      $newbutton->type = 'submit';
      $newbutton->value = ' ' . $string['guestbutton'] . ' ';
      $newbutton->name = 'guestlogin';
      $displaystdformobj->buttons[] = $newbutton;
    }

    return $displaystdformobj;
  }

}

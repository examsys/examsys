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
 * Creates a new user (staff or student).
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require '../include/admin_auth.inc';
require '../include/sort.inc';
require_once '../classes/lookup.class.php';


if(isset($_REQUEST['LOOKUP'])) {
  if(isset( $_SESSION['ldaplookupdata'][$_REQUEST['LOOKUP']])) {
  //  var_dump( $_SESSION['ldaplookupdata'][$_REQUEST['LOOKUP']]);
    $lookup = Lookup::get_instance($configObject, $mysqli);
    $data = new stdClass();
    $data->lookupdata = $_SESSION['ldaplookupdata'][$_REQUEST['LOOKUP']];


 //   $data->settings = new stdClass();
//  $data->settings->recursive = true;

    $output = $lookup->userlookup($data);

    var_dump($output);
    ?>

  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>LDAP Lookup</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/screen.css"/>
    <style type="text/css">
        body {
            background-color: #F1F5FB;
            font-size: 90%
        }
    </style>
    <script type="text/javascript">
        function setSelectedIndex(s, v) {
            for (var i = 0; i < s.options.length; i++) {
                if (s.options[i].value.toLowerCase() == v.toLowerCase()) {
                    s.options[i].selected = true;
                    return;
                }
            }
        }

        function writeDetails(user_title, first_names, surname, username, email,yearofstudy,gender,course,studentid) {
            window.opener.document.getElementById('new_surname').value = surname;
            window.opener.document.getElementById('new_first_names').value = first_names;
            window.opener.document.getElementById('new_username').value = username;
            window.opener.document.getElementById('new_email').value = email;
            window.opener.document.getElementById('new_grade').value = course;
            window.opener.document.getElementById('new_gender').value = gender;
            window.opener.document.getElementById('new_yos').value = yearofstudy;
            window.opener.document.getElementById('new_studentid').value = studentid;

            window.close();
        }
    </script>
</head>
    <?php
  if(!isset($output->lookupdata->yearofstudy)) { $output->lookupdata->yearofstudy='';  }
  if(!isset($output->lookupdata->studentID)) { $output->lookupdata->studentID='';  }
  if(!isset($output->lookupdata->coursecode)) { $output->lookupdata->coursecode='';  }
  if(!isset($output->lookupdata->gender)) { $output->lookupdata->gender='';  }

    ?>

    <body onload="writeDetails('<?php echo $output->lookupdata->title; ?>','<?php echo $output->lookupdata->firstname; ?>','<?php echo $output->lookupdata->surname ?>','<?php echo $output->lookupdata->username; ?>','<?php echo $output->lookupdata->email; ?>','<?php echo $output->lookupdata->yearofstudy; ?>','<?php echo $output->lookupdata->gender; ?>','<?php echo $output->lookupdata->coursecode; ?>','<?php echo $output->lookupdata->studentID; ?>');">
    CLOSING WINDOW
    </body>
    <?php


  }
  unset($_SESSION['ldaplookupdata']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>LDAP Lookup</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/screen.css"/>
    <style type="text/css">
        body {
            background-color: #F1F5FB;
            font-size: 90%
        }
    </style>
    <script type="text/javascript">
        function setSelectedIndex(s, v) {
            for (var i = 0; i < s.options.length; i++) {
                if (s.options[i].value.toLowerCase() == v.toLowerCase()) {
                    s.options[i].selected = true;
                    return;
                }
            }
        }

        function writeDetails(user_title, first_names, surname, username, email) {
            window.opener.document.getElementById('new_surname').value = surname;
            window.opener.document.getElementById('new_first_names').value = first_names;
            window.opener.document.getElementById('new_username').value = username;
            window.opener.document.getElementById('new_email').value = email;
            if (user_title == 'Prof') {
                setSelectedIndex(window.opener.document.getElementById('new_users_title'), 'Professor');
            } else {
                setSelectedIndex(window.opener.document.getElementById('new_users_title'), user_title);
            }

            if (user_title == 'Mr') {
                setSelectedIndex(window.opener.document.getElementById('new_gender'), 'Male');
            } else if (user_title == 'Miss' || user_title == 'Mrs' || user_title == 'Ms') {
                setSelectedIndex(window.opener.document.getElementById('new_gender'), 'Female');
            }
            window.close();
        }
    </script>
</head>
<?php
if (isset($_POST['submit'])) {

  $lookup = Lookup::get_instance($configObject, $mysqli);
  $data = new stdClass();
  $data->lookupdata = new stdClass();
  if ($_REQUEST['username'] != '') {
    $data->lookupdata->username = $_REQUEST['username'];
    $data->searchorder = array('username');
  }
  if ($_REQUEST['surname'] != '') {
    $data->lookupdata->surname = $_REQUEST['surname'];
    $data->searchorder = array('surname');
  }

  $data->settings = new stdClass();
//  $data->settings->recursive = true;

  $output = $lookup->userlookup($data);
  ini_set('display_errors', 1);
  ini_set('log_errors', 1);
  ini_set('xdebug.remote_autostart', 1);
  ini_set("display_errors", 1);
  ini_set('xdebug.var_display_max_childrren', -1);
  ini_set('xdebug.var_display_max_data', -1);
  ini_set('xdebug.var_display_max_depth', -1);
 // var_dump($output);


  //$lookup->display_debug();


  if (isset($output->success)) {

    if (!isset($output->lookupdatas)) {
      ?>
    <body>
    <form method="post" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div style="text-align:center">
            <table style="text-align:left">
              <?php
              if (isset($_POST['username']) and $_POST['username'] != '') {
                echo "<tr><td>" . $string['username'] . "</td><td><input type=\"text\" name=\"username\" value=\"" . $_POST['username'] . "\" size=\"20\" style=\"border: 1px solid #800000; background-color:#FFC0C0\" /></td></tr>\n";
              } else {
                echo "<tr><td>" . $string['username'] . "</td><td><input type=\"text\" name=\"username\" value=\"\" size=\"20\" /></td></tr>\n";
              }
              if (isset($_POST['surname']) and $_POST['surname'] != '') {
                echo "<tr><td>" . $string['surname'] . "</td><td><input type=\"text\" name=\"surname\" value=\"" . $_POST['surname'] . "\" size=\"40\" style=\"border: 1px solid #800000; background-color:#FFC0C0\" /></td></tr>\n";
              } else {
                echo "<tr><td>" . $string['surname'] . "</td><td><input type=\"text\" name=\"surname\" value=\"\" size=\"40\" /></td></tr>\n";
              }
              ?>
                <tr>
                    <td colspan="2" style="text-align:center"><input type="submit" name="submit"
                                                                     value="<?php echo $string['lookup'] ?>"
                                                                     style="width:100px"/>&nbsp;&nbsp;<input
                            type="button" name="cancel" value="<?php echo $string['cancel'] ?>"
                            onclick="window.close();" style="width:100px"/></td>
                </tr>
            </table>
        </div>
    </form>
    <script language="JavaScript">
        alert(<?php echo $string['nousersalert'] ?>);
    </script>
    </body>
</html>
<?php
      exit;

    } else {
      $user_data = array();
      $user = 0;
      echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:100%; background-color:white\">\n";
      echo "<tr style=\"cursor:pointer\"><th \">title</th><th \">first_names</th><th \">surname</th><th \">username</th><th \">email</th><th \">role</th></tr>\n";
      foreach ($output->lookupdatas as $key => $object) {

        if (isset($object->title)) {
          $user_data[$user]['title'] = $object->title;
        } else {
          $user_data[$user]['title'] = '';
        }
        if (isset($object->firstname)) {
          $user_data[$user]['first_names'] = $object->firstname;
        } else {
          $user_data[$user]['first_names'] = '';
        }
        if (isset($object->surname)) {
          $user_data[$user]['surname'] = $object->surname;
        } else {
          $user_data[$user]['surname'] = '';
        }
        if (isset($object->username)) {
          $user_data[$user]['username'] = $object->username;
        } else {
          $user_data[$user]['username'] = '';
        }
        if (isset($object->email)) {
          $user_data[$user]['email'] = $object->email;
        } else {
          $user_data[$user]['email'] = '';
        }
        if (isset($object->role)) {
          $user_data[$user]['role'] = $object->role;
        } else {
          $user_data[$user]['role'] = '';
        }
        if (isset($object->school)) {
          $user_data[$user]['school'] = $object->school;
        } else {
          $user_data[$user]['school'] = '';
        }
        $user_data[$user]['key'] = $key;
        $user_data[$user]['object'] = $object;
        $user++;
      }
    }

    if ($user > 1) $user_data = array_csort($user_data, 'first_names', 'asc');
unset($_SESSION['ldaplookupdata']);
    for ($i = 0; $i < $user; $i++) {
      $title = $user_data[$i]['title'];
      $first_names = $user_data[$i]['first_names'];
      $surname = $user_data[$i]['surname'];
      $username = $user_data[$i]['username'];
      $email = $user_data[$i]['email'];
      $school = $user_data[$i]['school'];
      $role = $user_data[$i]['role'];
      $key = $user_data[$i]['key'];
      $object = $user_data[$i]['object'];

      //echo "<tr style=\"cursor:pointer\"><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$title</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$first_names</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$surname</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$username</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$email</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$role</td></tr>\n";
      $_SESSION['ldaplookup'][$i] = $key;
      $_SESSION['ldaplookupdata'][$key]=$object;
      echo "<tr style=\"cursor:pointer\"><td><a href='?LOOKUP=$key'>$title</a></td><td><a href='?LOOKUP=$key'>$first_names</a></td><td><a href='?LOOKUP=$key'>$surname</a></td><td><a href='?LOOKUP=$key'>$username</a></td><td><a href='?LOOKUP=$key'>$email</a></td><td><a href='?LOOKUP=$key'>$role</a></td></tr>\n";
    }
    echo "</table>\n";
  }
  exit();
}

?>
<body>
<br/>

<form method="post" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div style="text-align:center">
        <table style="text-align:left">
            <tr>
                <td><?php echo $string['username'] ?></td>
                <td><input type="text" name="username" size="20"/></td>
            </tr>
            <tr>
                <td><?php echo $string['surname'] ?></td>
                <td><input type="text" name="surname" size="40"/></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center"><input type="submit" name="submit"
                                                                 value="<?php echo $string['lookup'] ?>"
                                                                 style="width:100px"/>&nbsp;&nbsp;<input type="button"
                                                                                                         name="cancel"
                                                                                                         value="<?php echo $string['cancel'] ?>"
                                                                                                         onclick="window.close();"
                                                                                                         style="width:100px"/>
                </td>
            </tr>
        </table>
    </div>
</form>

<?php

?>

</body>
</html>

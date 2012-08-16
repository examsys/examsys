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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/admin_auth.inc';
require '../include/sort.inc';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>LDAP Lookup</title>

  <style type="text/css">
  body {background-color:#F1F5FB; color:black; font-family:Arial,sans-serif; font-size:90%; margin:0px}
  table {font-size:100%}
  textarea, input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
  </style>
  <script type="text/javascript">
    function setSelectedIndex(s, v) {
      for ( var i = 0; i < s.options.length; i++ ) {
        if ( s.options[i].value.toLowerCase() == v.toLowerCase() ) {
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
        setSelectedIndex(window.opener.document.getElementById('new_users_title'),'Professor');
      } else {
        setSelectedIndex(window.opener.document.getElementById('new_users_title'),user_title);
      }

      if (user_title == 'Mr') {
        setSelectedIndex(window.opener.document.getElementById('new_gender'),'Male');
      } else if (user_title == 'Miss' || user_title == 'Mrs' || user_title == 'Ms') {
        setSelectedIndex(window.opener.document.getElementById('new_gender'),'Female');
      }
      window.close();
    }
  </script>
</head>
<?php
  if (isset($_POST['submit'])) {
    $ldap = ldap_connect( $cfg_ldap_server );
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    
    if (ldap_bind($ldap, $cfg_ldap_bind_rdn, $cfg_ldap_bind_password)) {
      if ($_POST['username'] != '') {
        $search=@ldap_search($ldap, $cfg_ldap_search_dn, 'cn=' . trim($_POST['username']));
      } else {
        $search=@ldap_search($ldap, $cfg_ldap_search_dn, 'sn=' . trim($_POST['surname']));
      }
      $info = ldap_get_entries($ldap, $search);
      
      if (!isset($info[0])) {
?>
<body>
<form method="post" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div style="text-align:center">
<table style="text-align:left">
<?php
  if (isset($_POST['username']) and $_POST['username'] != '') {
    echo "<tr><td>".$string['username']."</td><td><input type=\"text\" name=\"username\" value=\"" . $_POST['username'] . "\" size=\"20\" style=\"border: 1px solid #800000; background-color:#FFC0C0\" /></td></tr>\n";
  } else {
    echo "<tr><td>".$string['username']."</td><td><input type=\"text\" name=\"username\" value=\"\" size=\"20\" /></td></tr>\n";
  }
  if (isset($_POST['surname']) and $_POST['surname'] != '') {
    echo "<tr><td>".$string['surname']."</td><td><input type=\"text\" name=\"surname\" value=\"" . $_POST['surname'] . "\" size=\"40\" style=\"border: 1px solid #800000; background-color:#FFC0C0\" /></td></tr>\n";
  } else {
    echo "<tr><td>".$string['surname']."</td><td><input type=\"text\" name=\"surname\" value=\"\" size=\"40\" /></td></tr>\n";
  }
?>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['lookup'] ?>" style="width:100px" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close();" style="width:100px" /></td></tr>
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
    foreach ($info as $person=>$details) {
      
      if ($details['sn'][0] != '') {
        if (isset($details['title'][0])) {
          $user_data[$user]['title'] = $details['title'][0];
        } else {
          $user_data[$user]['title'] = '';
        }
        if (isset($details['givenname'][0])) {
          $user_data[$user]['first_names'] = $details['givenname'][0];
        } else {
          $user_data[$user]['first_names'] = '';
        }
        if (isset($details['sn'][0])) {
          $user_data[$user]['surname'] = $details['sn'][0];
        } else {
          $user_data[$user]['surname'] = '';
        }
        if (isset($details['samaccountname'][0])) {
          $user_data[$user]['username'] = $details['samaccountname'][0];
        } else {
          $user_data[$user]['username'] = '';
        }
        if (isset($details['mail'][0])) {
          $user_data[$user]['email'] = $details['mail'][0];
        } else {
          $user_data[$user]['email'] = '';
        }
        
        if (isset($details['edupersonentitlement'][0])) {
          $user_data[$user]['role'] = $details['edupersonentitlement'][0];
        } else {
          $user_data[$user]['role'] = '';
        }
        if (isset($details['ou'][0])) {
          $user_data[$user]['school'] = $details['ou'][0];
        } else {
          $user_data[$user]['school'] = '';
        }
        $user++;
      }
    }
    
    if ($user > 1) $user_data = array_csort($user_data,'first_names','asc');
    
    for ($i=0; $i<$user; $i++) {
      $title = $user_data[$i]['title'];
      $first_names = $user_data[$i]['first_names'];
      $surname = $user_data[$i]['surname'];
      $username = $user_data[$i]['username'];
      $email = $user_data[$i]['email'];
      $school = $user_data[$i]['school'];
      $role = $user_data[$i]['role'];
      echo "<tr style=\"cursor:pointer\"><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$title</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$first_names</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$surname</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$username</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$email</td><td onclick=\"writeDetails('$title','$first_names','$surname','$username','$email')\">$role</td></tr>\n";
    }
    echo "</table>\n";
  }
}
} else {
?>
<body>
<br />
<form method="post" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<div style="text-align:center">
<table style="text-align:left">
<tr><td><?php echo $string['username'] ?></td><td><input type="text" name="username" size="20" /></td></tr>
<tr><td><?php echo $string['surname'] ?></td><td><input type="text" name="surname" size="40" /></td></tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['lookup'] ?>" style="width:100px" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close();" style="width:100px" /></td></tr>
</table>
</div>
</form>

<?php
  }
?>

</body>
</html>

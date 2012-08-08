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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';
?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Rogō</title>
<script language="JavaScript">
  var selected_q = Array();

  function in_array (needle, haystack) { 
    for (i=0; i<haystack.length; i++) { 
      if (haystack[i] == needle) { 
        return true; 
      } 
    } 
    return false; 
  }

  function myToString(haystack) {
    var str = '';
    for (i=0; i<haystack.length; i++) {
      if (i == 0) {
        str = haystack[i];
      } else {
        str = str + ',' + haystack[i];
      }
    } 
    return str;
  }
  
  function myDelete(needle, haystack) {
    var new_haystack = Array();
    for (i=0; i<haystack.length; i++) {
      if (haystack[i] != needle) {
        new_haystack[new_haystack.length] = haystack[i];
      }
    }
    
    return new_haystack;
  }
  
  function checkStatus(questionObj) {
    var q_id = questionObj.name;
    
    if (in_array(q_id, selected_q) == true && questionObj.checked == false) {          // Question in array but user has unchecked
      selected_q = myDelete(q_id, selected_q);
    } else if (in_array(q_id, selected_q) == false && questionObj.checked == true) {   // User has checked question but it is not in the array
      selected_q.push(q_id);
    }
    
    document.theform.questions_to_add.value = myToString(selected_q);
  }
</script>
</head>
<body style="font-family:Arial,sans-serif; margin-top:0px; margin-left:0px; margin-right:4px; margin-bottom:2px; background-color:#DFECFF; font-size:90%">
<?php
  echo "<form name=\"theform\" method=\"post\" action=\"do_add_questions.php?paperID=" . $_GET['paperID'] . "&display_pos=" . $_GET['display_pos'] . "&module=" . $_GET['module'] . "&folder=" . $_GET['folder'] . "&scrOfY=" . $_GET['scrOfY'] . "&max_screen=" . $_GET['max_screen'] . "\">\n";
  echo "<div align=\"right\">" . $string['screen'] . "&nbsp;<select name=\"screen\">\n";

  $max_screen = $_GET['max_screen'];
  for ($i=1; $i<=$max_screen + 1; $i++) {
    if ($i == $max_screen) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
?>
</select>&nbsp;
<input type="hidden" name="questions_to_add" size="100" value="" /><input type="submit" name="submit" value="<?php echo $string['addquestions']; ?>" /></div>

</form>
</body>
</html>
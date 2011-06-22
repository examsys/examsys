<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require_once $_SERVER['DOCUMENT_ROOT'] . 'touchstone/config/config.inc';
  require '../include/media.inc';
  if (!isset($_SERVER['PHP_AUTH_USER'])) {
    Header("WWW-authenticate: basic realm=\"TouchStone\"");
    Header("HTTP/1.0 401 Unauthorised");
    echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with TouchStone has failed.</p>\n";
    echo "</body>\n</html>\n";
    $mysqli->close();
    exit;
  } else {
    if ($_SERVER['PHP_AUTH_USER'] != 'sctreviewer' or $_SERVER['PHP_AUTH_PW'] != 'vetschool1') {
      Header("WWW-authenticate: basic realm=\"TouchStone\"");
      Header("HTTP/1.0 401 Unauthorised");
      echo "<html>\n<head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
      echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
      echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
      echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Authentication with TouchStone has failed.</p>\n";
      echo "</body>\n</html>\n";
      $mysqli->close();
      exit;
    }
  }
  
  $mysqli = new $dbclass($cfg_db_host , $cfg_db_username, $cfg_db_passwd, $cfg_db_database);
  
  function display_question($question, &$question_no) {
    $question_no++;

    if ($question['scenario'] != '') {
      echo "<tr><td class=\"q_no\">" . $question_no . ".&nbsp;</td><td style=\"background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold; padding:2px; color:#000040\">Clinical Vignette</td></tr>\n";
      echo '<tr><td style="vertical-align:top; text-align:right"></td><td>';
      if ($question['notes'] != '') echo '<p class="note"><img src="/touchstone/artwork/notes_icon.gif" width="14" height="14" alt="Note" />&nbsp;<strong>NOTE:</strong>&nbsp;' . $question['notes'] . '</p>';
      echo $question['scenario'] . "<br />\n<br />";
      $li_set = 1;
    }
    if ($question['q_media'] != '') {
      if ($li_set == 0) {
        echo '<tr><td class="q_no">' . $question_no . '.&nbsp;</td><td>';
      }
      echo '<p align="center">' . display_media($question['q_media'],$question['q_media_width'],$question['q_media_height']) . "</p>\n";
      $li_set = 1;
    }
    
    $sct_parts = explode('~',$question['leadin']);
    echo '<table cellpadding="2" cellspacing="0" border="0" style="width:100%">';
    $sct_titles = array(1=>'Hypothesis',2=>'Investigation',3=>'Prescription',4=>'Intervention',5=>'Treatment');
    echo "<tr><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">" . $sct_titles[$question['score_method']] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; background-color:#E4EEFC; border-bottom:1px solid #B5C4DF; font-weight:bold\">New Information</td></tr>\n";
    if ($question['score_method'] == 1) {
      echo "<tr><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">If you were thinking of the following diagnosis</span><br />" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">And then you find:</span><br />" . $sct_parts[1] . "</td></tr>\n";
    } elseif ($question['score_method'] == 2) {
      echo "<tr><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">If you were considering to ask</span><br />" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">And then you find:</span><br />" . $sct_parts[1] . "</td></tr>\n";
    } elseif ($question['score_method'] == 3) {
      echo "<tr><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">If you were considering to prescribe</span><br />" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">And then you find:</span><br />" . $sct_parts[1] . "</td></tr>\n";
    } elseif ($question['score_method'] == 4) {
      echo "<tr><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">If you were considering the following intervention</span><br />" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">And the following new information were to become available:</span><br />" . $sct_parts[1] . "</td></tr>\n";
    } else {
      echo "<tr><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">If you were considering the following treatment</span><br />" . $sct_parts[0] . "</td><td style=\"width:2%\">&nbsp;</td><td style=\"width:49%; vertical-align:top\"><span style=\"color:#808080\">And the following new information were to become available:</span><br />" . $sct_parts[1] . "</td></tr>\n";
    }
    echo "</table>\n";
      
    echo '<p><strong>';
    if ($question['score_method'] == 1) {
      echo 'Then this hypothesis becomes:';
    } elseif ($question['score_method'] == 2) {
      echo 'Then this investigation becomes:';
    } elseif ($question['score_method'] == 3) {
      echo 'Then this prescription becomes:';
    } elseif ($question['score_method'] == 4) {
      echo 'Then this intervention becomes:';
    } elseif ($question['score_method'] == 5) {
      echo 'Then this treatment becomes:';
    }
    echo '</strong></p>';
    echo '<blockquote><table cellpadding="2" cellspacing="0" border="0">';
      
    $part_id = 0;
    foreach ($question['options'] as $option_text) {
      $part_id++;
      echo "<tr><td><input type=\"radio\" name=\"q" . $question_no . "\" value=\"$part_id\" /></td><td>$option_text</td></tr>\n";
    }
    echo "</table>\n</blockquote>\n";
    
    echo "<span style=\"color:#808080\">Brief reason why?</span><br /><textarea name=\"reason$question_no\" cols=\"80\" rows=\"3\" /></textarea>\n";
    echo "</td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  }
  
  if (isset($_POST['submit'])) {
    $question_no = 1;

    // Clear previous ratings for current reviewer and current paper
    $stmt = $mysqli->prepare("DELETE FROM sct_reviews WHERE papers.paper=? AND reviewer_name=? AND reviewer_email=?");
    $stmt->bind_param('iss',$_GET['paperID'], $_POST['reviewer_name'], $_POST['reviewer_email']);
    $stmt->execute();
    $stmt->close();

    // Loop through the structure of the paper
    $stmt = $mysqli->prepare("SELECT q_id FROM (papers, questions) WHERE papers.paper=? AND papers.question=questions.q_id AND q_type='sct' ORDER BY display_pos");
    $stmt->bind_param('i',$_GET['paperID']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($q_id);
    while ($stmt->fetch()) {
      // Store experts' reviews in sct_reviews table
      $update = $mysqli->prepare("INSERT INTO sct_reviews VALUES (NULL,?,?,?,?,?,?");
      $update->bind_param('ssiiis',$_POST['reviewer_name'], $_POST['reviewer_email'], $_GET['paperID'], $q_id, $_POST['q' . $question_no], $_POST['reason' . $question_no]);
      $update->execute();
      $update->close();

      $question_no++;
    }
    $stmt->close();  
  
  } else {
    require '../config/start.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>SCT Review</title>
<style type="text/css">
body {background-color:white;color:black;padding:0px;margin:0px;border:0px;font-family:Arial,sans-serif;font-size:90%}
li {margin-left:15px;margin-right:15px;font-size:100%}
select,input{font-family:$font,sans-serif;font-size:100%}
table {font-size:100%}
pre {font-family:Arial,sans-serif; font-size:100%}
.q_no {width:40px; text-align:right;vertical-align:top}
.theme {font-size:150%; padding-left:4px;font-weight:bold;color:#316AC5}
.note {color:#C00000}
.mk {color:#808080;font-size:80%}
</style>
</head>

<body>
<?php
  $stmt = $mysqli->prepare("SELECT paper_title FROM properties WHERE property_id=?");
  $stmt->bind_param('i',$_GET['paperID']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($paper_title);
  $stmt->fetch();
  $stmt->close();
  
  // Output the top logo banner.
  echo $top_table_html;
  echo '<tr><td><div style="margin-left:0px;font-size:180%;color:white;font-weight:bold">' . $paper_title . '</div></td>';
  echo $logo_html;

  echo "<form name=\"myform\" action=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "\" method=\"post\">\n";
  echo "<br />\n";
  
  echo "<blockquote>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"padding:10px; border: 1px solid #C0C000; background-color:#FFFFC0; width:100%; font-size:100%\">\n";
  echo "<col width=\"80\"><col>\n";
  echo "<tr><td colspan=\"2\">This screen is designed to allow you to answer the following Script Concordance Test questions. Please provide a brief reason why you believe each answer is correct.</td></tr>\n";
  echo "<tr><td>Name</td><td><input type=\"text\" name=\"reviewer_name\" size=\"50\" /></td></tr>\n";
  echo "<tr><td>Email</td><td><input type=\"text\" name=\"reviewer_email\" size=\"50\" /></td></tr>\n";
  echo "</table>\n</blockquote>\n";
  
  echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:100%; font-size:100%\">\n<col width=\"40\"><col>\n";

  //build the questions_array
  $old_q_id = '';
  $q_no = 0;
  $question_no = 0;

  $stmt = $mysqli->prepare("SELECT q_id, theme, leadin, scenario, notes, score_method, q_media, q_media_width, q_media_height, q_option_order, option_text FROM (papers, questions, options) WHERE papers.paper=? AND papers.question=questions.q_id AND questions.q_id=options.o_id AND q_type='sct' ORDER BY display_pos, id_num");
  $stmt->bind_param('i',$_GET['paperID']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($q_id, $theme, $leadin, $scenario, $notes, $score_method, $q_media, $q_media_width, $q_media_height, $q_option_order, $option_text);
  while ($stmt->fetch()) {
    if ($old_q_id != $q_id) {
      $q_no++;
      $questions_array[$q_no]['theme'] = trim($theme);
      $questions_array[$q_no]['scenario'] = trim($scenario);
      $questions_array[$q_no]['leadin'] = trim($leadin);
      $questions_array[$q_no]['notes'] = trim($notes);
      $questions_array[$q_no]['q_id'] = $q_id;
      $questions_array[$q_no]['score_method'] = $score_method;
      $questions_array[$q_no]['q_media'] = $q_media;
      $questions_array[$q_no]['q_media_width'] = $q_media_width;
      $questions_array[$q_no]['q_media_height'] = $q_media_height;
      $questions_array[$q_no]['q_option_order'] = $q_option_order;
    }
    $questions_array[$q_no]['options'][] = $option_text;
    
    $old_q_id = $q_id;
  }
  $stmt->close();
  
  //display the questions
  foreach($questions_array as &$question) {
    if ($q_displayed == 0 and $question['theme'] == '') echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
    display_question($question, $question_no);	
  }

?>
</table>

<div style="text-align:center"><input type="submit" name="submit" value="Save" style="width:100px" /></div>
<br />
</form>
<?php
  echo $bottom_html;
?>
</body>
</html>
<?php
}
?>

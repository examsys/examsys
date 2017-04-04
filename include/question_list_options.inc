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
* Task menu for question list.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once $cfg_web_root . 'include/sidebar_menu.inc';
require_once $cfg_web_root . 'include/sidebar_functions.inc';
require_once $cfg_web_root . 'include/errors.php';
require_once $cfg_web_root . 'include/mapping.inc';

$module = check_var('module', 'GET', true, false, true);

$add_member = false;

if (!isset($module_details) && $module != '0') {
  $module_details = module_utils::get_full_details_by_ID($module, $mysqli);
}
?>
<?php echo $configObject->get('cfg_js_root') ?>
<script>
  function getLastID(IDs) {
    var id_list = IDs.split(",");
    last_elm = id_list.length - 1;
    
    return id_list[last_elm];
  }

  function addQID(qID, clearall) {
    if (clearall) {
      $('#questionID').val(',' + qID);
    } else {
      new_value = $('#questionID').val() + ',' + qID;
      $('#questionID').val(new_value);
    }
  }

  function subQID(qID) {
    var tmpq = ',' + qID;
    new_value = $('#questionID').val().replace(tmpq, '');
    $('#questionID').val(new_value);
  }
  
  function clearAll() {
    $('.highlight').removeClass('highlight');
  }
  
  function previewQ() {
    notice=window.open("../question/view_question.php?q_id=" + getLastID($('#questionID').val()) + "","preview","left=10,top=10,width=1000,height=700,scrollbars=yes,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
  }
	
  function ed() {
    var url = '<?php echo $configObject->get('cfg_root_path') ?>/question/edit/index.php?q_id=' + getLastID($('#questionID').val()) + '&calling=list';
    <?php 
      if (isset($_GET['team']) and $_GET['team'] != '') {
          echo "url = url + '&team=" . $_GET['team'] . "';\n";
      }
      if (isset($_GET['keyword']) and $_GET['keyword'] != '') {
          echo "url = url + '&keyword=" . $_GET['keyword'] . "';\n";
      }
    ?>
    window.location.href = url;
    return false;
  }

  function deleteQuestion() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/delete/check_delete_q_original.php?q_id=" + $('#questionID').val() + "","notice","width=500,height=200,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250, screen.height/2-100);
    if (window.focus) {
      notice.focus();
    }
  }

  function questionInfo() {
    notice=window.open("<?php echo $configObject->get('cfg_root_path') ?>/question/info.php?q_id=" + getLastID($('#questionID').val()) + "","question_info","width=700,height=620,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-250, screen.height/2-260);
    if (window.focus) {
      notice.focus();
    }
  }

  function copyToPaper() {
    <?php
    $url = $configObject->get('cfg_root_path') . '/question/copy_onto_paper.php?q_id=" + $(\'#questionID\').val()';
    if (isset($_GET['type']) and $_GET['type'] != '') {
      $url .= ' + "&type=' . $_GET['type'] . '&module=' . $module;
    }
    ?>
    notice=window.open("<?php echo $url ?>","notice","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300, 20);

    if (window.focus) {
      notice.focus();
    }
  }

  function getSelectedOutcomes() {
    var outcomes = {};
    $('.check_type:checked').each(function() {
      var ids = $(this).data('ids');
      if (ids != '') {
        ids = ids.toString().split(',');
        for (var i = 0; i < ids.length; i++) {
          outcomes[ids[i].toString()] = $(this).val();
        }
      }
    });
    return JSON.stringify(outcomes);
  }

  function linkToPaper() {
    <?php
    $url = $configObject->get('cfg_root_path') . '/question/link_to_paper.php?q_id=" + $(\'#questionID\').val()';
    if (isset($_GET['type']) and $_GET['type'] != '') {
      $url .= ' + "&type=' . $_GET['type'] . '&module=' . $module;
    }
    ?>
    notice=window.open("<?php echo $url ?>","notice","width=600,height=" + (screen.height-100) + ",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    notice.moveTo(screen.width/2-300, 20);
    if (window.focus) {
      notice.focus();
    }
  }

  function qOff() {
    $('#menu2a').show();
    $('#menu2b').hide();
    $('#menu2c').hide();
    $('#questionID').val();
    clearAll();
  }
  
  function check_checkboxes() {
    $(".q").hide();
    $('input[type=checkbox].check_type:checked').each(function () {
      q_type = (typeof $(this).data('ids') == 'undefined') ? $(this).val() : $(this).data('ids').toString().replace(/,/g, ',.');
      $("." + q_type).show();
      if (!$('#check_locked').attr('checked')) {
        $("." + q_type + '.lock').hide();
      }
    });
    
    count_questions();
  }
  
  function count_questions() {
    var n = $(".q:visible").length;
    var count = n.toLocaleString("<?php echo $language ?>");
    // Old browsers use and old implementation of toLocaleString so we need to check for decimal places
    // in the string and remove them.
    var dp = count.indexOf('.');
    if (dp !== -1) {
      count = count.slice(0,dp);
    }
    $("#q_count").text('(' + count + ')');
  }
  
  function resizeList() {
    var offset = $('#list').position().top;
    winH = ($(window).height() - offset) - 2;

    $('#list').css('height', winH + 'px');
  }

  var hide_unmapped_obs = function() {
    $('.check_type').parent().addClass('hidden');
    $('.check_type').each(function() {
      var ids = $(this).data('ids');
      ids = ids.toString().split(',');
      for (var i = 0; i < ids.length; i++) {
        if ($('.q.' + ids[i]).length > 0) {
          $(this).parent().removeClass('hidden');
          break;
        }
      };
    });
  }

  $(function () {
    $(".check_type").click(function() {
      check_checkboxes();
    });
    
    $("#check_locked").click(function() {
      check_checkboxes();
    });
    
    $(".q").click(function(evt) {
      var passed_id = $(this).attr('id');
      var remainder = passed_id.substring(1);
      var parts = remainder.split("_");
      var questionID = parts[0];
      var lineID = parts[1];
      
      $('#menu2a').hide();
      if ($('#' + passed_id).hasClass('lock')) {
        $('#menu2b').hide();
        $('#menu2c').show();
      } else {
        $('#menu2c').hide();
        $('#menu2b').show();
      }
      
      if (evt.ctrlKey == false && evt.metaKey == false) {
        clearAll();
        $('#' + passed_id).addClass('highlight');
        addQID(questionID, true);
      } else {
        if ($('#' + passed_id).hasClass('highlight')) {
          $('#' + passed_id).removeClass('highlight');
          subQID(questionID);
        } else {
          $('#' + passed_id).addClass('highlight');
          addQID(questionID, false);
        }
      }
      
    });

<?php
if (isset($_GET['type']) and $_GET['type'] == 'objective') {
?>
    hide_unmapped_obs();

    $('#filter').keyup(function() {
      var match = $(this).val().toLowerCase();

      if (match.length > 2) {
        $('.check_type').not('.hidden').each(function() {
          if ($(this).next('label').text().toLowerCase().indexOf(match) == -1) {
            $(this).closest('div').addClass('filter');
          } else {
            $(this).closest('div').removeClass('filter');
          }
        });
      }
    });

    $('#filter_clear').click(function() {
      $('.filter').removeClass('filter');
      $('#filter').val('');
    })
<?php
}
if ($type == 'all') {
  echo "count_questions();\n";
} else {
  echo "check_checkboxes();\n";
}
?> 
    resizeList();
      
    $(window).resize(function(){
      resizeList();
    });
  });
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="" autocomplete="off">

<div id="menu2a">
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/small_play_grey.png" alt="<?php echo $string['quickview'] ?>" /><?php echo $string['quickview'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit_grey.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon_grey.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon_grey.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link_grey.png" alt="<?php echo $string['linktopaperx'] ?>" /><?php echo $string['linktopaperx'] ?></div>
	<div class="grey menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion'] ?>" /><?php echo $string['deletequestion'] ?></div>
</div>

<div id="menu2b">
	<div class="menuitem"><a id="preview" onclick="previewQ(); return false;" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview'] ?>" /><?php echo $string['quickview'] ?></a></div>
	<div class="menuitem"><a id="edit" onclick="ed(); return false;" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></a></div>
	<div class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></a></div>
	<div class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></a></div>
	<div class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx'] ?>" /><?php echo $string['linktopaperx'] ?></a></div>
	<div class="menuitem"><a id="delete" href="#" onclick="deleteQuestion(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross.png" alt="<?php echo $string['deletequestion'] ?>" /><?php echo $string['deletequestion'] ?></a></div>
</div>

<div id="menu2c">
	<div class="menuitem"><a id="preview" onclick="previewQ(); return false;" href="#"><img class="sidebar_icon" src="../artwork/small_play.png" alt="<?php echo $string['quickview'] ?>" /><?php echo $string['quickview'] ?></a></div>
	<div class="menuitem"><a id="edit" onclick="ed(); return false;" href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/edit.png" alt="<?php echo $string['editquestion'] ?>" /><?php echo $string['editquestion'] ?></a></div>
	<div class="menuitem"><a id="information" href="#" onclick="questionInfo(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/information_icon.gif" alt="<?php echo $string['information'] ?>" /><?php echo $string['information'] ?></a></div>
	<div class="menuitem"><a id="copy" href="#" onclick="copyToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/copy_icon.gif" alt="<?php echo $string['copyontopaperx'] ?>" /><?php echo $string['copyontopaperx'] ?></a></div>
	<div class="menuitem"><a id="link" href="#" onclick="linkToPaper(); return false;"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/link.png" alt="<?php echo $string['linktopaperx'] ?>" /><?php echo $string['linktopaperx'] ?></a></div>
	<div class="menuitem"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/red_cross_grey.png" alt="<?php echo $string['deletequestion'] ?>" /><?php echo $string['deletequestion'] ?></div>
</div>

<div>
<div class="menuitem cascade" id="newquestion" onclick="showMenu('popup0','banktasks','newquestion',myOptions0,myURLs0,event)"><a href="#"><img class="sidebar_icon" src="<?php echo $configObject->get('cfg_root_path') ?>/artwork/new_question_menu_icon.gif" alt="newquestion" /><?php echo $string['createnewquestion'] ?></a></div>
</div>

<?php

if ($module != 0 and strpos($module_details['checklist'], 'mapping') === false and $_GET['type'] == 'objective') {
  // Trying to access learning objectives when not selected at the module level.
} else {
  switch ($_GET['type']) {
    case 'all':    
    case 'type':
      $display_type = $string['type'];
      break;
    case 'status':
      $display_type = $string['status'];
      break;
    case 'bloom':
      $display_type = $string['bloomstaxonomy'];
      break;
    case 'keyword':
      $display_type = $string['keyword'];
      break;
    case 'performance':
      $display_type = $string['performance'];
      break;
    case 'objective':
      $display_type = $string['learningoutcome'];
      break;
  }
  echo "<div style=\"padding-top:30px; font-size:170%\">$display_type</div>\n";
  ?>
  <div style="height:78%; overflow:auto" id="list">
  <?php
    echo "<div style=\"margin-bottom:15px\"><input type=\"checkbox\" id=\"check_locked\" value=\"locked\" checked=\"checked\" /><label style=\"background-color:#F0C01E\">" . $string['showlockedquestions'] . "&nbsp;</label></div>\n";
    if (isset($_GET['type'])) {
      if ($_GET['type'] == 'objective') {
        echo '<div style="margin-bottom:15px"><input type="text" name="filter" id="filter" style="font-size:110%" placeholder="' . $string['search'] . '" /> <button type="button" style="padding:2px 6px 2px 6px" id="filter_clear">' . $string['clear'] . '</button></div>';
      }

      $subtypes = $qbank->get_categories($type);
      if ($type != 'bloom') {    // Do not sort Bloom's Taxonomy categories.
        asort($subtypes);     // Sort the options alphabetically. Different languages need different orders.
      }

      foreach ($subtypes as $id=>$subtype) {
        $data = '';

        if ($_GET['type'] == 'all') {
          $checked = ' checked';
        } elseif (isset($_GET['subtype']) and $_GET['subtype'] == $id) {
          $checked = ' checked';
        } else {
          $checked = '';
        }

        $hidden = '';
        if ($_GET['type'] == 'objective') {
          $ids = $subtype['ids'];
          $data = ' data-ids="' . implode(',', $ids) . '"';

          $subtype = $subtype['label'];
          $hidden = ' class="hidden"';
        }

        echo "<div{$hidden}><input type=\"checkbox\" class=\"check_type\" id=\"check_$id\" value=\"$id\" $checked $data/><label for=\"check_$id\" class=\"check_label\">$subtype</label></div>\n";
      }
    }
  }
?>
</div>

<input type="hidden" name="questionID" id="questionID" value="" />
<input type="hidden" name="qType" id="qType" value="" />
<input type="hidden" name="oldQuestionID" id="oldQuestionID" value="" />
<input type="hidden" name="team" id="team" value="<?php if(isset($_GET['team'])) echo $_GET['team'] ?>" />

</form>
</div>
<?php

  makeMenu(array(
    $string['info'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=info&module=$module",
    $string['keyword_based'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=keyword_based&module=$module",
    $string['random'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=random&module=$module",
    "-" => "-",
    $string['area'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=area&module=$module",
    $string['calculation'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=enhancedcalc&module=$module",
    $string['dichotomous'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=dichotomous&module=$module",
    $string['extmatch'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=extmatch&module=$module",
    $string['blank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=blank&module=$module",
    $string['hotspot'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=hotspot&module=$module",
    $string['labelling'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=labelling&module=$module",
    $string['likert'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=likert&module=$module",
    $string['matrix'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=matrix&module=$module",
    $string['mcq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mcq&module=$module",
    $string['mrq'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=mrq&module=$module",
    $string['rank'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=rank&module=$module",
    $string['sct'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=sct&module=$module",
    $string['textbox'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=textbox&module=$module",
    $string['true_false'] => "{$configObject->get('cfg_root_path')}/question/edit/index.php?type=true_false&module=$module")
  );

  hideMenuScript($menuNo);
?>
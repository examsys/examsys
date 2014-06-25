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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/
?>
<script>
    
var ajaxSave = function () {
    // Redirect the form 
    $('#osceform').attr('action',"<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id']; ?>&dont_record=true");

    <?php // Hide any errors ?>
    $('#saveError').fadeOut('fast');
    <?php // Random page ID to stop IE caching results. ?>
    date = new Date();
    randomPageID = date.getTime();
    $.ajax({
          url: '<?php echo $_SERVER['PHP_SELF']?>?id=<?php echo $_GET['id']; ?>&rnd=' + randomPageID + '&dont_redirect=true',
          type: 'post',
          data: $('#osceform').serialize() + '&submit=true',
          dataType: 'html',
          timeout: <?php
                    // Set the time out of one requst to be the maximum total time plus 5s for network latency
                    // PHP handles nomal timeouts. This is just to make sure the user wont wait forever if somthing
                    // weird happens.
                    echo ceil((($configObject->get('cfg_autosave_retrylimit') * $configObject->get('cfg_autosave_backoff_factor') * $configObject->get('cfg_autosave_settimeout')) + $configObject->get('cfg_autosave_settimeout') + 5)) * 1000;
                   ?>,
          cache: false,
          tryCount : 0,
          retryLimit : <?php echo $configObject->get('cfg_autosave_retrylimit'); // Try 3 times before erroring ?>,
          beforeSend: function() {
          },
          fail: function() {
            if (this.retry()) {
              return;
            } else  {
              saveFail();
              return;
            }
          },
          error: function(xhr, textStatus, errorThrown) {
            if (textStatus == 'timeout' ) {
              saveFail();
              return;
            } else if (textStatus == 'error') {
              if (this.retry()) {
                return;
              } else  {
                saveFail();
                return;
              }
            }
            saveFail();
            return;
          },
          success: function (ret_data, jqXHR, textStatus) {
              if (ret_data == randomPageID) {
                saveSuccess();
                return;
              }
              if (this.retry()) {
                return;
              } else  {
                saveFail();
                return;
              }
          },
          retry: function (){
            <?php // Retry if we can ?>
            this.tryCount++;
            if (this.tryCount <= this.retryLimit) {
              <?php // Indicate the retry on the url ?>
              if (this.tryCount == 1) {
                this.url = this.url + "&retry=" + this.tryCount;
              } else {
                this.url = this.url.replace("&retry=" + (this.tryCount - 1), "&retry=" + this.tryCount);
              }
              $.ajax(this);
              return true;
            }
            return false
          }
      });
    return false;
  }

  var saveSuccess = function () {
		$('#osceform').submit();
		return true;
  }

  var saveFail = function () {
    $('#saveError').fadeIn('fast');
    $('#savemsg').html("");
    document.body.style.cursor = 'default';
    return false;
  }
  
  
$(document).ready(function () {
    $('#save').replaceWith('<input id="save" type="submit" name="submitButton" value="<?php echo $string['save']; ?>" style="font-size:120%; width:120px; height:35px; font-weight:bold" disabled />');
    $('#save').click(ajaxSave);
    checkTotals();
});          
</script>
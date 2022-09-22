<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

?>
<div id="media-upload-holder">
  <div id="media-upload">
    <h2 class="midblue_header"><?php echo $string['uploadimage'] ?></h2>
    <p><?php echo $string['uploadinstructions'] ?></p>
    <p><label for="q_media" class="heavy"><?php echo $string['image'] ?></label> <input id="q_media" name="q_media" size="45" type="file" /></p>
    <p class="align-centre"><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" /> <input type="submit" data-popupid="submit_media" id="submit_media" name="submit_media" value="<?php echo $string['next'] ?>" class="cancel" /></p>
  </div>
</div>

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
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/
?>
  <div id="media-upload-holder">
    <div id="media-upload">
      <h2><?php echo $string['uploadimage'] ?></h2>
      <p><?php echo $string['uploadinstructions'] ?></p>
      <p><label for="q_media" class="heavy"><?php echo $string['image'] ?></label> <input id="q_media" name="q_media" size="45" type="file" /></p>
      <p class="compact"><a href="#" id="media-labels-link">Upload images for labels</a></p>
      <div id="media-label-upload">
        <p>Maximum size 200 pixels square.</p>
        <p><label for="label_media1" class="heavy"><?php echo $string['image'] ?> 1</label> <input id="label_media1" name="label_media1" size="45" type="file" /></p>
        <p><label for="label_media2" class="heavy"><?php echo $string['image'] ?> 2</label> <input id="label_media2" name="label_media2" size="45" type="file" /></p>
        <p><label for="label_media3" class="heavy"><?php echo $string['image'] ?> 3</label> <input id="label_media3" name="label_media3" size="45" type="file" /></p>
        <p><label for="label_media4" class="heavy"><?php echo $string['image'] ?> 4</label> <input id="label_media4" name="label_media4" size="45" type="file" /></p>
        <p><label for="label_media5" class="heavy"><?php echo $string['image'] ?> 5</label> <input id="label_media5" name="label_media5" size="45" type="file" /></p>
        <p class="compact"><label for="label_media6" class="heavy"><?php echo $string['image'] ?> 6</label> <input id="label_media6" name="label_media6" size="45" type="file" /></p>
      </div>
      <p class="align-centre"><input type="button" name="cancel" value="Cancel" onclick="javascript: history.back();" class="submit cancel" /> <input type="submit" name="submit_media" value="<?php echo $string['next'] ?>" class="submit cancel" /></p>
    </div>
  </div>

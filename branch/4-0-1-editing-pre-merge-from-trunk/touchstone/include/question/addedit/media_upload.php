  <div id="media-upload-holder">
    <div id="media-upload">
      <h2>Upload Image</h2>
      <p>Please select the image file you would like to use as the basis for this new question. Images must be in JPEG, GIF or PNG formats and be no larger than 900x800 pixels.</p>
      <form name="upload_form" method="post" action="./<?php echo $query_string ?>" enctype="multipart/form-data">
        <p><label for="q_media" class="heavy">Image</label> <input id="q_media" name="q_media" size="45" type="file" /></p>
        <p class="align-centre"><input type="button" name="cancel" value="Cancel" onclick="javascript: history.back();" class="submit" /> <input type="submit" name="submit_media" value="Next &gt;" class="submit" /></p>
      </form>
    </div>
  </div>

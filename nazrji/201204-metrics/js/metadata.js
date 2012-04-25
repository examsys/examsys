function toggle(objectID) {
  if (document.getElementById(objectID).style.backgroundColor == 'white') {
    document.getElementById(objectID).style.backgroundColor = '#B3C8E8';
  } else {
    document.getElementById(objectID).style.backgroundColor = 'white';
  }
}

function deleteMedia(media_id) {
  document.getElementById(media_id).style.display = 'none';
  document.getElementById('delete_'+media_id).value = '1';
}

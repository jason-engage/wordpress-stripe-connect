jQuery(document).ready(function($){
  $('#stripe-connect-delete').click(function() {
    if(confirm('You are about the permanently delete the selected item.\n Cancel to stop, OK to delete.')) {
      return true;
    } else {
      return false;
    }
  });
});
$(document).ready(function(){

  $.get("/plugs/page/<?php echo isset($page) && (int)$page ? $page : 1; ?>/", function(data){
    
    $('#pluglist').html(data);
  });
  
});
$(document).ready(function(){

  $.get("/updates/page/<?php echo isset($page) && (int)$page ? $page : 1; ?>/", function(data){
    
    $('#updatelist').html(data);
  });
  
});
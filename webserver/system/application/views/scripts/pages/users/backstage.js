$(document).ready(function(){

  $.get("/recent", function(data){
    
    $('#updatelist').html(data);
  });
  $("#update_frm").validate({
          debug: true,
          rules: {
             "short": {
                
                
                minlength: 4,
                maxlength: 140
              },
              "tweet": {
                
                
                minlength: 4,
                maxlength: 140
              },
              "link": {
                
                url: true
              },
              "photo": {
                
                url: true
              },
              "img_curl": {
                
                url: true
              }
          },
          messages: {
            "short": {
                
                
                minlength: '4 characters min',
                maxlength: '140 characters max'
              },
              "link": {
                
                url: 'Must be valid URL'
              }
            },
            submitHandler: function (form)
            {
                $('button#publish').hide();
                $('#updatelist').css('opacity',0.2);
                $('button#publish').after('<div class="loader">publishing...</div>');
                var options = { 
                  success: function (data)
                  {
                    
                    
                    $('.loader').fadeOut('slow', function ()
                    {
                      $('button#publish').show();
                      $.get("/recent", function(d){
                      
                        $('#updatelist').html(d);
                        $('#updatelist').animate({'opacity':1},500);
                      });
                      
                    });
                  },
                  error:       function (xml,e,s)
                  {
                    $('.loader').html(xml.responseText);
                  },
                  resetForm: true,
                  type:      'post',        
                  dataType:  'json'
                }; 
                form.submit(); 
            }
        });
});
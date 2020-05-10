$(document).ready(function(){
    
    
    
      $("#login_frm").validate({
        rules: {
          "email": {
              "email": true,
              "required": true
            },
            "password": {
              
              "required": true
            }
        },
          
          submitHandler: function (form)
          {
            pw = $("#login_password").val();
            if(pw)
            {
              pw = $.md5(pw);
            
              var options = { 
                  data: {'email': $("#login_email").val(), 'password': pw, 'remember': $('input[name=remember]').val()},      
                  success:       function (data)
                  {
                    <?php if(isset($redirect)) { ?>
                    
                    window.location.href = '<?php echo $redirect; ?>';
                    
                    <?php } else { ?>
                    
                    window.location.href = '/backstage';
                    
                    <?php } ?>
                  },
                  error:       function ()
                  {
                    $("#login_password").val('');
                  },
                  type:      'post',     
                  dataType:  'json'
           
                  
              }; 
              $(form).ajaxSubmit(options); 
            }
            
          
      
      }});
      
      $("#register_frm").validate({
          rules: {
            "user[email]": {
                "email": true,
                "required": true,
                remote: "/check/"
              },
              "user[password]": {
                
                "required": true,
                'minlength': 7
              },
              cpassword: {
                equalTo: "#register_password"
              }
          },
          messages: {
            "user[email]": {
                "email": 'Must be valid email',
                "required": 'Required',
                remote: 'Email already in use'
              },
              "user[password]": {
                
                "required": 'Required',
                'minlength': '7 characters minimum'
              },
              cpassword: {
                equalTo: "Type the password again"
              }
            },
            submitHandler: function (form)
            {
              pw = $("#register_password").val();
              if(pw)
              {
                pw = $.md5(pw);
                var options = { 
                  data: {'email': $("#register_email").val(), 'password': pw},
                  success:       function (data)
                  {
                    <?php if(isset($redirect)) { ?>
                    
                    window.location.href = '<?php echo $redirect; ?>';
                    
                    <?php } else { ?>
                    
                    window.location.href = '/settings';
                    
                    <?php } ?>
                  },
                  error:       function (xml,e,t)
                  {
                    alert("Uh oh, looks like our server had a hiccup. Either try double checking what you typed, and sign up again, or try back later.");
                    
                  },
                  type:      'post',        
                  dataType:  'json'
                }; 
                $(form).ajaxSubmit(options); 
              }
            }
        
        });
      
      
    });
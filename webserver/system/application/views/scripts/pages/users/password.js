$(document).ready(function(){

     $("#profile_frm").validate({
          rules: {
            "user[password]": {
                minlength: 7,
                "required": true
                
              },
              "cpassword": {
                equalTo: '#user_password'
                
              }
          },
          messages: {
            "user[password]": {
                required: 'Required',
                minlength: 'Must be 7 at least 7 characters'
                
              },
              "cpassword": {
                equalTo: 'Passwords not the same'
                
              }
            },
            submitHandler: function (form)
            {
               btn = $(form).find('button[type=submit]');
               btn.hide();
               btn.after('<div class="loader">saving...</div>');
          
          
               udata = {};
                pw = $("#user_password").val();
                if(pw) udata = {'user[password]': $.md5(pw)};
                
                var options = { 
                  data: udata,
                  success:       function (data)
                  {
                    $(form).clearForm();
                    $('.userpw form').empty();
                    $('.userpw h3').css({color: '#A4CC35'}).text('Password Changed!');
                    
                  },
                  error:       function (xmlr)
                  {
                    
                  },
                  type:      'post',        
                  dataType:  'json'
                }; 
                $(form).ajaxSubmit(options); 
              }
            }
        );
    });
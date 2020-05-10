$(document).ready(function(){
     
     $("#profile_frm").validate({
          rules: {
            "user[email]": {
                "email": true,
                "required": true,
                remote: "/check/"
              },
              "user[slug]": {
                "required": true,
                remote: "/check/slug/"
              }
          },
          messages: {
            "user[email]": {
                "email": 'Must be valid email',
                "required": 'Required',
                remote: 'Email already in use'
              },
              "user[slug]": {
                "required": 'Required',
                remote: "This name is taken, try again"
              }
            },
            submitHandler: function (form)
            {
               btn = $(form).find('button[type=submit]');
               btn.hide();
               btn.after('<div class="loader">saving...</div>');
          
          
               dob = '0000-00-00';
               year = $("select[name='user[dob][Year]']").val();
               month = $("select[name='user[dob][Month]']").val();
               day = $("select[name='user[dob][Day]']").val();
               if(year && month && day)
               {
                    dob = year + '-' + month + '-' + day;
               }
                    
                udata = {
                         'user[first]': $("#user_firstname").val(),
                         'user[last]': $("#user_lastname").val(),
                         'user[dob]': dob,
                         'user[slug]': $("#user_slug").val(),
                         'user[gender]': $("#user_gender").val(),
                         'user[email]': $("#user_email").val()
                         };
                         
                
                         
                
                
                var options = { 
                  data: udata,
                  success:       function (data)
                  {
                    
                    l = $(form).find('.loader');
                    l.css({color: '#A4CC35'}).text('Saved!');
                    l.fadeOut(1000, function ()
                    {
                         btn.fadeIn();
                         
                           
                    });
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
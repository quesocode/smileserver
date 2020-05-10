$(document).ready(function(){
     $('.account_type.active input[type=text], .account_type.active input[type=password]').live('keyup', function (e)
     {
          $(this).closest('.account_type').find('.account_submit').addClass('stay').fadeIn();
     });
     function setupPwLink ()
     {
          $('.account_type.active .currentpw').html("<a href='#' title='Change Password'>Change Password</a>");
          $('.account_type.active .changepw').empty();
          $('.account_type.active .currentpw a').unbind();
          $('.account_type.active .currentpw a').toggle(function (e)
          {
               $(this).addClass('open');
               $(this).text("Don't change Password");
               p = $(this).closest('.account_type');
               btn = p.find('.account_submit');
               cp = p.find('.changepw');
               acc = p.attr('id');
               cp.hide().html('<label for="'+acc+'_password">Password</label><fieldset><input type="password" name="account[password]" id="'+acc+'_password" class="password"  autocomplete=off /></fieldset>');
               
               cp.slideDown(function ()
               {
                    pw = $(this).find('input.password');
                    
                    if(pw.length)
                    {
                         
                         
                         
                         pw.rules("add", 
                         {
                              required: true,
                              messages: {
                                   required: 'Required'
                              }
                         });
                         
                    }
                    
               });
               if(!btn.hasClass('stay')) btn.slideDown();
               e.preventDefault();
               return;
          },
          function (e)
          {
               $(this).removeClass('open');
               p = $(this).closest('.account_type');
               $(this).text('Change Password');
               
               btn = p.find('.account_submit');
               if(!btn.hasClass('stay')) btn.hide();
               cp = p.find('.changepw');
               cp.slideUp(function ()
               {
                    pw = $(this).find('input.password');
                    
                    if(pw.length)
                    {
                         pw.rules("remove", "required");
                    }
                    cp.empty();
               });
               e.preventDefault();
               return;
          });
     }
          
     setupPwLink();
     
     
     
     jQuery.validator.setDefaults({ 
         
         rules: {
            "account[username]": {
                
                required: true
              }
          },
          messages: {
            "account[username]": {
                required: "Required"
              }
            },
       submitHandler: function (form)
       {
          loader = $(form).find('.accountloader');
          if(!loader.length)
          {
               btn = $(form).find('.account_submit');
               btn.hide();
               btn.after('<div class="loader accountloader">wait...</div>');
          }
          else
          {
               loader.css({'background': '#ccc', 'text-shadow': '1px 1px #777'});
               loader.show();
               loader.text('wait...');
          }
          
          
           var options = { 
             
             success:       function (data)
             {
               if(data.success)
               {
                    at = $(form).parent();
                    if(at.hasClass('inactive'))
                    {
                         at.removeClass('inactive').addClass('active');
                    }
                    if(data.id !== undefined && data.id) 
                    {
                         
                         $(form).find('.acc').val(data.id);
                    }
                    l = $(form).find('.accountloader');
                    $(form).find('.mugshot').html('<img src="'+data.mugshot_url+'" width=50 height=50 title="Profile Image" />');
                    l.css({background: '#A4CC35'}).text('Saved!');
                    l.fadeOut(1000, function ()
                    {
                         link = $(form).find('.currentpw a.open');
                         if(link.length)
                         {
                              link.click();
                         }
                         else
                         {
                              setupPwLink();
                         }
                         
                           
                    });
               }
               else
               {
                    
                    l = $(form).find('.accountloader');
                    l.css({background: '#ff0000', 'text-shadow': '1px 1px #770000'}).text(data.error);
                    
                    btn.show();
               }
             },
             error:       function (xml)
             {
               l = $(form).find('.accountloader').html(xml.responseText);
               btn.show();
             },
             type:      'post',        
             dataType:  'json'
      
             
           }; 
          

           $(form).ajaxSubmit(options); 
         
       }
     });
     
     $("#twitter_frm").validate({
          
        
        });
     $("#facebook_frm").validate({
          
        
        });
     $("#flickr_frm").validate({
          
        
        });
     $("#myspace_frm").validate({
          
        
        });
     $('.inactive input.password').each(function (k,v) {
          
          $(this).rules("add", 
          {
               required: true,
               messages: {
                    required: 'Required'
               }
          });
     
     });
     
     
     
     $('#domain_frm').validate({ 
         
         rules: {
            
          },
          messages: {
            
            },
       submitHandler: function (form)
       {
          btn = $(form).find('button[type=submit]');
          btn.hide();
          btn.after('<div class="loader">wait...</div>');
          
           var options = { 
             
             success:       function (data)
             {
               
               loader = $(form).find('.loader');
               if(data.success && data.success != 'false')
               {
                    
                    loader.css({color: '#A4CC35'}).text('Saved!');
                    loader.fadeOut(1000, function ()
                    {
                         btn.fadeIn();
                    });
               }
               else
               {
                    loader.css({color: 'red'}).text(data.error);
                    btn.show();
               }
               
             },
             error:       function ()
             {
               
               btn.show();
             },
             type:      'post',        
             dataType:  'json'
      
             
           }; 


           $(form).ajaxSubmit(options); 
         
       }
     });
     
     
      
      
});


function facebook_onlogin ()
{
     FB.Connect.showPermissionDialog('publish_stream,read_stream,offline_access,email',function(perms) {
        if (!perms) {
          continue_without_permission();
        } else {
          save_session();
        }
      },
      true
    );
}
function continue_without_permission ()
{
     $('#facebook .account_settings').append('Permissions rejected, but they are required.');
}
function save_session ()
{
     $('#facebook .account_settings').html('<div class="accountloader loader">Please wait...</div><div class="clear"></div>');
     FB.Connect.get_status().waitUntilReady(function(status) {
        switch(status) {
        case FB.ConnectState.connected:
          FB.Connect.getSignedPublicSessionData(function (data)
          {
               
               data.upds = true;
               $.ajax({
                    url: '/activate/facebook/',
                    'data': data,
                    dataType: 'html',
                    type: 'POST',
                    success: function (html)
                    {
                        $('#facebook .account_settings').html($(html).find('#facebook .account_settings').html());
                    },
                    error: function (xmlr, r,e)
                    {
                         alert('oops there was an error talking to the server');
                    }
               });
               
          });
          break;
        case FB.ConnectState.appNotAuthorized:
        case FB.ConnectState.userNotLoggedIn:
          // ... show facebook login button ...
          break;
        }
      });
     
}

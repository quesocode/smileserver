validation_setup = false;
function setupLoginForm ()
    {
           $('#login_password, #login_email').val('');
           $('#login_password, #login_email').unbind();
           $('#login_password, #login_email').keyup(function (e)
           {
               
               if($(this).val())
               {
                    if(!$(this).siblings('label').data('oldtxt')) $(this).siblings('label').data('oldtxt', $(this).siblings('label').text());
                    $(this).siblings('label').text('');
                    if($(this).hasClass('invalid'))
                    {
                         $(this).css('color','#fff');
                    }
               }
               else
               {
                    $(this).siblings('label').text($(this).siblings('label').data('oldtxt'));
                    $(this).siblings('label').css('color','#ccc');
                    $(this).siblings('label.err').css('color','#660000');
               }
               e.preventDefault();
           });
           $('#login_password, #login_email').change(function (e)
           {
               
               $(this).keyup();
           });
           $('#login_password, #login_email').focus(function (e)
           {
               
                    $(this).siblings('label').css('color','#ccc');
                    $(this).siblings('label.err').css('color','#660000');
               
           }).blur(function ()
           {
               $(this).siblings('label').css('color','#00658B');
               $(this).siblings('label.err').css('color','#660000');
           });
           
           if(!validation_setup)
           {
               validation_setup = true;
               login_validator = $("#login_frm").validate({
                  rules: {
                    "email": {
                        "email": true,
                        "required": true
                      },
                      "password": {
                        
                        "required": true
                      }
                  },
                    errorClass: "invalid",
                    errorPlacement: function(error, element) {
                    
                         element.siblings('label').css('background','red');
                         element.siblings('label').css('color','#660000');
                         element.siblings('label').addClass('err');
                         element.css('color','white');
                         
                  },
                    submitHandler: function (form)
                    {
                      pw = $("#login_password").val();
                      if(pw)
                      {
                        pw = $.md5(pw);
                        var options = { 
                            data: {'email': $("#login_email").val(), 'password': pw},      
                            success:       function (data)
                            {
                              $('#menu').load(window.location.href + ' #menu .container_24', {}, function ()
                              {
                                   
                                   setupLogoutLink();     
                              });
                              
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
          }
          else if(validation_setup)
          {
               validation_setup = false;
               setupLoginForm();   
          }
      
      
      
          $('#login_email').focus();
      }

function setupLogoutLink ()
{
     $('#logout').unbind().click(function (e){
          e.preventDefault();
          
          $.get('/logout/json/', {}, function ()
          {
               $('#menu').load(window.location.href + ' #menu .container_24', {}, function () {
                    setupLoginForm();
               });
               
          });
     });
}

$(document).ready(function()
{
    
    setupLoginForm();
    setupLogoutLink();    
});
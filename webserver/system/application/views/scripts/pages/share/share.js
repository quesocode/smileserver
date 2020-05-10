var loggedin = <?php echo $logged ? 'true' : 'false'; ?>;

validation_setup = false;
function setupLoginForm ()
{

          $('input[type=text], input[type=password]').keyup(function (e){
          
               label = $(this).prev('label');
               if(!label.data('text')) label.data('text', label.text());
               
               $(this).val() ? label.text('') : label.text(label.data('text'));
               e.preventDefault();
          
          }).focus(function (e)
          {
               label = $(this).prev('label');
               if(!label.data('text')) label.data('text', label.text());
               
               $(this).val() ? label.text('').css('color','#ddd') : label.text(label.data('text')).css('color','#ddd');
               e.preventDefault();
          }).blur(function (e)
          {
               label = $(this).prev('label');
               if(!label.data('text')) label.data('text', label.text());
               
               $(this).val() ? label.text('').css('color','#aaa') : label.text(label.data('text')).css('color','#aaa');
               e.preventDefault();
          });
          
          
          $('#loginbtn').click(function (e)
          {
               e.preventDefault();
               $('fieldset#cpass').remove();
               $(this).removeClass('inactive').addClass('active');
               $('#signupbtn').removeClass('active').addClass('inactive');
               showNotLoggedIn();
               
          });
          /*
$('#signupbtn').click(function (e)
          {
               e.preventDefault();
               if(!$('fieldset#cpass').length)
               {
                    $('#login #fields').append('<fieldset id=cpass><label for="confirm">Confirm</label><input type=password id=confirm name=confirm /></fieldset>');
               };
               $(this).removeClass('inactive').addClass('active');
               $('#loginbtn').removeClass('active').addClass('inactive');
               showSignUp();
               
          });
*/
           
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
                      pw = $("#password").val();
                      if(pw)
                      {
                         em = $("#email").val();
                        pw = $.md5(pw);
                        console.log(pw);
                        console.log(em);
                        var options = { 
                            data: {'email': em, 'password': pw},      
                            success:       function (data)
                            {
                             
                              window.location.href = window.location.href;
                              
                            },
                            error:       function (xml, e, t)
                            {
                              $("#password").val('');
                              
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

function showNotLoggedIn ()
{
     $('#instructions h4').text('You aren\'t logged in');
     $('#instructions p').html('Before you can plug this to any of your social connections, you must <a href="#login" class="login_link">login</a>. <br /><br />If you don\'t have an account, you can <a href="#signup" class="signup_link">sign up</a> for one.');
}

function showSignUp ()
{
     $('#instructions h4').text('Why Sign Up?');
     $('#instructions p').html("<ul><li>It takes 5 seconds... why not?</li><li>With a click of the plug button, you can share stuff you like on Twitter, Facebook, Flickr, Myspace, and more... it's magic</li><li>Plug your stuff so your friends plug it, then their friends plug it, and before you know it... you're a rockstar</li></ul>");
}

$(document).ready(function(){

     
     setupLoginForm();
     setupLogoutLink(); 
     
     
     $('#plug').click(function (e)
     {
          e.preventDefault();
          
          if(loggedin)
          {
               // plug the content
               $('#plug_frm').submit();
          }
          else
          {
               $('.loggedout').show();
               showNotLoggedIn();
          }
          
          
     });
     
     $('input[type=checkbox]').click(function (e)
     {
          
          console.log($(this).attr('checked'));
          if($(this).is(':checked'))
          {
               $(this).siblings().css('opacity', 1);
               $(this).attr('checked',true);
          }
          else
          {
               $(this).siblings().css('opacity', 0.3);
               $(this).removeAttr('checked');
          }
          //e.preventDefault();
          
     });
     
     $('#plug_frm').validate({
     
     
          submitHandler: function (form)
          {
              connections = '';
              $(form).find('input:checked').each(function ()
              {
                    connections += $(this).val() + ',';
              });
              connections = connections.substr(0, connections.length-1);
              data = {'connections':connections};
              data = getFormValues('#plug_frm',data);
              
              var options = { 
                  data: data,      
                  success:       function (data)
                  {
                    console.log(data);
                    alert('its been plugged');
                    
                  },
                  error:       function (xml, e, t)
                  {
                    alert('there was an error');
                    console.log(xml);
                    console.log(e);
                    console.log(t);
                    $('#sharedcontent').append(xml.responseText);
                  },
                  type:      'post',        
                  dataType:  'json'
           
                  
              }; 
               
     
              $(form).ajaxSubmit(options); 
            
     
          }
     
     
     
     });
     
     
     
     

});


function getFormValues (formsel, extend_obj)
{
	// get all the inputs into an array.
    var $inputs = $(formsel + ' :input');

    // not sure if you wanted this, but I thought I'd add it.
    // get an associative array of just the values.
    var values = {};
    $inputs.each(function() {
        values[this.name] = $(this).val();
    });
    if(extend_obj && typeof extend_obj == 'object')
    {
    	$.extend(values, extend_obj);
    }
    return values;
}


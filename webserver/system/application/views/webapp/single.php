<?php

$imagesize = getimagesize($file);
$imagewidth = $imagesize[0];
$imageheight = $imagesize[1];

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		
		<title><?php echo($eventName); ?></title>
    <style type="text/css">
      <?php echo isset($css) ? $css : NULL; ?>
      
      
    </style>
    <script type="text/javascript">
function aspectFit(rect,maxRect)
{
	var originalAspectRatio = rect.w / rect.h;
	var maxAspectRatio = maxRect.w / maxRect.h;
  
	var newRect = {w: maxRect.w, h: maxRect.h, x: maxRect.x, y: maxRect.y};
	if (originalAspectRatio > maxAspectRatio) { // scale by width
	 console.log('scale by width');
		newRect.h = maxRect.w * rect.h / rect.w;
		newRect.y += (maxRect.h - newRect.h)/2.0;
	} else {
	 console.log('scale by height');
		newRect.w = maxRect.h  * rect.w / rect.h;
		newRect.x += (maxRect.w - newRect.w)/2.0;
	}
  newRect.x = parseInt(newRect.x);
  newRect.y = parseInt(newRect.y);
  newRect.w = parseInt(newRect.w);
  newRect.h = parseInt(newRect.h);
	return newRect;
}

      <?php echo isset($js) ? $js : NULL; ?>




  function centerPhoto() {
      var bWidth = $(window).width()-20;
      var bHeight = $(window).height()-160-20;
      var pHeight = $('#singlePhoto img').height();
      var pWidth = $('#singlePhoto img').width();
      var sHeight = $('#singlePhoto').height();
      var sWidth = $('#singlePhoto').width();
      
      if(pHeight == 0){
          pHeight = <?php echo($imageheight); ?>;
      }
      if(pWidth == 0)
      {
        pWidth = <?php echo($imagewidth); ?>;
      }
      var newHeight = (pHeight)/2;
      newHeight = '-'+newHeight +'px';
      
      var newWidth = (pWidth)/2;
      
      newWidth = '-'+newWidth +'px';
      var intrect = {'w': pWidth, 'h': pHeight, 'x':0, 'y': 0};
      var maxrect = {'w': bWidth, 'h': bHeight, 'x':0, 'y': 0};
      aspect = aspectFit(intrect, maxrect);
      $('#singlePhoto img').css({'height': aspect.h, 'width': aspect.w, 'margin-top': aspect.h/2*-1, 'margin-left': aspect.w/2*-1});
      
      
  };
  
  $(window).resize(function () {
      centerPhoto();
  }); 
  
	$(document).ready(function() {
		$('#singlePhoto').click(function() {
  			$('.control').fadeToggle();
		});
		
		$('#goBack').click(function() {
  			history.back();
			return false;

		});
		
		$('#email').click(function(e){
			e.preventDefault();
			$('#emailBox, #overlay').fadeIn('fast');
			
		});
		
		$('#mms').click(function(e){
			e.preventDefault();
			$('#mmsBox, #overlay').fadeIn('fast');
		});
		
		$('#tweet').click(function(e){
			e.preventDefault();
			$('#tweetBox, #overlay').fadeIn('fast');
		});
		
		$('.addExtension').click(function(e){
			e.preventDefault();
			$('#destination_email').val($('#destination_email').val() + $(this).html());
		});
		
		$('.addNumber').click(function(e){
			e.preventDefault();
			$('#mmsInput').val($('#mmsInput').val() + $(this).html());
		});
		
		$('#emailBox #emailInputWrapper #clearBtn').click(function(e){
			e.preventDefault();
			$('#destination_email').val('');
			
		});
		
		$('#mmsBox #clearBtn').click(function(e){
			e.preventDefault();
			$('#mmsInput').val('');
			
		});
		
		$('#tweetBox #clearBtn').click(function(e){
			e.preventDefault();
			$(this).prev().val('');
			
		});
				
		$('#emailBox #closeBtn').click(function(e){
			e.preventDefault();
			$('#emailBox, #overlay').fadeOut('fast');
		});
		
		$('#mmsBox #closeBtn').click(function(e){
			e.preventDefault();
			$('#mmsBox, #overlay').fadeOut('fast');
		});
		
		$('#tweetBox #closeBtn').click(function(e){
			e.preventDefault();
			$('#tweetBox, #overlay').fadeOut('fast');
		});
		
		$('#tweetBox2 #closeBtn').click(function(e){
			e.preventDefault();
			$('#tweetBox2, #overlay').fadeOut('fast');
		});
		
		$('#twitterForm1').submit(function (e) {
		  $('#twitterForm1Submit').attr('disabled', true).val('...validating...');
		  $('#twitterForm1Submit').css({'opacity':'.5'});
		  var twUser = $('#twitterUser').val();
		  var twPass = $('#twitterPassword').val();
		  var twurl = $('#twitter_file_url').val();
		  
		  var twCreds = [];
		  twCreds['method'] = 'twAuth';
		  twCreds['twUser'] = twUser;
		  twCreds['twPass'] = twPass;
		  twCreds['twurl'] = twurl;
		  
		  var result = $.get("<?php echo $base; ?>?method=twauth&twuser="+twUser+"&twpass="+twPass+'&twurl='+twurl, twCreds, function (data) {
		      var resultData = $.parseJSON(data);
		      if(resultData.success===true)
		      {
		          //move to TW step two
		          //set hidden inputs with tokens
		            $('#myTweet_t1').val(resultData.data.t1);
		            $('#myTweet_t2').val(resultData.data.t2);
		            $('#mySlugHash').val(resultData.data.twurl)
		          //set textarea
		            $('#myTweet').val('Just got my @smilebooth photo taken at the googledcparty http://thmb.it/smileapp/' + resultData.data.twurl);
		            $('#tweeter').html(resultData.data.screen_name);
		          $('#tweetBox').fadeOut('fast');
		          $('#twitterForm1Submit').attr('disabled',false).val('send');
        		  $('#twitterForm1Submit').css({'opacity':1});
		          $('#tweetBox2').fadeIn('fast');
		          $('#twitterUser').val('');
	            $('#twitterPassword').val('');
		      }
		      else
		      {
		          $('#twitterForm1Submit').attr('disabled',false).val('send');
        		  $('#twitterForm1Submit').css({'opacity':1});
		          $('#twitterPassword').val('');
		          alert('Twitter did not recognize your Username and Password.  Please try again');
		      }
		  });
		  e.preventDefault();
		  return false;
		});
		
		$('#twitterForm2').submit(function (e) {
		    $('#twitterForm2Submit').attr('disabled', true).val('...tweeting...');
		    $('#twitterForm2Submit').css({'opacity':'.5'});
		    var t1 = $('#myTweet_t1').val();
		    var t2 = $('#myTweet_t2').val();
		    var tw = $('#myTweet').val();
		    tw = tw.replace('#', 'ggggHASHgggg');
		    var slughash = $('#mySlugHash').val();
		    var file_path = $('#tw_file_path').val();
		    

		    var thedata = [];
		    var theurl = encodeURI("<?php echo $base; ?>?method=tweet&t1="+t1+"&t2="+t2+"&slughash="+slughash+"&file_path="+file_path+"&tw="+tw);
		    console.log(theurl);
		    var result = $.get(theurl, thedata, function (data) {
		        var resultData = $.parseJSON(data);
		        console.log(resultData);
		        if(resultData.success === true)
		        {
		            $('#twitterForm2Submit').attr('disabled',false).val('send');
          		  $('#twitterForm2Submit').css({'opacity':1});
		            $('#twitterUser').val('');
		            $('#twitterPassword').val('');
		            $('#tweeter').val('Not Logged In');
		            $('#myTweet_t1').val('');
		            $('#myTweet_t2').val('');
		            $('#myTweet').val('');
		            $('#tweetBox2, #overlay').fadeOut('fast');
		            window.location = '<?php echo $twitterSuccessURL; ?>';
		        }
		        else
		        {
		            $('#twitterUser').val('');
		            $('#twitterPassword').val('');
		            $('#tweeter').val('Not Logged In');
		            $('#myTweet_t1').val('');
		            $('#myTweet_t2').val('');
		            $('#myTweet').val('');
		            $('#tweetBox2').fadeOut('fast');
		            $('#tweetBox').fadeIn('fast');
		            $('#twitterForm2Submit').attr('disabled',false).val('send');
          		  $('#twitterForm2Submit').css({'opacity':1});
		            alert('There was an error sending your Tweet.');
		        }
		    });
		    
		    
		    e.preventDefault();
		    return false;
		});
		
		$(window).resize();
		
		//setInterval(function() { $(window).resize(); }, 3000);
				
	});
</script>
	</head>
	<body>
	
	<div id="wrapper">
		<div id="overlay">
		
		</div>
		<div id="topControls" class="control">
			<a href="#" id="goBack">Back</a>
		</div>
		<div id="bottomControls" class="control">
			<div id="controlWrapper">
				<a href="#" id="email">email</a>
				<a href="#" id="mms">mms</a>
				<a href="#" id="tweet">tweet</a>
			</div>
		</div>
		<div id="singlePhoto">
		  <center>
  			<img src="<?php echo SMILESERVER; ?>app/<?php echo($filename); ?>.jpg?method=thumbnail&path=<?php echo($file); ?>&quality=<?php echo($quality); ?>&width=<?php echo($width); ?>&height=<?php echo($height); ?>&tag=<?php echo($tag); ?>" id="photo" />
			</center>
			
		</div>
	</div><!--end #wrapper-->
	<div id="emailBox" class="modal">
		<div class="modalHeader">
			<div class="close">
				<a href="#" id="closeBtn">Close</a>
			</div>
			<h2>What's your email address?</h2>
		</div>
		<div class="modalBody">
			<div class="modalInput">
				<form action="<?php echo $base; ?>" id="emailForm">
					<div id="emailInputWrapper">
						<input type="email" id="destination_email" name="destination" autocomplete="off">
						<a href="#" id="clearBtn">Clear</a>
					</div>
					<div class="buttonHolder">
					
						<a href="#" id="addGmail" class="addExtension">@gmail.com</a>
						<a href="#" id="addMe" class="addExtension">@me.com</a>
						<a href="#" id="addHotmail" class="addExtension">@hotmail.com</a>
					</div>
					  <input type="hidden" value="<?php echo $livemode; ?>" name="share" />
						<input type="hidden" value="smilebooth" name="username" />
						<input type="hidden" value="share_redirect" name="method" />
						<input type="hidden" value="email" name="device" />
						<input type="hidden" value="<?php echo $emailTemplate; ?><?php echo $emailMessage; ?>" name="message" />
						<input type="hidden" value="<?php echo $emailFrom; ?>" name="from" />
						<input type="hidden" value="<?php echo $emailSubject; ?>" name="subject" />
						<input type="hidden" value="<?php echo $eventName; ?>" name="code" />
						<input type="hidden" value="<?php echo $emailAttachment; ?>" name="file_url" />
						<input type="hidden" value="<?php echo $redirect; ?>" name="redirect" />
					<input type="submit" value="Send" id="emailSubmit">
				</form>
			</div>
		</div>
	</div><!--end #emailBox-->
	
	<div id="mmsBox" class="modal">
		<div class="modalHeader">
			<div class="close">
				<a href="#" id="closeBtn">Close</a>
			</div>
			<h2>Type Phone Number</h2>
		</div>
		<div class="modalBody">
			<div class="modalInput">
				<form action="<?php echo $base; ?>" id="mmsForm">
					<input type="phone" id="mmsInput" name="destination" autocomplete="off">
					<div class="buttonHolder">
						<a href="#" id="one" class="addNumber">1</a>
						<a href="#" id="two" class="addNumber">2</a>
						<a href="#" id="three" class="addNumber">3</a>
						<a href="#" id="four" class="addNumber">4</a>
						<a href="#" id="five" class="addNumber">5</a>
						<a href="#" id="six" class="addNumber">6</a>
						<a href="#" id="seven" class="addNumber">7</a>
						<a href="#" id="eight" class="addNumber">8</a>
						<a href="#" id="nine" class="addNumber">9</a>
						<input type="button" id="clearBtn" class="dialpad" value="Clear">
						<a href="#" id="zero" class="addNumber">0</a>
						  <input type="hidden" value="<?php echo $livemode; ?>" name="share" />
  						<input type="hidden" value="smilebooth" name="username" />
  						<input type="hidden" value="share_redirect" name="method" />
  						<input type="hidden" value="mms" name="device" />
  						<input type="hidden" value="<?php echo $mmsMessage; ?>" name="message" />
  						<input type="hidden" value="<?php echo $eventName; ?>" name="code" />
  						<input type="hidden" value="<?php echo $emailAttachment; ?>" name="file_url" />
  						<input type="hidden" value="<?php echo $redirect; ?>" name="redirect" />

						
						<input type="submit" value="Send" id="mmsSubmit">
					</div>
				</form>
			</div>
		</div><!--end #mmsBox-->
	</div>	
	<div id="tweetBox" class="modal">
		<div class="modalHeader">
			<div class="close">
				<a href="#" id="closeBtn">Close</a>
			</div>
			<h2>Sign In</h2>
		</div>
		<div class="modalBody">
			<div class="modalInput">
				<form id="twitterForm1">
					<div class="twitterInputWrapper">
						<input id="twitterUser" name="twitterUser" class="twitterInput" type="text" placeholder="twitter account" autocomplete="off">
						<a href="#" id="clearBtn">Clear</a>					
					</div>
					<div class="twitterInputWrapper">
						<input id="twitterPassword" name="twitterPassword" class="twitterInput" type="password" placeholder="password">
						<a href="#" id="clearBtn">Clear</a>					
					</div>
					<input type="hidden" value="<?php echo $emailAttachment; ?>" id="twitter_file_url" name="file_url" />
					<input type="submit" value="Send" id="twitterForm1Submit">
					<small>We respect privacy, so we never store passwords.</small>
				</form>
			</div>
		</div>
	</div><!--end #twitterBox-->
	<div id="tweetBox2" class="modal">
		<div class="modalHeader">
			<div class="close">
				<a href="#" id="closeBtn">Close</a>
			</div>
			<h2 id="tweeter">username</h2>
		</div>
		<div class="modalBody">
			<div class="modalInput">
				<form id="twitterForm2">
					<div class="twitterTextAreaWrapper">
					  <textarea id="myTweet" name="myTweet" class="twitterBigInput"></textarea>
					  <input id="myTweet_t1" name="myTweet_t1" type="hidden" value="" />
					  <input id="myTweet_t2" name="myTweet_t2" type="hidden" value="" />
					  <input type="hidden" value="" id="mySlugHash" name="mySlugHash" />
					  <input type="hidden" value="<?php echo urlencode($file); ?>" id="tw_file_path" name="tw_file_path" />
					</div>
					<input type="submit" value="Send Tweet" id="twitterForm2Submit">
				</form>
			</div>
		</div>
	</div><!--end #twitterBox-->
  <script type="text/javascript">
      //$(window).resize();
      centerPhoto();
  </script>
	</body>
</html>

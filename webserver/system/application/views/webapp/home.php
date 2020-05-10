<?php 
$show_msg = isset($show_msg) ? $show_msg : false; 

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name = "viewport" content = "width = device-width,
       initial-scale = 1.0, user-scalable = no">
		<? if($refresh && $refresh!='no') { ?><META HTTP-EQUIV=Refresh CONTENT="<? echo($refreshTime); ?>;url=<? echo($refreshURL); ?>"><? } ?>
		<title><?php echo($eventName); ?></title>
    <style type="text/css">
      <?php echo isset($css) ? $css : NULL; ?>
      .photoThumb a
      {
        width: 150px;
        height: 150px;
        
      }
      .photoThumb a img
      {
        max-width: 150px;
        max-height: 150px;
        vertical-align: middle;
        border: 5px solid white;
      }


.photoThumb a * {
    vertical-align: middle;
}

.photoThumb a
{
    display: table-cell !important;
    text-align: center;
    vertical-align: middle;
}


/* IE/Mac \*//*/
* html .photoThumb a {
	display: block;
	line-height: 0.6;
}
* html .photoThumb a span {
	display: inline-block;
	height: 100%;
	width: 1px;
}
/**/
</style>
<!--[if lt IE 8]><style>
.photoThumb a span {
    display: inline-block;
    height: 100%;
}
</style><![endif]-->
    <script type="text/javascript">
      <?php echo isset($js) ? $js : NULL; ?>
    
    	$(document).ready(function() {
    		
    		<?php if($show_msg===true) { ?>
    		$('#messageBox, #overlay').show();
    		<? } ?>
    		
    		<?php if($show_tw_msg===true) { ?>
    		$('#message').html('Your Tweet was successful<br />Thanks for sharing!');
    		$('#messageBox, #overlay').show();
    		<? } ?>
    		
    		$('#messageBox #closeBtn').click(function(e){
    			e.preventDefault();
    			$('#messageBox, #overlay').fadeOut('fast');
    		});
    	});
    </script>
	</head>
	<body>
	
	<div id="wrapper">
  	<div id="overlay">
		
		</div>
		<header>
			<h1><? echo($eventName); ?></h1>
				<a href="<? echo($refreshURL); ?>" id="refreshBtn">Refresh</a>
		
		</header>
		<div id="gallery">
		  
			<?php if(isset($photos)) : foreach($photos as $photo) : ?>
			
			
			<div class="photoThumb">
	 			<a href="<?php echo $photo['view_url'];?>" target="_self" style=""><span></span><img src="<?php echo $photo['src_thumb']; ?>" /></a>
			</div>
			<?php endforeach; ?>
			<?php else : ?>	

				<h2>No records were returned.</h2>

			<?php endif; ?>
		</div>
	</div><!--end #wrapper-->
	<div id="messageBox" class="modal">
		<div class="modalHeader">
			<div class="close">
				<a href="#" id="closeBtn">Close</a>
			</div>
			<h2>Thank You</h2>
		</div>
		<div class="modalBody">
			<div class="modalInput">
				<center>
				  <h1 id="message">You will receive your message<br />within 24 hours.</h1>
				</center>
			</div>
		</div>
	</div><!--end #messageBox-->
	
	</body>
</html>

<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('addText'))
{
	function addText($string='Text goes here', $fontsize=12.0, $fontcolor=array(255,255,255), $angle=0, $font=NULL, $image=NULL, $image_width=NULL, $image_height=NULL, $bgcolor = array(37,37,37))
	{
	     $font = is_null($font) ? BASEPATH . "fonts" . DIRECTORY_SEPARATOR . "arial.ttf";
		// create a bounding box for the text
          $dims = imagettfbbox($fontsize, $angle, $font, $quote);
          
          // make some easy to handle dimension vars from the results of imagettfbbox
          // since positions aren't measures in 1 to whatever, we need to
          // do some math to find out the actual width and height
          $width = $dims[4] - $dims[6]; // upper-right x minus upper-left x 
          $height = $dims[3] - $dims[5]; // lower-right y minus upper-right y
          
          // Create image or use existing image
          if(is_null($image))
          {
               $image_width = is_null($image_width) ? $width : $image_width;
               $image_height = is_null($image_height) ? $height : $image_height;
               $image = imagecreatetruecolor($image_width,$image_height);
               
               
               // pick color for the background
               $bgcolor_img = imagecolorallocate($image, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
               
               // fill in the background with the background color
               imagefilledrectangle($image, 0, 0, $image_width, $image_height, $bgcolor_img);
               
               
          }
          
          // pick color for the text
          $fontcolor_img = imagecolorallocate($image, $fontcolor[0], $fontcolor[1], $fontcolor[2]);
          
          
          // x,y coords for imagettftext defines the baseline of the text: the lower-left corner
          // so the x coord can stay as 0 but you have to add the font size to the y to simulate
          // top left boundary so we can write the text within the boundary of the image
          $x = 0; 
          $y = $fontsize;
          imagettftext($image, $fontsize, 0, $x, $y, $fontcolor_img, $font, $string);
          
          return $image;
          
          
	}
	
	
	
	
}
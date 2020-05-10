<?php



/**
 * class txp_thumb: wrapper for wet_thumb interfacing the TxP repository
 */
class Thumbnail extends Thumbnail_Base {

    

}

class Thumb extends Thumbnail
{

}


/**
 * class wet_thumb
 * @author	C. Erdmann
 * @see		<a href="http://www.cerdmann.de/thumb">http://www.cerdmann.de/thumb</a>
 * @author	Robert Wetzlmayr
 *
 * refactored from function.thumb.php by C. Erdmann, which contained the following credit & licensing terms:
 * ===
 * Smarty plugin "Thumb"
 * Purpose: creates cached thumbnails
 * Home: http://www.cerdmann.com/thumb/
 * Copyright (C) 2005 Christoph Erdmann
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
 * -------------------------------------------------------------
 * Author:   Christoph Erdmann (CE) <smarty@cerdmann.com>
 * Internet: http://www.cerdmann.com
 *
 * Author: Benjamin Fleckenstein (BF)
 * Internet: http://www.benjaminfleckenstein.de
 *
 * Author: Marcus Gueldenmeister (MG)
 * Internet: http://www.gueldenmeister.de/marcus/
 *
 * Author: Andreas BÃ¶sch (AB)
 *
 */

/*
$HeadURL$
$LastChangedRevision$
*/

$verbose = false;


class Thumbnail_Base {
    var $width;      // The width of your thumbnail. The height (if not set) will be automatically calculated.
    var $height;	// The height of your thumbnail. The width (if not set) will be automatically calculated.
    var $longside;	// Set the longest side of the image if width, height and shortside is not set.
    var $shortside;	// Set the shortest side of the image if width, height and longside is not set.
    var $extrapolate;  // Set to 'false' if your source image is smaller than the calculated thumb and you do not want the image to get extrapolated.
    var $crop;	// If set to 'true', image will be cropped in the center to destination width and height params, while keeping aspect ratio. Otherwise the image will get resized.
    var $sharpen;	// Set to 'false' if you don't want to use the Unsharp-Mask. Thumbnail creation will be faster, but quality is reduced.
    var $hint; 	// If set to 'false' the image will not have a lens-icon.
    var $addgreytohint; // Set to 'false' to get no lightgrey bottombar.
    var $quality;	// JPEG image quality (0...100, defaults to 80).
    var $keepsize; // this will make it so the rendered image keeps the same proportions
    
    // unsharp tweaks
  	var $unsharp_amount;
  	var $unsharp_radius;
  	var $unsharp_threshold;
    
    // link related params
    var $linkurl;    // Set to your target URL (a href="linkurl")
    var $html;       // Will be inserted in the image-tag

    var $types = array('','.gif','.jpg','.png');
    var $_SRC;
    var $_DST;

    /**
     * constructor
     */
    function __construct(  ) {
    	$this->max = false;
		$this->extrapolate = false;
		$this->unsharp_amount = 80;
		$this->unsharp_radius = .5;
		$this->unsharp_threshold = 3;
		$this->crop = true;
		$this->sharpen = true;
		$this->hint = false;
		$this->textOptions = array();
		$this->text = false;
		$this->addgreytohint = false;
		$this->quality = 80;
		$this->keepsize = false;
		$this->html = " alt=\"\" title=\"\" ";
		$this->link = true;
    }
	 function check_jpeg($f, $fix=false ){
		# [070203]
		# check for jpeg file header and footer - also try to fix it
		    if ( false !== (@$fd = fopen($f, 'r+b' )) ){
		        if ( fread($fd,2)==chr(255).chr(216) ){
		            fseek ( $fd, -2, SEEK_END );
		            if ( fread($fd,2)==chr(255).chr(217) ){
		                fclose($fd);
		                return true;
		            }else{
		                if ( $fix && fwrite($fd,chr(255).chr(217)) ){return true;}
		                fclose($fd);
		                return false;
		            }
		        }else{fclose($fd); return false;}
		    }else{
		        return false;
		    }
		}
	function imagecreatefrompsd($fileName) { 
	    $psdReader = new Thumbnail_Psd($fileName); 
	    if (isset($psdReader->infoArray['error'])) return ''; 
	    else return $psdReader->getImage(); 
	} 
    /**
     * write thumbnail file
     * @param	infile	image file name
     * @param	outfile	array of thumb file names (1...n)
     * @return	boolean, true indicates success
     */
    
    function write( $infile, $outfile ) {

        global $verbose;

        //if( $verbose )echo "writing thumb nail...";

		### fetch source (SRC) info
		$temp = getimagesize($infile);
		
	
		$this->_SRC['file']		= $infile;
		$this->_SRC['width']		= $temp[0];
		$this->_SRC['height']		= $temp[1];
		$this->_SRC['type']		= $temp[2]; // 1=GIF, 2=JPG, 3=PNG, SWF=4
		$this->_SRC['string']		= $temp[3];
		$this->_SRC['filename'] 	= basename($infile);
		//$this->_SRC['modified'] 	= filemtime($infile);
		
		if(($this->_SRC['type'] > 3 && $this->_SRC['type'] != 5)  || !isset($this->_SRC['type']))
		{
			
			return;
			
			
		}
		
		if($this->keepsize)
		{
			
			$this->width = $this->_SRC['width'];
			$this->height = $this->_SRC['height'];
			$_max = $this->max;
			if($_max && ($this->width > $_max || $this->height > $_max))
			{
				if($this->width > $_max)
				{
					$p = $_max / $this->width ;
					$this->width = $_max;
					$this->height = round($this->height * $p);
				}
				else
				{
					$p = $_max / $this->height ;
					$this->height = $_max;
					$this->width = round($this->width * $p);
				}
			}
			
		}
		//check image orientation
		if ($this->_SRC['width'] >= $this->_SRC['height']) {
		    $this->_SRC['format'] = 'landscape';
		} else {
		    $this->_SRC['format'] = 'portrait';
		}
	
		### fetch destination (DST) info
		if (is_numeric($this->width) AND empty($this->height)) {
			$this->_DST['width']	= $this->width;
			$this->_DST['height']	= round($this->width/($this->_SRC['width']/$this->_SRC['height']));
		}
		elseif (is_numeric($this->height) AND empty($this->width)) {
			$this->_DST['height']	= $this->height;
			$this->_DST['width']	= round($this->height/($this->_SRC['height']/$this->_SRC['width']));
		}
		elseif (is_numeric($this->width) AND is_numeric($this->height)) {
			$this->_DST['width']	= $this->width;
			$this->_DST['height']	= $this->height;
		}
		elseif (is_numeric($this->longside) AND empty($this->shortside)) {
		    // preserve aspect ratio based on provided height
		    if ($this->_SRC['format'] == 'portrait') {
			$this->_DST['height']	= $this->longside;
			$this->_DST['width']	= round($this->longside/($this->_SRC['height']/$this->_SRC['width']));
		    }
		    else {
			$this->_DST['width']	= $this->longside;
			$this->_DST['height']	= round($this->longside/($this->_SRC['width']/$this->_SRC['height']));
		    }
	        }
		elseif (is_numeric($this->shortside)) {
		    // preserve aspect ratio based on provided width
		    if ($this->_SRC['format'] == 'portrait') {
			$this->_DST['width']	= $this->shortside;
			$this->_DST['height']	= round($this->shortside/($this->_SRC['width']/$this->_SRC['height']));
		    }
		    else {
			$this->_DST['height']	= $this->shortside;
			$this->_DST['width']	= round($this->shortside/($this->_SRC['height']/$this->_SRC['width']));
		    }
	        }
	        else { // default dimensions
	            $this->width = 100;
	            $this->_DST['width'] = $this->width;
	            $this->_DST['height'] = round($this->width/($this->_SRC['width']/$this->_SRC['height']));
	        }
	
	
		// don't make the new image larger than the original image
		if ($this->extrapolate === false && $this->_DST['height'] > $this->_SRC['height'] &&
						    $this->_DST['width'] > $this->_SRC['width']) {
		    $this->_DST['width'] = $this->_SRC['width'];
		    $this->_DST['height'] = $this->_SRC['height'];
		}
	
		$this->_DST['type'] = $this->_SRC['type'];
		$this->_DST['file'] = $outfile;
	
		// make sure we have enough memory if the image is large
		//if (max($this->_SRC['width'], $this->_SRC['height']) > 1024)
			// this won't work on all servers but it's worth a try
			//ini_set('memory_limit', EXTRA_MEMORY);
		
		
		
		// read SRC
		if ($this->_SRC['type'] == 1)
		{
			if($gif = imagecreatefromgif($this->_SRC['file']))
			{
				$this->_SRC['image'] = $gif;
			}
		}
		elseif ($this->_SRC['type'] == 2)
		{
			if(!$this->check_jpeg($infile, true))
			{
				return;
			}
			ini_set("gd.jpeg_ignore_warning", true);
			if($jpg = @imagecreatefromjpeg($this->_SRC['file']))
			{
				$this->_SRC['image'] = $jpg;
			}
			else
			{
				return;
			}
		}
		elseif ($this->_SRC['type'] == 3)
		{
			if($png = imagecreatefrompng($this->_SRC['file']))
			{
				$this->_SRC['image'] = $png;
			}
			else
			{
				return;
			}
		}
		elseif ($this->_SRC['type'] == 5)
		{
			if($psd = imagecreatefrompsd($this->_SRC['file']))
			{
				$this->_SRC['image'] = $psd;
				$this->_SRC['psd'] = true;
			}
			else
			{
				return;
			}
		}
		else
		{
			return;
		}
	
		// crop image?
		$off_w = 0;
		$off_h = 0;
		if($this->crop != false) {
		    if($this->_SRC['height'] < $this->_SRC['width']) {
			$ratio = (double)($this->_SRC['height'] / $this->_DST['height']);
			$cpyWidth = round($this->_DST['width'] * $ratio);
			if ($cpyWidth > $this->_SRC['width']) {
			    $ratio = (double)($this->_SRC['width'] / $this->_DST['width']);
			    $cpyWidth = $this->_SRC['width'];
			    $cpyHeight = round($this->_DST['height'] * $ratio);
			    $off_w = 0;
			    $off_h = round(($this->_SRC['height'] - $cpyHeight) / 2);
			    $this->_SRC['height'] = $cpyHeight;
			}
			else {
			    $cpyHeight = $this->_SRC['height'];
			    $off_w = round(($this->_SRC['width'] - $cpyWidth) / 2);
			    $off_h = 0;
			    $this->_SRC['width']= $cpyWidth;
			}
		    }
		    else {
			$ratio = (double)($this->_SRC['width'] / $this->_DST['width']);
			$cpyHeight = round($this->_DST['height'] * $ratio);
			if ($cpyHeight > $this->_SRC['height']) {
			    $ratio = (double)($this->_SRC['height'] / $this->_DST['height']);
			    $cpyHeight = $this->_SRC['height'];
			    $cpyWidth = round($this->_DST['width'] * $ratio);
			    $off_w = round(($this->_SRC['width'] - $cpyWidth) / 2);
			    $off_h = 0;
			    $this->_SRC['width']= $cpyWidth;
			}
			else {
			    $cpyWidth = $this->_SRC['width'];
			    $off_w = 0;
			    $off_h = round(($this->_SRC['height'] - $cpyHeight) / 2);
			    $this->_SRC['height'] = $cpyHeight;
			}
		    }
		}
	
		// ensure non-zero height/width
		if (!$this->_DST['height']) $this->_DST['height'] = 1;
		if (!$this->_DST['width'])  $this->_DST['width']  = 1;
	
		// create DST
		$this->_DST['image'] = imagecreatetruecolor($this->_DST['width'], $this->_DST['height']);
		imagecopyresampled($this->_DST['image'], $this->_SRC['image'], 0, 0, $off_w, $off_h, $this->_DST['width'], $this->_DST['height'], $this->_SRC['width'], $this->_SRC['height']);
		if ($this->sharpen === true) {
		    $this->_DST['image'] = UnsharpMask($this->_DST['image'],$this->unsharp_amount,$this->unsharp_radius,$this->unsharp_threshold);
		}
	
	        // finally: the real dimensions
	        $this->height =  $this->_DST['height'];
	        $this->width =  $this->_DST['width'];
	
		// add magnifying glass?
		if ( $this->hint === true) {
		    // should we really add white bars?
		    if ( $this->addgreytohint === true ) {
			$trans = imagecolorallocatealpha($this->_DST['image'], 255, 255, 255, 25);
			imagefilledrectangle($this->_DST['image'], 0, $this->_DST['height']-9, $this->_DST['width'], $this->_DST['height'], $trans);
		    }
	
		    $magnifier = imagecreatefromstring(gzuncompress(base64_decode("eJzrDPBz5+WS4mJgYOD19HAJAtLcIMzBBiRXrilXA1IsxU6eIRxAUMOR0gHkcxZ4RBYD1QiBMOOlu3V/gIISJa4RJc5FqYklmfl5CiGZuakMBoZ6hkZ6RgYGJs77ex2BalRBaoLz00rKE4tSGXwTk4vyc1NTMhMV3DKLUsvzi7KLFXwjFEAa2svWnGdgYPTydHEMqZhTOsE++1CAyNHzm2NZjgau+dAmXlAwoatQmOld3t/NPxlLMvY7sovPzXHf7re05BPzjpQTMkZTPjm1HlHkv6clYWK43Zt16rcDjdZ/3j2cd7qD4/HHH3GaprFrw0QZDHicORXl2JsPsveVTDz//L3N+WpxJ5Hff+10Tjdd2/Vi17vea79Om5w9zzyne9GLnWGrN8atby/ayXPOsu2w4quvVtxNCVVz5nAf3nDpZckBCedpqSc28WTOWnT7rZNXZSlPvFybie9EFc6y3bIMCn3JAoJ+kyyfn9qWq+LZ9Las26Jv482cDRE6Ci0B6gVbo2oj9KabzD8vyMK4ZMqMs2kSvW4chz88SXNzmeGjtj1QZK9M3HHL8L7HITX3t19//VVY8CYDg9Kvy2vDXu+6mGGxNOiltMPsjn/t9eJr0ja/FOdi5TyQ9Lz3fOqstOr99/dnro2vZ1jy76D/vYivPsBoYPB09XNZ55TQBAAJjs5s</body>")));
		    imagealphablending($this->_DST['image'], true);
		    imagecopy($this->_DST['image'], $magnifier, $this->_DST['width']-15, $this->_DST['height']-14, 0, 0, 11, 11);
		    imagedestroy($magnifier);
		}
	     
	     
	     // add text to image?
	     if($this->text)
	     {
	         $image_width = floatval($this->_DST['width']);
              $image_height = floatval($this->_DST['height']);
	         $this->_DST['image'] = $this->addText($this->textOptions['text'],$image_width,$image_height, $this->_DST['image']);
	     }
	     
	     
	     
	     
	     
	       // if ($verbose ) echo "... saving image ...";
	
	        if ($this->_DST['type'] == 1)	{
		    imagetruecolortopalette($this->_DST['image'], false, 256);
			if ( function_exists ('imagegif') ) {
				imagegif($this->_DST['image'], $this->_DST['file']);
			} else {
				imagedestroy($this->_DST['image']);
				imagedestroy($this->_SRC['image']);
				return false;
			}
		}
		elseif ($this->_DST['type'] == 2) {
		    imagejpeg($this->_DST['image'], $this->_DST['file'], $this->quality);
		}
		elseif ($this->_DST['type'] == 3) {
		    imagepng($this->_DST['image'], $this->_DST['file']);
		}
		elseif ($this->_DST['type'] == 5) {
		    imagejpeg($this->_DST['image'], str_replace(".psd", "", $this->_DST['file']) . ".jpg");
		}
	
	        //if ($verbose ) echo "... image successfully saved ...";
	
		imagedestroy($this->_DST['image']);
		imagedestroy($this->_SRC['image']);
		return true;
    }
     
     
     function addText($string='Text goes here', $image_width=NULL, $image_height=NULL, $image=NULL)
	{

	    
	     
	     $fontsize = floatval($this->textOptions['fontsize']);
	     $default_font = BASEPATH . "fonts" . DIRECTORY_SEPARATOR . "arial.ttf";
	     $font = is_null($this->textOptions['font']) ? $default_font : $this->textOptions['font'];
	     $custom = $this->textOptions['custom'];
	     
	     $fontcolor = $this->textOptions['fontcolor'];
	     $box = $this->textOptions['box'];
	     $pos = $this->textOptions['pos'];
	     $angle = $this->textOptions['angle'];
	     $padding = $this->textOptions['pad'];
	     
	     if ($font) {

			$font_file = $font;
			$font = false;

			if (!is_file($font_file)) {
				$font_file = BASEPATH . "fonts" . DIRECTORY_SEPARATOR . $font_file . ".ttf";
			}

			if (is_file($font_file)) {
				$font = $font_file;
			}

			
		}
		if (!$font || is_null($font)) {
			$font = $default_font;
		}
          
	     $string = urldecode($string);
	     
		// create a bounding box for the text
          $dims = imagettfbbox($fontsize, $angle, $font, $string);
          
          
          
          
          
          
          // make some easy to handle dimension vars from the results of imagettfbbox
          // since positions aren't measures in 1 to whatever, we need to
          // do some math to find out the actual width and height
          $width = $dims[4] - $dims[6]; // upper-right x minus upper-left x 
          $height = $dims[3] - $dims[5]; // lower-right y minus upper-right y
          $bgcolor_img = false;
          if($bgcolor = $this->textOptions['bgcolor'])
          {
          
               // pick color for the background
               $bgcolor_img = imagecolorallocate($image, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
          }
          
          // Create image or use existing image
          if(!$custom || is_null($image))
          {
               $image_width = is_null($image_width) ? $width : $image_width;
               $image_height = is_null($image_height) ? $height : $image_height;
               
               
               
               $image = imagecreatetruecolor($image_width,$image_height);
               
               
               
               
               // fill in the background with the background color
               imagefilledrectangle($image, 0, 0, $image_width, $image_height, $bgcolor_img);
               
               
          }
          
          // pick color for the text
          $fontcolor_img = imagecolorallocate($image, $fontcolor[0], $fontcolor[1], $fontcolor[2]);
          
          
          
          // x,y coords for imagettftext defines the baseline of the text: the lower-left corner
          // so the x coord can stay as 0 but you have to add the font size to the y to simulate
          // top left boundary so we can write the text within the boundary of the image
          switch($pos['x'])
          {
               case 'center':
                    $x = $image_width/2 - $width/2;
                    break;
               case 'right':
                    $x = $image_width - $width - $padding[1];
                    break;
               case 'left':
                    $x = 0 + $padding[3];
                    break;
               default:
                    $x = $pos['x'];
                    
          }
          
          if($x < $padding[3])
          {
               $x+= $padding[3];
          }
          if($x > ($image_width - $padding[1]))
          {
               $x -= $padding[1];
          }
          
          
          switch($pos['y'])
          {
               case 'center':
                    $y = $image_height/2 + $height/2;
                    break;
               case 'bottom':
                    $y = $image_height - $padding[2];
                    break;
               case 'top':
                    $y = 0 + $padding[0] + $height;
                    break;
               default:
                    $y = $pos['y'];
                    break;
          }
          
          if($y < $padding[0])
          {
               
               $y+= $padding[0];
               
          }
          if($y > ($image_height - $padding[2]))
          {
               
               $y -= $padding[2];
          }
          $x = round($x);
          $y = round($y);
          if($box) 
          {
               if(isset($this->textOptions['boxcolor']))
               {
                    $bgcolor = $this->textOptions['boxcolor'];
                    $bgcolor_img = imagecolorallocate($image, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
               }
               if($bgcolor_img)
               {
                    $bpad = 10;
          
                    imagefilledrectangle($image, $x-$bpad, $y-$height-$bpad, $x+$width+$bpad+round($bpad/2), $y+$bpad+round($bpad/2), $bgcolor_img);
               }
          }
          
          imagettftext($image, $fontsize, 0, $x, $y, $fontcolor_img, $font, $string);
          
          return $image;
          
          
	}
     
     
     
    /**
     * return a reference to the the thumbnailimage as a HTML <a> or <img> tag
     * @param	aslink	return an anchor tag to the source image
     * @param	aspopup	open link in new window
     * @return	string with suitable HTML markup
     */
    function asTag( $aslink = true, $aspopup = false  )
    {
        $imgtag = "<img src=\"" . $this->_DST['file']. "\" " .
                    $this->html . " " .
                    "width=\"".$this->width."\" " .
                    "height=\"".$this->height."\" " .
                    "/>";

        if ( $aslink === true ) {
            return "<a href=\"" . ((empty($this->linkurl)) ? $this->_SRC['file'] : $this->linkurl) . "\" " .
                    (($aspopup === true) ? "target=\"_blank\"" : "") . ">" .
                    $imgtag .
                    "</a>";
        }
        else {
            return $imgtag;
        }
    }
}

/**
 * Unsharp mask algorithm by Torstein HÃ¸nsi 2003 (thoensi_at_netcom_dot_no)
 * Christoph Erdmann: changed it a little, cause i could not reproduce the
 * darker blurred image, now it is up to 15% faster with same results
 * @param   img     image as a ressource
 * @param   amount  filter parameter
 * @param   radius  filter parameter
 * @param   treshold    filter parameter
 * @return  sharpened image as a ressource
 *
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option) any later version.
*/

function UnsharpMask($img, $amount, $radius, $threshold)    {
    // Attempt to calibrate the parameters to Photoshop:
    if ($amount > 500) $amount = 500;
    $amount = $amount * 0.016;
    if ($radius > 50) $radius = 50;
    $radius = $radius * 2;
    if ($threshold > 255) $threshold = 255;

    $radius = abs(round($radius)); 	// Only integers make sense.
    if ($radius == 0) {	return $img; imagedestroy($img); break;	}
    $w = imagesx($img); $h = imagesy($img);
    $imgCanvas = $img;
    $imgCanvas2 = $img;
    $imgBlur = imagecreatetruecolor($w, $h);

    // Gaussian blur matrix:
    //	1	2	1
    //	2	4	2
    //	1	2	1

    // Move copies of the image around one pixel at the time and merge them with weight
    // according to the matrix. The same matrix is simply repeated for higher radii.
    for ($i = 0; $i < $radius; $i++)
            {
            imagecopy      ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
            imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
            imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
            imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
            imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
            imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
            }
    $imgCanvas = $imgBlur;

    // Calculate the difference between the blurred pixels and the original
    // and set the pixels
    for ($x = 0; $x < $w; $x++) { // each row
        for ($y = 0; $y < $h; $y++) { // each pixel
            $rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
            $rOrig = (($rgbOrig >> 16) & 0xFF);
            $gOrig = (($rgbOrig >> 8) & 0xFF);
            $bOrig = ($rgbOrig & 0xFF);
            $rgbBlur = ImageColorAt($imgCanvas, $x, $y);
            $rBlur = (($rgbBlur >> 16) & 0xFF);
            $gBlur = (($rgbBlur >> 8) & 0xFF);
            $bBlur = ($rgbBlur & 0xFF);

            // When the masked pixels differ less from the original
            // than the threshold specifies, they are set to their original value.
            $rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
            $gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
            $bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;

            if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                ImageSetPixel($img, $x, $y, $pixCol);
            }
        }
    }
    return $img;
}

/* This file is released under the GPL, any version you like 
* 
*    PHP PSD reader class, v1.3 
* 
*    By Tim de Koning 
* 
*    Kingsquare Information Services, 22 jan 2007 
* 
*    example use: 
*    ------------ 
*    <?php 
*    include_once('classPhpPsdReader.php') 
*    header("Content-type: image/jpeg"); 
*    print imagejpeg(imagecreatefrompsd('test.psd')); 
*    ?> 
* 
*    More info, bugs or requests, contact info@kingsquare.nl 
* 
*    Latest version and demo: http://www.kingsquare.nl/phppsdreader 
* 
*    TODO 
*    ---- 
*    - read color values for "multichannel data" PSD files 
*    - find and implement (hunter)lab to RGB algorithm 
*    - fix 32 bit colors... has something to do with gamma and exposure available since CS2, but dunno how to read them... 
*/ 


class Thumbnail_Psd { 
    var $infoArray; 
    var $fp; 
    var $fileName; 
    var $tempFileName; 
    var $colorBytesLength; 

    function __construct($fileName) { 
        set_time_limit(0); 
        $this->infoArray = array(); 
        $this->fileName = $fileName; 
        $this->fp = fopen($this->fileName,'r'); 

        if (fread($this->fp,4)=='8BPS') { 
            $this->infoArray['version id'] = $this->_getInteger(2); 
            fseek($this->fp,6,SEEK_CUR); // 6 bytes of 0's 
            $this->infoArray['channels'] = $this->_getInteger(2); 
            $this->infoArray['rows'] = $this->_getInteger(4); 
            $this->infoArray['columns'] = $this->_getInteger(4); 
            $this->infoArray['colorDepth'] = $this->_getInteger(2); 
            $this->infoArray['colorMode'] = $this->_getInteger(2); 


            /* COLOR MODE DATA SECTION */ //4bytes Length The length of the following color data. 
            $this->infoArray['colorModeDataSectionLength'] = $this->_getInteger(4);
            fseek($this->fp,$this->infoArray['colorModeDataSectionLength'],SEEK_CUR); // ignore this snizzle 

            /*  IMAGE RESOURCES */ 
            $this->infoArray['imageResourcesSectionLength'] = $this->_getInteger(4); 
            fseek($this->fp,$this->infoArray['imageResourcesSectionLength'],SEEK_CUR); // ignore this snizzle 

            /*  LAYER AND MASK */ 
            $this->infoArray['layerMaskDataSectionLength'] = $this->_getInteger(4);
            fseek($this->fp,$this->infoArray['layerMaskDataSectionLength'],SEEK_CUR); // ignore this snizzle 


            /*  IMAGE DATA */ 
            $this->infoArray['compressionType'] = $this->_getInteger(2); 
            $this->infoArray['oneColorChannelPixelBytes'] = $this->infoArray['colorDepth']/8; 
            $this->colorBytesLength = $this->infoArray['rows']*$this->infoArray['columns']*$this->infoArray['oneColorChannelPixelBytes']; 

            if ($this->infoArray['colorMode']==2) { 
                $this->infoArray['error'] = 'images with indexed colours are not supported yet'; 
                return false; 
            } 
        } else { 
            $this->infoArray['error'] = 'invalid or unsupported psd'; 
            return false; 
        } 
    } 


    function getImage() { 
        // decompress image data if required 
        switch($this->infoArray['compressionType']) { 
            // case 2:, case 3: zip not supported yet.. 
            case 1: 
                // packed bits 
                $this->infoArray['scanLinesByteCounts'] = array(); 
                for ($i=0; $i<($this->infoArray['rows']*$this->infoArray['channels']); $i++) $this->infoArray['scanLinesByteCounts'][] = $this->_getInteger(2); 
                $this->tempFileName = tempnam(realpath('/tmp'),'decompressedImageData'); 
                $tfp = fopen($this->tempFileName,'wb'); 
                foreach ($this->infoArray['scanLinesByteCounts'] as $scanLinesByteCount) { 
                    fwrite($tfp,$this->_getPackedBitsDecoded(fread($this->fp,$scanLinesByteCount))); 
                } 
                fclose($tfp); 
                fclose($this->fp); 
                $this->fp = fopen($this->tempFileName,'r'); 
            default: 
                // continue with current file handle; 
                break; 
        } 

        // let's write pixel by pixel.... 
        $image = imagecreatetruecolor($this->infoArray['columns'],$this->infoArray['rows']); 

        for ($rowPointer = 0; ($rowPointer < $this->infoArray['rows']); $rowPointer++) { 
            for ($columnPointer = 0; ($columnPointer < $this->infoArray['columns']); $columnPointer++) { 
                /*     The color mode of the file. Supported values are: Bitmap=0; 
                    Grayscale=1; Indexed=2; RGB=3; CMYK=4; Multichannel=7; 
                    Duotone=8; Lab=9. 
                */ 
                switch ($this->infoArray['colorMode']) { 
                    case 2: // indexed... info should be able to extract from color mode data section. not implemented yet, so is grayscale 
                        exit; 
                        break; 
                    case 0: 
                        // bit by bit 
                        if ($columnPointer == 0) $bitPointer = 0; 
                        if ($bitPointer==0) $currentByteBits = str_pad(base_convert(bin2hex(fread($this->fp,1)), 16, 2),8,'0',STR_PAD_LEFT); 
                        $r = $g = $b = (($currentByteBits[$bitPointer]=='1')?0:255); 
                        $bitPointer++; 
                        if ($bitPointer==8) $bitPointer = 0; 
                        break; 

                    case 1: 
                    case 8: // 8 is indexed with 1 color..., so grayscale 
                        $r = $g = $b = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        break; 

                    case 4: // CMYK 
                        $c = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        $currentPointerPos = ftell($this->fp); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $m = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $y = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $k = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$currentPointerPos); 
                        $r = round(($c * $k) / (pow(2,$this->infoArray['colorDepth'])-1)); 
                        $g = round(($m * $k) / (pow(2,$this->infoArray['colorDepth'])-1)); 
                        $b = round(($y * $k) / (pow(2,$this->infoArray['colorDepth'])-1)); 

                          break; 

                          case 9: // hunter Lab 
                              // i still need an understandable lab2rgb convert algorithm... if you have one, please let me know! 
                            $l = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                            $currentPointerPos = ftell($this->fp); 
                            fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                            $a = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                            fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                            $b =  $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                            fseek($this->fp,$currentPointerPos); 

                            $r = $l; 
                            $g = $a; 
                            $b = $b; 

                        break; 
                    default: 
                        $r = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        $currentPointerPos = ftell($this->fp); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $g = $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$this->colorBytesLength-1,SEEK_CUR); 
                        $b =  $this->_getInteger($this->infoArray['oneColorChannelPixelBytes']); 
                        fseek($this->fp,$currentPointerPos); 
                        break; 

                } 

                if (($this->infoArray['oneColorChannelPixelBytes']==2)) { 
                    $r = $r >> 8; 
                    $g = $g >> 8; 
                    $b = $b >> 8; 
                } elseif (($this->infoArray['oneColorChannelPixelBytes']==4)) { 
                    $r = $r >> 24; 
                    $g = $g >> 24; 
                    $b = $b >> 24; 
                } 

                $pixelColor = imagecolorallocate($image,$r,$g,$b); 
                imagesetpixel($image,$columnPointer,$rowPointer,$pixelColor); 
            } 
        } 
        fclose($this->fp); 
        if (isset($this->tempFileName)) unlink($this->tempFileName); 
        return $image; 
    } 

    /** 
     * 
     * PRIVATE FUNCTIONS 
     * 
     */ 

    function _getPackedBitsDecoded($string) { 
        /* 
        The PackBits algorithm will precede a block of data with a one byte header n, where n is interpreted as follows: 
        n Meaning 
        0 to 127 Copy the next n + 1 symbols verbatim 
        -127 to -1 Repeat the next symbol 1 - n times 
        -128 Do nothing 

        Decoding: 
        Step 1. Read the block header (n). 
        Step 2. If the header is an EOF exit. 
        Step 3. If n is non-negative, copy the next n + 1 symbols to the output stream and go to step 1. 
        Step 4. If n is negative, write 1 - n copies of the next symbol to the output stream and go to step 1. 

        */ 

        $stringPointer = 0; 
        $returnString = ''; 

        while (1) { 
            if (isset($string[$stringPointer])) $headerByteValue = $this->_unsignedToSigned(hexdec(bin2hex($string[$stringPointer])),1); 
            else return $returnString; 
            $stringPointer++; 

            if ($headerByteValue >= 0) { 
                for ($i=0; $i <= $headerByteValue; $i++) { 
                    $returnString .= $string[$stringPointer]; 
                    $stringPointer++; 
                } 
            } else { 
                if ($headerByteValue != -128) { 
                    $copyByte = $string[$stringPointer]; 
                    $stringPointer++; 

                    for ($i=0; $i < (1-$headerByteValue); $i++) { 
                        $returnString .= $copyByte; 
                    } 
                } 
            } 
        } 
    } 

    function _unsignedToSigned($int,$byteSize=1) { 
        switch($byteSize) { 
            case 1: 
                if ($int<128) return $int; 
                else return -256+$int; 
                break; 

            case 2: 
                if ($int<32768) return $int; 
                else return -65536+$int; 

            case 4: 
                if ($int<2147483648) return $int; 
                else return -4294967296+$int; 

            default: 
                return $int; 
        } 
    } 

    function _hexReverse($hex) { 
        $output = ''; 
        if (strlen($hex)%2) return false; 
        for ($pointer = strlen($hex);$pointer>=0;$pointer-=2) $output .= substr($hex,$pointer,2); 
        return $output; 
    } 

    function _getInteger($byteCount=1) { 
        switch ($byteCount) { 
            case 4: 
                // for some strange reason this is still broken... 
                return @reset(unpack('N',fread($this->fp,4))); 
                break; 

            case 2: 
                return @reset(unpack('n',fread($this->fp,2))); 
                break; 

            default: 
                return hexdec($this->_hexReverse(bin2hex(fread($this->fp,$byteCount)))); 
        } 
    } 
} 

/** 
* Returns an image identifier representing the image obtained from the given filename, using only GD, returns an empty string on failure 
* 
* @param string $fileName 
* @return image identifier 
*/ 

function imagecreatefrompsd($fileName) { 
    $psdReader = new Thumbnail_Psd($fileName); 
    if (isset($psdReader->infoArray['error'])) return ''; 
    else return $psdReader->getImage(); 
} 


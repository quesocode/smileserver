<?php
require_once 'files.php';

class Images extends Files
{
	public $resize;
	public $thumb_method = NULL;
	public $quality = 100;
	public $sharpen = false;
	public $hint = false;
	public $addbgtohint = false;
     public $textOptions = false;

	// flag this in case the sharpening is causing problems
	public $disable_sharpen = false;
	// default sharpening amount
	public $def_usa = 80;

	// default sharpening threshold
	public $def_ust = 3;

	// default sharpening radius
	public $def_usr = .5;

	function index()
	{
		// reset php time to allow it to process
		ini_set("max_execution_time", 30);
		if ($this->isUriAskingToThumb()) {
			$max = 1200;
			$image =& $this->getFile();
               /* var_dump($image);exit; */
			// if there is an image already found
			if ($image) {
                    
				$thumb = $this->prepareThumb();

				// fix double slashes
				if (isset($image['path'])) {
					$infile = str_replace("//", "/", $image['path']);
				}
				else {
					return $this->requestNotFound();
				}
				// generate a unique filename based on thumbnail parameters
				$outfile = $this->getOutfile($image);
                    
				// if there is not a processed file, or its being forced
				if (($outfile && !is_file($outfile)) || isset($_GET['thumb'])) {

					//
					if (!$outfile) $outfile = $this->getOutFilePath($image['name']);
					$outfile = str_replace("//", "/", $outfile);


					if ($success = $thumb->write($infile, $outfile)) {
						$sym = str_replace("//", "/", $this->getSymLink());

						$s = pathinfo($sym);
						$dir = $s['dirname'] . DIRECTORY_SEPARATOR;

						if ($this->setupSymLink() && $sym && !is_file($sym) && !is_dir($sym) && !is_link($sym)) {
							$res = Filer::makeDir($dir);

							symlink($outfile, $sym);
						}

						$image['path'] = $outfile;
						$image['size'] = filesize($image['path']);
						$this->setFile($image);
					}
					else {
						$this->serverError = true;
					}
				}
				else {
					$outfile = str_replace("//", "/", $outfile);
					$sym = str_replace("//", "/", $this->getSymLink());
					$s = pathinfo($sym);
					$dir = $s['dirname'] . DIRECTORY_SEPARATOR;

					if ($this->setupSymLink() && $sym && !is_file($sym) && !is_dir($sym) && !is_link($sym)) {
						$res = Filer::makeDir($dir);
						symlink($outfile, $sym);
					}
					$image['path'] = $this->getOutFilePath($image['filename']);
					$image['size'] = filesize($image['path']);
					$this->setFile($image);
				}

			}
			else {
				return $this->requestNotFound();
			}
		}
		else {
			// if the request is not asking to thumbnail the image
			$image =& $this->getFile();
			if ($this->setupSymLink() && !is_link($this->getSymLink())) {
				$sym = str_replace("//", "/", $this->getSymLink());

				$s = pathinfo($sym);
				$dir = $s['dirname'] . DIRECTORY_SEPARATOR;

				if ($this->setupSymLink() && $sym && !is_file($sym) && !is_dir($sym) && !is_link($sym)) {
					$res = Filer::makeDir($dir);
					symlink($outfile, $sym);
				}

			}
		}

		parent::index();
	}
	function setupSymLink()
	{
		if ($this->uri->segment(2) && $this->uri->segment(3)) 
		{
		     $this->load->helper('url');
		     $preurl = $this->parseUrl(current_url());
		     $info = pathinfo($preurl['path']);
		     if(isset($info['extension']))
		     {
			  return true;
               }
		}
		return false;
	}
	function getThumbnailPath()
	{
		$file = $this->getFile();
		$pi = pathinfo($file['path']);

		return $pi['dirname'];
	}
	function getPathName()
	{

		$file = $this->getFile();
		$array = pathinfo($file['path']);
		return isset($array["dirname"]) ? $array["dirname"] : NULL;
	}

	function getPathFile()
	{

		$file = $this->getFile();
		$array = pathinfo($file['path']);
		return isset($array["dirname"]) && isset($array["basename"]) ? $array["dirname"] . DIRECTORY_SEPARATOR .$array["basename"] : NULL;
	}
	function getSymLink()
	{
		$this->load->helper('url');
		$uri = uri_string();
		$sym_link = FCPATH . substr($uri, 1);
		$symlink = $this->image_notFound ? false : $sym_link;
		return $symlink;
	}
	function getSharpenAmount()
	{
		return (isset($_GET['usa'])) ? floatval($_GET['usa']) : $this->def_usa;
	}
	function getSharpenRadius()
	{
		return (isset($_GET['usr'])) ? floatval($_GET['usr']) : $this->def_usr;
	}
	function getSharpenThreshold()
	{
		return (isset($_GET['ust'])) ? floatval($_GET['ust']) : $this->def_ust;
	}
	function getShortSide()
	{
		$width = $this->getWidth();
		$height = $this->getHeight();

		if ($width && $height) {
			return min($width, $height);
		}
		elseif ($width) {
			return $width;
		}
		else {
			return $height;
		}
	}
	function getLongSide()
	{
		$width = $this->getWidth();
		$height = $this->getHeight();

		if ($width && $height) {
			return max($width, $height);
		}
		elseif ($width) {
			return $width;
		}
		else {
			return $height;
		}
	}
	function isUriAskingToThumb()
	{
		if ($this->shouldResize()) return true;
		if ($this->getQuality(false)) return true;
		if ($this->getSharpen()) return true;
		if ($this->getImageText()) return true;

		return false;
	}
	function shouldResize()
	{
		return self::getResize() ? true : false;
	}
	function getOutFilePath($name)
	{
		$dir = "";
		if ($dir = $this->getThumbnailPath()) {
			$dir = str_replace("//", "/", $dir);
			
		}
		return str_replace("//", "/", $dir . DIRECTORY_SEPARATOR . $this->generateName($name));
	}
	function getSharpen()
	{
		$s = false;

		$s = isset($_GET['sharpen']) && $_GET['sharpen'] ? true : false;
		$s = isset($_GET['s']) && $_GET['s'] && !$s ? true : $s;
		$s = isset($_GET['shrp']) && $_GET['shrp'] && !$s ? true : $s;


		$s = isset($_GET['ust']) && $_GET['ust'] && !$s ? true : $s;
		$s = isset($_GET['usr']) && $_GET['usr'] && !$s ? true : $s;
		$s = isset($_GET['usa']) && $_GET['usa'] && !$s ? true : $s;



		return $s;
	}



	function getImageText($force=false)
	{
	     if($this->textOptions) return $this->textOptions;
	     
	     $w = $this->getWidth();
          $h = $this->getHeight();

	     
	     
		$s = array(

			'text'=>$w.'x'.$h,
			'angle'=>0,
			'fontsize'=>12.0,
			'bgcolor'=>array(37, 37, 37),
			'fontcolor'=>array(255, 255, 255),
			'pos'=>array('x'=>'center','y'=>'center'),
			'pad'=>array(0,0,0,0),
			'font'=>NULL,
			'boxcolor'=>NULL,
			'box'=>false,
			'custom'=>true
			

		);
		$f = $force;
		$text = $this->input->get_post('text');
		if ($text) {
			$s['text'] = $text;
			$f = true;
		}

		$fontsize = $this->input->get_post('fontsize');
		if ($fontsize) {
			$s['fontsize'] = $fontsize;
		}
		
		$box = $this->input->get_post('box') ? $this->input->get_post('box') : false;
		$box = $this->input->get_post('bg') && $this->getSource() ? true : $box;
		if ($box) {
			$s['box'] = true;
		}
		
		$boxcolor = $this->input->get_post('boxcolor');
		if ($boxcolor) {
			$this->load->helper('color');

			$s['boxcolor'] = html2rgb($boxcolor);
		}
		
		

		$fontcolor = $this->input->get_post('color');
		if ($fontcolor) {
			$this->load->helper('color');

			$s['fontcolor'] = html2rgb($fontcolor);
		}

		$bgcolor = $this->input->get_post('bg');
		if ($bgcolor) {
			$this->load->helper('color');

			$s['bgcolor'] = html2rgb($bgcolor);
		}

		$angle = $this->input->get_post('txtangle');
		if ($angle) {
			$s['angle'] = floatval($angle);
		}
		
		$x = $this->input->get_post('tx') ? $this->input->get_post('tx') : $this->input->get_post('x');
		if ($x) {
			$s['pos']['x'] = is_numeric($x) ? floatval($x) : $x;
			
		}
		
		$y = $this->input->get_post('ty') ? $this->input->get_post('ty') : $this->input->get_post('y');
		if ($y) {
			$s['pos']['y'] = is_numeric($y) ? floatval($y) : $y;
			
		}
		
		$pad = $this->input->get_post('tpad');
		if ($pad) {
		     $pad = explode(",",$pad);
		     if(count($pad) > 1)
		     {
		        switch(count($pad))
		        {
		             case 2:
		                  $pad = array($pad[0],$pad[1],$pad[0],$pad[1]);
		                  break;
		             case 3:
		                  $pad = array($pad[0],$pad[1],$pad[3],$pad[1]);
		                  break;
		        }
		     }
		     else
		     {
		        $p = $pad[0];
		        $pad = array($p,$p,$p,$p);
		        
		     }
			$s['pad'] = $pad;
			
		}

		$font = $this->input->get_post('font');
		if ($font) {
               $s['font'] = $font;
		}
		$return = $f ? $s : false;
		if($return)
		{
		   $this->textOptions = $return;
		}
		return $return;
	}

     function getSource ()
     {
          $src = $this->input->get_post('src');
          if(!$src) $src = $this->input->get_post('img');
          return $src;
     }


	function getOutfile($image = NULL)
	{
		if (is_null($image)) $image = $this->getFile();
		if ($image) 
		{
			$out = $this->getOutFilePath($image['filename']);
			$out2 =  Core::makeUniqueFile($out, $out);
			if (!$out2) return $out;
			return $out2;
		}
		return NULL;
	}
	function getThumbMethod()
	{
		return $this->thumb_method;
	}
	function generateName($name)
	{
		$n = pathinfo($name);

		$sharp = $this->getSharpen() ? "_shrp" : NULL;

		if ($sharp && $this->getSharpenAmount() && $this->getSharpenAmount() != $this->def_usa) $sharp .= $this->getSharpenAmount();
		if ($sharp && $this->getSharpenThreshold() && $this->getSharpenThreshold() != $this->def_ust) $sharp .= $this->getSharpenThreshold();
		if ($sharp && $this->getSharpenRadius() && $this->getSharpenRadius() != $this->def_usr) $sharp .= round(($this->getSharpenRadius() + 1)*10);

          $txtstr = NULL;
          if($txt = $this->getImageText())
          {
               //var_dump($txt);
               $txtstr = r_implode("-", $txt);
               $txtstr = substr(md5($txtstr),0,9);

          }
          
		$quality = $this->getQuality() ? "_q" . $this->getQuality() : NULL;
		$str = $this->getWidth() . $this->getThumbMethod() . $this->getHeight() . $quality . $sharp . $txtstr;
		$prefix = $this->image_notFound ? "_nf_" : NULL;
		if (isset($n['extension']) && isset($n['filename'])) {
			return Core::purify($prefix . $n['filename'] . "__" . $str . "." . $n['extension']);
		}
	}
	function getWidth()
	{
		return isset($this->resize["width"]) ? $this->resize["width"] : NULL;
	}
	function getHeight()
	{
		return isset($this->resize["height"]) ? $this->resize["height"] : NULL;
	}
	function getQuality($include_default = true)
	{
		$q = $this->input->get('q') ? $this->input->get('q') : NULL;

		$q = (is_null($q)) ? $this->quality : $q;



		if (is_numeric($q) && $q < 1) $q = 1;
		if (is_numeric($q) && $q >= 100) $q = 100;
		return $q;

	}
	function badRequest()
	{
		$resp= $this->render($this->document->template, array
			(
				'content' => NULL,
				'heading'=>"Wrong File",
				'encoding' => $this->document->encoding,
				'title' => "The file you requested is not the type allowed.",
				'scripts' => $this->document->scripts,
				'styles' => $this->document->styles
			));
		return $resp;
	}
	function getResize()
	{
		if ($value = $this->uri->segment(2)) {
			if (preg_match("(^(?P<width>(\d+))?((?P<method>(l|x|s|c|r|L|X|S|C|R)))?((?P<height>(\d+)))?$)", $value, $sizes)) {
				$info = $sizes;
				$width = isset($info["width"]) ? $info["width"] : NULL;
				$height = isset($info["height"]) ? $info["height"] : NULL;
				$this->thumb_method = isset($info["method"]) ? strtolower($info["method"]) : NULL;

				$this->resize = array("width"=>$width, "height"=>$height);

				return $this->resize;
			}
		}
		if ($value = $this->uri->segment(3)) {
			if (preg_match("(^(?P<width>(\d+))?((?P<method>(l|x|s|c|r|L|X|S|C|R)))?((?P<height>(\d+)))?$)", $value, $sizes)) {
				$info = $sizes;
				$width = isset($info["width"]) ? $info["width"] : NULL;
				$height = isset($info["height"]) ? $info["height"] : NULL;
				$this->thumb_method = isset($info["method"]) ? strtolower($info["method"]) : NULL;

				$this->resize = array("width"=>$width, "height"=>$height);

				return $this->resize;
			}
		}

		return false;
	}
	public function prepareThumb()
	{
		$this->load->library('Thumbnail');
		$thumb = $this->thumbnail;

		$thumb->quality = $this->getQuality();

		switch ($this->getThumbMethod()) {
		case "x":
			if ($this->getWidth() && $this->getHeight()) {
				$thumb->width = $this->getWidth();
				$thumb->height = $this->getHeight();
			}
			else {
				$this->badRequest = true;
			}

			break;
		case "r":
			if ($this->getWidth() && $this->getHeight()) {
				$thumb->width = $this->getWidth();
				$thumb->height = $this->getHeight();
				$thumb->crop = false;
			}
			else {
				$this->badRequest = true;
			}

			break;
		case "s":
			if ($this->getShortSide()) {
				$thumb->shortside = $this->getShortSide();
			}
			else {
				$this->badRequest = true;
			}
			break;
		case "c":

			if ($this->getWidth() && $this->getHeight()) {
				$thumb->width = $this->getWidth();
				$thumb->height = $this->getHeight();
				$thumb->crop = true;
			}
			else {
				$this->badRequest = true;
			}

			break;
		case "l":
			if ($this->getLongSide()) {
				$thumb->longside = $this->getLongSide();
			}
			else {
				$this->badRequest = true;
			}
			break;
		default:
			if ($this->getLongSide()) {
				$thumb->longside = $this->getLongSide();
			}
			elseif (!$this->getQuality() && !$this->getSharpen()) {
				$this->badRequest = true;
			}
			else {
				$thumb->keepsize = true;
			}
			break;

		}
		if (isset($_GET['force'])) {
			$thumb->extrapolate = $_GET['force'] ? true : false;
		}
		else {
			$thumb->extrapolate = true;
		}
		if (!$this->disable_sharpen) {
			$thumb->sharpen = $this->getSharpen();
			$thumb->unsharp_amount = $this->getSharpenAmount();
			$thumb->unsharp_radius = $this->getSharpenRadius();
			$thumb->unsharp_threshold = $this->getSharpenThreshold();
		}

		if ($txt_opts = $this->getImageText()) {
			$thumb->textOptions = $txt_opts;
			$thumb->text = true;
		}

		return $thumb;
	}

	/**
	 * Returns array of information about file being requested
	 * return array(
	 * path:             path to file
	 * ref_url:          url requesting this image
	 * ref_web:          domain requesting image
	 * type:             type of file (from mime type. mime ex: "image/jpg" returns "image" (no quotes)
	 * mime:             mime type of image
	 * size:             filesize
	 * filename:         filename
	 * extension:        file extension
	 * last_modified:    last time file was modified
	 * last_accessed:    last time file was accessed
	 * url:              new url to image
	 * uri:              new uri to formatted image
	 * notfound:         true | false based on if the image being processed is a 404 replacement image
	 * );
	 * */


	public function getFile()
	{

		if (isset($this->file)) return $this->file;
		$this->image_notFound = false;

		// get the file id
		$id = $this->uri->segment(3);
		// if the id is a number
		if ($id && is_numeric($id)) 
		{
			$q = Doctrine_Query::create()->from("Thumbnail t")->where("t.id = ?", $id);
			$file = $q->execute()->getFirst();
			if ($file) $file = $file->toArray();
		}
		else 
		{
			$fromFlickr = false;
			$this->load->helper('url');
			$url = $this->getSource();
			if(!$url) $url = $this->input->get_post('src');
			$extt = Filer::getExtensionFromUrl($url);
			$extt = $extt ? $extt : '';
			$extt_suffix = $extt ? '_'.$extt : null;
			$tag = $this->input->get_post('tag') ? $this->input->get_post('tag') : NULL;
			$tag = urldecode($tag);
               if($url)
               {
     			$preurl = $this->parseUrl($url);
     			
     			if (!isset($preurl['host']) && isset($_SERVER['HTTP_REFERER'])) {
     				$re_url = $this->parseUrl($_SERVER['HTTP_REFERER']);
     				if (isset($re_url['host']) && isset($re_url['scheme'])) {
     					if (substr($url, 0, 1) != '/') {
     						$url = '/'.$url;
     					}
     					$url = $re_url['scheme'] . '://' . $re_url['host'] . $url;
     				}
     			}
     
     
     			if ($url === '' && !$tag) {
     				$url = "notfound";
     			}
               }



			if (!$url && $tag) 
			{

				$this->load->library('phpflickr');
				$this->phpflickr->init(Core::get('flickr_key'));
				$photos = $this->phpflickr->photos_search(array("tags"=>$tag, "tag_mode"=>"all", "privacy_filter"=>"1", "safe_search"=>1, "content_type"=>"1", "sort"=>"relevance", "per_page"=>350));
				if (!isset($photos['photo']) || !count($photos['photo'])) {
					show_404();
					exit();
				}
				$max = count($photos['photo']) > 30 ? 30 : 0;
				$photo = $photos['photo'][rand(0, $max)];
				$url = $this->phpflickr->buildPhotoURL($photo);
				if ($url) $fromFlickr = true;

			}
               // if $_GET['img']
			if ($url) {
			     
				$urlp = $this->parseUrl($url);
				$file_path = false;
				if (isset($urlp['host']) && isset($urlp['path'])) {
					/* print '<pre>'.print_r($urlp,true); */
					$dir = FCPATH . 'images/thumbs/' . $urlp['host'] . substr($urlp['path'], 0, strrpos($urlp['path'], '/')+1);
					$hash = null;
					if (!isset($urlp['file'])) {
						$hash = substr(md5($url), 0, 6) ;
						$urlp['file'] = $hash. '.' . Filer::getExtensionFromUrl($url);
					}

					$file_path = $dir . $urlp['file'];

					$this->load->helper('url');
					$new_url = site_url($this->file_dir . $urlp['host'] . $urlp['path']);
					$new_uri = ($this->file_dir . $urlp['host'] . $urlp['path']);
				}
				$nf_url = null;

				if ($nf_url = $this->input->get_post('notfound')) {
					$nf_file_path = $nf_new_url = $nf_new_uri = NULL;
					$prenfurl = $this->parseUrl($nf_url);

					if (!isset($prenfurl['host']) && isset($_SERVER['HTTP_REFERER'])) {
						$renf_url = $this->parseUrl($_SERVER['HTTP_REFERER']);
						if (isset($renf_url['host']) && isset($renf_url['scheme'])) {
							if (substr($nf_url, 0, 1) != '/') {
								$nf_url = '/'.$nf_url;
							}
							$nf_url = $renf_url['scheme'] . '://' . $renf_url['host'] . $nf_url;
						}
					}

					$nf_urlp = $this->parseUrl($nf_url);

					if (isset($nf_urlp['host'])) {
						$nf_dir = FCPATH . 'images/thumbs/' . $nf_urlp['host'] . substr($nf_urlp['path'], 0, strrpos($nf_urlp['path'], '/')+1);

						if (!isset($nf_urlp['file'])) {
							$hash = !empty($hash) ? $hash : substr(md5($url), 0, 6) ;
							$hash .= '_nf';
							$nf_urlp['file'] = $hash . '.' . Filer::getExtensionFromUrl($nf_url);
						}

						$nf_file_path = $nf_dir . $nf_urlp['file'];


						$nf_new_url = site_url($this->file_dir . $nf_url['host'] . $nf_url['path']);
						$nf_new_uri = ($this->file_dir . $nf_url['host'] . $nf_url['path']);
					}

				}
				$filed = $file_path && is_file($file_path);
				$nf_filed = isset($nf_file_path) && $nf_file_path ? is_file($nf_file_path) : false;
				
				// not processed
				if (!$filed) 
				{
				      
					$contents = $file_path ? @file_get_contents($url) : false;
					if ($contents) 
					{
					     // not processed
					     // able to download img 
						// made img and found it
						
						Filer::save($contents, $urlp['file'], $dir, false);
						$file = array('path'=>$file_path, 'ref_url'=>$url, 'ref_web'=>$urlp['host'], 'type'=>Filer::type($file_path), 'mime'=>Filer::mime($file_path), 'size'=>Filer::size($file_path), 'filename'=>Filer::filename($file_path), 'extension'=>Filer::extension($file_path), 'last_modified'=>Filer::modified($file_path), 'last_accessed'=>Filer::accessed($file_path), 'url'=>$new_url, 'uri'=>$new_uri, 'notfound'=>false, 'download'=>true, 'cache'=>false, 'text'=>false,'id'=>'cachefile688');
						if (!$fromFlickr) {
							$sfile = new SFile();
							$sfile->fromArray($file);
							$sfile->save();
							$file = $sfile->toArray();
						}

					}
					elseif ($nf_url && !$nf_filed && $contents = @file_get_contents($nf_url)) 
					{
					     // not processed
					     // there is a notfound url
					     // it hasnt been filed
					     // and it was able to be downloaded
					     
					     
						$this->quality = 70;
						Filer::save($contents, $nf_urlp['file'], $nf_dir, false);
						$file = array('path'=>$nf_file_path, 'ref_url'=>$nf_url, 'ref_web'=>$nf_urlp['host'], 'type'=>Filer::type($nf_file_path), 'mime'=>Filer::mime($nf_file_path), 'size'=>Filer::size($nf_file_path), 'filename'=>Filer::filename($nf_file_path), 'extension'=>Filer::extension($nf_file_path), 'last_modified'=>Filer::modified($nf_file_path), 'last_accessed'=>Filer::accessed($nf_file_path), 'url'=>$nf_new_url, 'uri'=>$nf_new_uri, 'notfound'=>true, 'download'=>false, 'cache'=>false, 'text'=>false,'id'=>'cachefile707');


					}
					elseif ($nf_url && $nf_filed) 
					{
                              // processed
					     // there is a notfound url
					     // and it was able to be downloaded
					     
					     
						$file = array('ref_url'=>$nf_url, 'ref_web'=>$nf_urlp['host'], 'path'=>$nf_file_path, 'type'=>Filer::type($nf_file_path), 'mime'=>Filer::mime($nf_file_path), 'size'=>Filer::size($nf_file_path), 'filename'=>Filer::filename($nf_file_path), 'extension'=>Filer::extension($nf_file_path), 'last_modified'=>Filer::modified($nf_file_path), 'last_accessed'=>Filer::accessed($nf_file_path), 'url'=>$nf_new_url, 'uri'=>$nf_new_uri, 'notfound'=>true, 'download'=>true, 'cache'=>false, 'text'=>false,'id'=>'cachefile718');

					}
					else 
					{
						// unable to download image
						$url = $this->config->item('default_cant_download_img'.$extt_suffix);
						$nf_urlp = $this->parseUrl($url);
						$a = pathinfo($nf_urlp['path']);

						$nf_file_path = FCPATH . 'images/notfound/'.$a['basename'];
						$nf_new_url = site_url($this->file_dir . $nf_urlp['host'] . $nf_urlp['path']);
						$nf_new_uri = ($this->file_dir . $nf_urlp['host'] . $nf_urlp['path']);
						$file = array('ref_url'=>$url, 'ref_web'=>$nf_urlp['host'], 'path'=>$nf_file_path, 'type'=>Filer::type($nf_file_path), 'mime'=>Filer::mime($nf_file_path), 'size'=>Filer::size($nf_file_path), 'filename'=>Filer::filename($nf_file_path), 'extension'=>Filer::extension($nf_file_path), 'last_modified'=>Filer::modified($nf_file_path), 'last_accessed'=>Filer::accessed($nf_file_path), 'url'=>$nf_new_url, 'uri'=>$nf_new_uri, 'notfound'=>false, 'download'=>false, 'cache'=>false, 'text'=>false,'id'=>'cachefile731');
						$this->quality = 60;
					}
				}
				else 
				{
				     // cache file has been processed
				     
					$file = array('ref_url'=>$url, 'ref_web'=>$urlp['host'], 'path'=>$file_path, 'type'=>Filer::type($file_path), 'mime'=>Filer::mime($file_path), 'size'=>Filer::size($file_path), 'filename'=>Filer::filename($file_path), 'extension'=>Filer::extension($file_path), 'last_modified'=>Filer::modified($file_path), 'last_accessed'=>Filer::accessed($file_path), 'url'=>$new_url, 'uri'=>$new_uri, 'notfound'=>false, 'download'=>true, 'cache'=>true,'text'=>false,'id'=>'cachefile739','file_path'=>$file_path);
				}


			}
			
			
			elseif($txt = $this->getImageText())
			{
			     // if ! $_GET['img']
			     // if ! $_GET['notfound']
			     // if $_GET['text']
			     
			     $this->textOptions['custom'] = false;
			     
				$url = $this->config->item('default_text_img'.$extt_suffix);
				$urlp = $this->parseUrl($url);
				$a = pathinfo($urlp['path']);

				$file_path = FCPATH . 'images/text/'.$a['basename'];
				
				
				$new_url = site_url($this->file_dir . $urlp['host'] . $urlp['path']);
				$new_uri = ($this->file_dir . $urlp['host'] . $urlp['path']);
				$file = array('ref_url'=>$url, 'ref_web'=>$urlp['host'], 'path'=>$file_path, 'type'=>Filer::type($file_path), 'mime'=>Filer::mime($file_path), 'size'=>Filer::size($file_path), 'filename'=>Filer::filename($file_path), 'extension'=>Filer::extension($file_path), 'last_modified'=>Filer::modified($file_path), 'last_accessed'=>Filer::accessed($file_path), 'url'=>$new_url, 'uri'=>$new_uri, 'notfound'=>true, 'download'=>false, 'cache'=>false, 'text'=>true,'id'=>'cachefile763');
				$this->quality = 100;
				
				
				
			}
			elseif ( $this->input->get_post('notfound')) 
			{
			     // if ! $_GET['img']
			     // if $_GET['notfound']
				$nf_url = null;
				$file_path = false;
				
				
				if ($nf_url = $this->input->get_post('notfound')) {
					$nf_file_path = $nf_new_url = $nf_new_uri = NULL;
					$prenfurl = $this->parseUrl($nf_url);

					if (!isset($prenfurl['host']) && isset($_SERVER['HTTP_REFERER'])) {
						$renf_url = $this->parseUrl($_SERVER['HTTP_REFERER']);
						if (isset($renf_url['host']) && isset($renf_url['scheme'])) {
							if (substr($nf_url, 0, 1) != '/') {
								$nf_url = '/'.$nf_url;
							}
							$nf_url = $renf_url['scheme'] . '://' . $renf_url['host'] . $nf_url;
						}
					}




					$nf_urlp = $this->parseUrl($nf_url);

					if (isset($nf_urlp['host'])) {
						$nf_dir = FCPATH . 'images/thumbs/' . $nf_urlp['host'] . substr($nf_urlp['path'], 0, strrpos($nf_urlp['path'], '/')+1);

						if (!isset($nf_urlp['file'])) {
							$hash = !empty($hash) ? $hash : substr(md5($url), 0, 6) ;
							$hash .= '_nf';
							$nf_urlp['file'] = $hash . '.' . Filer::getExtensionFromUrl($nf_url);
						}

						$nf_file_path = $nf_dir . $nf_urlp['file'];


						$nf_new_url = site_url($this->file_dir . $nf_url['host'] . $nf_url['path']);
						$nf_new_uri = ($this->file_dir . $nf_url['host'] . $nf_url['path']);
					}

				}
				
				
				$filed = $file_path && is_file($file_path);
				$nf_filed = isset($nf_file_path) && $nf_file_path ? is_file($nf_file_path) : false;
				if (!$filed) {
					if ($nf_url && !$nf_filed && $contents = @file_get_contents($nf_url)) 
					{
					
					     // not processed
					     // there is a notfound url
					     // it hasnt been filed
					     // and it was able to be downloaded
					     
					     
						$this->quality = 70;
						Filer::save($contents, $nf_urlp['file'], $nf_dir, false);
						$file = array('path'=>$nf_file_path, 'ref_url'=>$nf_url, 'ref_web'=>$nf_urlp['host'], 'type'=>Filer::type($nf_file_path), 'mime'=>Filer::mime($nf_file_path), 'size'=>Filer::size($nf_file_path), 'filename'=>Filer::filename($nf_file_path), 'extension'=>Filer::extension($nf_file_path), 'last_modified'=>Filer::modified($nf_file_path), 'last_accessed'=>Filer::accessed($nf_file_path), 'url'=>$nf_new_url, 'uri'=>$nf_new_uri, 'notfound'=>true, 'download'=>false, 'cache'=>false, 'text'=>false,'id'=>'cachefile829');


					}
					elseif ($nf_url && $nf_filed) 
					{
                              // processed
					     // there is a notfound url
					     // and it was able to be downloaded
					     
						$file = array('ref_url'=>$nf_url, 'ref_web'=>$nf_urlp['host'], 'path'=>$nf_file_path, 'type'=>Filer::type($nf_file_path), 'mime'=>Filer::mime($nf_file_path), 'size'=>Filer::size($nf_file_path), 'filename'=>Filer::filename($nf_file_path), 'extension'=>Filer::extension($nf_file_path), 'last_modified'=>Filer::modified($nf_file_path), 'last_accessed'=>Filer::accessed($nf_file_path), 'url'=>$nf_new_url, 'uri'=>$nf_new_uri, 'notfound'=>true, 'download'=>false, 'cache'=>true, 'text'=>false,'id'=>'cachefile839');

					}
					else 
					{
						// unable to download image
						$url = $this->config->item('default_cant_download_img'.$extt_suffix);
						$nf_urlp = $this->parseUrl($url);
						$a = pathinfo($nf_urlp['path']);

						$nf_file_path = FCPATH . 'images/notfound/'.$a['basename'];
						$nf_new_url = site_url($this->file_dir . $nf_urlp['host'] . $nf_urlp['path']);
						$nf_new_uri = ($this->file_dir . $nf_urlp['host'] . $nf_urlp['path']);
						$file = array('ref_url'=>$url, 'ref_web'=>$nf_urlp['host'], 'path'=>$nf_file_path, 'type'=>Filer::type($nf_file_path), 'mime'=>Filer::mime($nf_file_path), 'size'=>Filer::size($nf_file_path), 'filename'=>Filer::filename($nf_file_path), 'extension'=>Filer::extension($nf_file_path), 'last_modified'=>Filer::modified($nf_file_path), 'last_accessed'=>Filer::accessed($nf_file_path), 'url'=>$nf_new_url, 'uri'=>$nf_new_uri, 'notfound'=>false, 'download'=>true, 'cache'=>false, 'text'=>false,'id'=>'cachefile852');
						$this->quality = 60;
					}
				}

			}
			else 
			{
			     $w = $this->getWidth();
			     $h = $this->getHeight();
			     
				// if there are no parameters
				$this->textOptions = $this->getImageText(true);
				
				
				
			     $this->textOptions['text'] = $w.'x'.$h;
			     $this->textOptions['font'] = 'courier_new_bold';
			     $this->textOptions['fontsize'] = 14;
                    $this->textOptions['custom'] = false;
                    
          
          		
				$url = $this->config->item('default_text_img'.$extt_suffix);
				$urlp = $this->parseUrl($url);
				$a = pathinfo($urlp['path']);

				$file_path = FCPATH . 'images/text/'.$a['basename'];
				
				
				$new_url = site_url($this->file_dir . $urlp['host'] . $urlp['path']);
				$new_uri = ($this->file_dir . $urlp['host'] . $urlp['path']);
				$file = array('ref_url'=>$url, 'ref_web'=>$urlp['host'], 'path'=>$file_path, 'type'=>Filer::type($file_path), 'mime'=>Filer::mime($file_path), 'size'=>Filer::size($file_path), 'filename'=>Filer::filename($file_path), 'extension'=>Filer::extension($file_path), 'last_modified'=>Filer::modified($file_path), 'last_accessed'=>Filer::accessed($file_path), 'url'=>$new_url, 'uri'=>$new_uri, 'notfound'=>true, 'download'=>false, 'cache'=>false, 'text'=>true);
				$this->quality = 100;
                    //var_dump('nothing');
                    //exit;
			}

		}




		if ($file) {
			$return = $this->file = $file;
		}

		if (empty($return) || (isset($return['path']) && !is_file($return['path']))) {
			$this->image_notFound = true;
			$return = $this->file = $this->getNotFound();

		}

		return $return;
	}


	public function requestNotFound()
	{
		show_404();
		exit;
		$file = $this->getFile();

		$this->setHeader("Accept-Ranges", "bytes");
		$this->setHeader('Last-Modified', date('r', filemtime($file['path'])));
		$this->setContentType("image/jpg");
		$this->setHeader("Content-Disposition", 'filename="'."notfound.jpg".'";');
		$contents = file_get_contents($file['path']);
		$this->setContentLength(strlen($contents));
		$this->setEncoding("base64_encode");
		throw new Controller_Http_Response(404, $contents);
	}
	public function getNotFound()
	{
		return array(

			"path"=>FCPATH . "images" . DIRECTORY_SEPARATOR . "notfound.jpg",
			"mime"=>"image/jpg",
			"name"=>"notfound.jpg",
			"type"=>"image"
		);
	}
}



function r_implode( $glue, $pieces ) 
{ 
  foreach( $pieces as $r_pieces ) 
  { 
    if( is_array( $r_pieces ) ) 
    { 
      $retVal[] = r_implode( $glue, $r_pieces ); 
    } 
    else 
    { 
      $retVal[] = $r_pieces; 
    } 
  } 
  return implode( $glue, $retVal ); 
} 
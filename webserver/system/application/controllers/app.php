<?php
require_once APPPATH . '/controllers/mobile/gateway.php';
error_reporting(0);
class App extends Gateway
{

     function processMessage ()
     {
          $this->errors = NULL;
          $this->file_path = false;
          $device = $this->input->get_post('device');
          $destination = $this->input->get_post('destination');
          $message = $this->input->get_post('message');
          if($device == "email")
          {
               $url = $this->getUploadedFileUrl();
               $file = $this->file_path;
               $this->load->library("email");
               $this->email->from('app@smilebooth.com', 'Smilebooth');
               $this->email->to($destination); 
               
               $this->email->bcc('willie@tinystudios.com'); 
               
               $this->email->subject('Your Smilebooth Photo');
               $this->email->message($message);
               if($file)
               {	
                    $this->email->attach($file);
               }
               $this->email->send();
               echo 'Error: ' . $this->errors;
               echo $this->email->print_debugger();
          }
          else if ($device == "phone" || $device == "mms")
          {
               
               $destination = str_replace("-","", $destination);
               $this->send_msg($destination, NULL);
          }
          echo $device . ' processed.';
     }
     function folder ()
     {
          $json = $this->toJson($this->getFolder());
          
          if($this->input->get_post('readable'))
          {
              $json = $this->jsonReadable($json, true);
          }
          echo $json;
     }
     
     function smilebooth ()
     {
          $this->session();
          $imagesArray = $this->getFolder();
          $path = $this->input->get_post('path') ? $this->input->get_post('path') : '/smilebooth/';

          $data = array();
          $data['refresh'] = $this->input->get_post('refresh') ? $this->input->get_post('refresh') : 'yes';
          $data['refreshTime'] = $this->input->get_post('refreshTime') ? $this->input->get_post('refreshTime') : 180;
          $data['eventName'] = $this->input->get_post('eventName') ? $this->input->get_post('eventName') : 'Smilebooth';
          $data['refreshURL'] = $this->input->get_post('gateway') ? $this->input->get_post('gateway') : SMILESERVER . 'smilebooth/?path='.urlencode($path);
          $data['folder'] = urlencode($path);
                    
          $query = $imagesArray['photos'];
          $data['photos'] = array_reverse($query);
          $vars = array();
          $data['show_msg'] = false;
          $data['show_tw_msg'] = false;
          if($this->input->get_post('msg')) $data['show_msg'] = true;
          if($this->input->get_post('show_tw_msg')) $data['show_tw_msg'] = true;
          $data['js'] = $this->load->view('scripts/jquery.1.5.1.js', $vars, true);
          $data['css'] = $this->load->view('styles/webapp.css', $vars, true);
		      $this->load->view('webapp/home', $data);
     }
     
     function jsonReadable($json, $html=FALSE) { 
        $tabcount = 0; 
        $result = ''; 
        $inquote = false; 
        $ignorenext = false; 
        
        if ($html) { 
            $tab = "&nbsp;&nbsp;&nbsp;"; 
            $newline = "<br/>"; 
        } else { 
            $tab = "\t"; 
            $newline = "\n"; 
        } 
        
        for($i = 0; $i < strlen($json); $i++) { 
            $char = $json[$i]; 
            
            if ($ignorenext) { 
                $result .= $char; 
                $ignorenext = false; 
            } else { 
                switch($char) { 
                    case '{': 
                        $tabcount++; 
                        $result .= $char . $newline . str_repeat($tab, $tabcount); 
                        break; 
                    case '}': 
                        $tabcount--; 
                        $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char; 
                        break; 
                    case ',': 
                        $result .= $char . $newline . str_repeat($tab, $tabcount); 
                        break; 
                    case '"': 
                        $inquote = !$inquote; 
                        $result .= $char; 
                        break; 
                    case '\\': 
                        if ($inquote) $ignorenext = true; 
                        $result .= $char; 
                        break; 
                    default: 
                        $result .= $char; 
                } 
            } 
        } 
        
        return $result; 
    }
    
  function raw ()
  {
    $raw = file_get_contents($this->input->get_post('path'));
    
    echo $raw;
  }
	function getFolder()
	{
	  $this->load->helper('url');
		$images = array();
		$times = array();
		$simages = array();
		$path = $this->input->get_post('path') ? $this->input->get_post('path') : '/smilebooth/';
		$photoQuality = $this->input->get_post('pq') ? $this->input->get_post('pq') : 100;
    $thumbQuality = $this->input->get_post('tq') ? $this->input->get_post('tq') : 65;
    $files = $this->getFilesFromFolder($path, array('jpg'));
    $tags = $this->organizeVideosFromFiles($files);
    $baseurl = $this->input->get_post('gateway') ? $this->input->get_post('gateway') : SMILESERVER . 'app/';
    $limit = $this->input->get_post('limit') ? intval($this->input->get_post('limit')) : 4000;
    $viewWidth = $this->input->get_post('viewWidth') ? $this->input->get_post('viewWidth') : 1200;
    $viewHeight = $this->input->get_post('viewHeight') ? $this->input->get_post('viewHeight') : 600;
    $tag = $this->input->get_post('tag') ? $this->input->get_post('tag') : 'preview';
    if($limit < 1 || !is_numeric($limit))
    {
         $limit = 4000;
    }
    
    $x = 0;
    $filesize = 0;
    $hash = NULL;
    
    foreach($files as $i=>$image)
    {

              $info = pathinfo($image);
              $dirname = $info['dirname'];
              
              if($bytes = filesize($image))
              {
                   $filesize += $bytes;
              }
              else
              {
                   $bytes = 0;
              }
              $time = filemtime($image);
              $size = getimagesize($image);
              $file = urlencode($info['filename']) .'.'.$info['extension'];
              $photoUrl = $baseurl . $file .'?method=photo&path='.urlencode($image)."&quality=".$photoQuality;
              
              $thumbUrl = $baseurl .  $file .'?method=thumbnail&path='.urlencode($image)."&quality=".$thumbQuality;
              
              $viewUrl = SMILESERVER . 'photo/?path='.urlencode($image).'&quality='.$photoQuality.'&width='.$viewWidth.'&height='.$viewHeight.'&tag='.$tag.'&folder='.urlencode($path);
              
              $photoUrl = str_replace(' ','%20',$photoUrl);
              $thumbUrl = str_replace(' ','%20',$thumbUrl);
              
              $h = md5($bytes . $photoUrl);
              $hash .= $h.'|';
              
              
              
           
              while(in_array($time,$times))
              {
                   $time++;
              }
              $times[] = $time;
              $filename = str_replace(" ", "", $info['filename']);
              
              
              // video stuff
              $videotypes = array('mpg','mov','m4v');
              foreach($videotypes as $vtype)
              {
                $videoUrl = $dirname . "/". $tags[$i] . ".".$vtype;
                $videofile = $tags[$i] . ".".$vtype;
                if(is_file($videoUrl)) break;
              }
              
              $tokenUrl = $dirname . "/". $tags[$i] . ".token";
              $videoReady = is_file($tokenUrl);
              $videoTag = $tags[$i];
              $videoLink = $baseurl . $videofile .'?method=raw&path='.urlencode($videoUrl);
              
              
              $images[] = array(
                                'id'=>substr($h, 0, 6),
                                'name'=>$filename,
                                'mime'=>$size['mime'],
                                'width'=>$size[0],
                                'height'=>$size[1],
                                'size'=>$bytes,
                                'ext'=>strtolower($info['extension']),
                                'src_big'=>$photoUrl,
                                'src_thumb'=>$thumbUrl,
                                'view_url'=>$viewUrl,
                                'video'=>$videoUrl,
                                'videourl'=>$videoLink,
                                'tag'=>$videoTag,
                                'ready'=>$videoReady);
              
         
         $x++;
    }
    asort($times,SORT_NUMERIC);
    $x = 0;
    foreach($times as $key=>$value)
    {
         
         $simages[] = $images[$key];
         
    }
    $limited = array();
    $rarr = array_reverse($simages);
    
    foreach($rarr as $key=>$value)
    {
         if($x<$limit)
         {
              $limited[] = $value;
         }
         $x++;
    }
    
    $simages = array_reverse($limited);
    
    
    
    
    $this->session('hash',$hash);
		$r = array('token'=>session_id(),
		             'time'=>time(),
		             'photos'=>$simages, 
		             'filesize'=>$filesize, 
		             'count'=>count($images),
		             'pagecount'=>count($simages), 
		             'unique'=>$filesize+count($images));

          return $r;
	}
	function refresh ()
	{
	    $original_hash = $this->session('hash');
	    $original_filesize = $this->input->get_post('filesize') ? $this->input->get_post('filesize') : 0;
	    $original_unique = $this->input->get_post('unique') ? $this->input->get_post('unique') : 0;
	    
	    $folder = $this->getFolder();
	    $newfolder = array();
	    if(intval($folder['unique']) != intval($original_unique))
	    {
	          $added = 0;
	          foreach($folder['photos'] as $image)
               {
                    $hash = md5($image['bytes'] . $image['photo']).'|';
                    
                    if(strpos($original_hash,$hash) === false)
                    {
                         $added+=1;
                         $newfolder[] = $image;
                    }
                    else
                    {
                         $original_hash = str_replace($hash,"",$original_hash);
                    }
               }
               $removed = array();
               if(strlen($original_hash))
               {
                    $removed = array_filter(explode('|', $original_hash));
               }
               $return = array('token'=>$folder['token'], 
		             'photos'=>$newfolder,
		             'removed'=>$removed,
		             'time'=>time(),
		             'filesize'=>$folder['filesize'], 
		             'count'=>$folder['count'], 
		             'unique'=>$folder['unique']);
		     echo $this->toJson($return);
               
	    }
	    else
	    {
	         echo 'false';
	    }
	    
	    
	}
	
	function numshares ()
	{
	    $q = Doctrine_Query::create()->from('ShareRequest s')->where('s.sent = ?','no')->andWhere('s.error = ?','no');
	    $rows = $q->execute();
	    echo $rows->count();
	}
	
	function process ()
	{
	    $num = 1;
	    $shareable = Doctrine_Query::create()->from('ShareRequest s')->where('s.sent = ?','no')->andWhere('s.error = ?','no')->limit(1)->fetchOne();
	    if($shareable)
	    {
	         echo $shareable->sendMessage();
	    }
	    else
	    {
	         echo "0";
	    }
	    
	}
	
	function rss()
	{
		$images = array();
		echo $this->toJson($images);
	}
	function toJson($array=array())
	{
	     
	     $data = $array;
          $json = json_encode($data);
          
		return stripslashes($json);
	}
     function filePathForURL($url, $quality=90, $width=1024, $height=1024, $tag=NULL)
     {
          $urlinfo = parse_url($url);
          parse_str($urlinfo['query'], $params);
          extract($params);
          $tag = $tag ? "_$tag" : "";
          $pinfo = pathinfo($path);
          $filesize = filesize($path);
          $optionsStr = sprintf("_%s_q%s_%sx%s%s", $filesize,$quality,$width,$height,$tag);
          $filename = str_replace(" ", "", $pinfo['filename'].$optionsStr.".".$pinfo['extension']);
          return FCPATH . 'app/'. $filename;
     }

     function photo ()
     {
          $quality = $this->input->get_post('quality') ? intval($this->input->get_post('quality')) : 90;
          $height = $this->input->get_post('height') ? $this->input->get_post('height') : 1024;
          $width = $this->input->get_post('width') ? $this->input->get_post('width') : 1024;
          $tag = $this->input->get_post('tag') ? $this->input->get_post('tag') : NULL;
          $file = $this->input->get_post('path');
          extract(pathinfo($file));
          $this->generateImage($file,$filename,$quality,$width,$height,$tag);
          
     }

     function viewphoto ()
     {
          $quality = $this->input->get_post('quality') ? intval($this->input->get_post('quality')) : 90;
          $height = $this->input->get_post('height') ? $this->input->get_post('height') : 600;
          $width = $this->input->get_post('width') ? $this->input->get_post('width') : 1200;
          $tag = $this->input->get_post('tag') ? $this->input->get_post('tag') : 'preview';
          $file = $this->input->get_post('path');
          $folder = $this->input->get_post('folder') ? $this->input->get_post('folder') : '%2Fsmilebooth%2F';
          $base = $this->input->get_post('gateway') ? $this->input->get_post('gateway') : SMILESERVER . 'app/';
          $galleryURL = $this->input->get_post('gateway') ? $this->input->get_post('gateway') : SMILESERVER . 'smilebooth/?path='.urlencode($folder);
          $twitterSuccessURL = $galleryURL . "&show_tw_msg=show";
          $livemode = $this->input->get_post('livemode') ? $this->input->get_post('livemode') : 'yes';
          $eventName = $this->input->get_post('eventName') ? $this->input->get_post('eventName') : 'Smilebooth';
          $emailSubject = $this->input->get_post('emailSubject') ? $this->input->get_post('emailSubject') : 'Your Smilebooth Photo';
          $emailFrom = $this->input->get_post('emailFrom') ? $this->input->get_post('emailFrom') : 'hello@smilebooth.com';
          $emailMessage = $this->input->get_post('emailMessage') ? $this->input->get_post('emailMessage') : 'Here is your Smilebooth photo';
          $mmsMessage = $this->input->get_post('mmsMessage') ? $this->input->get_post('mmsMessage') : 'Here is your Smilebooth photo';
          $twMessage = $this->input->get_post('twMessage') ? $this->input->get_post('twMessage') : 'Check out my Smilebooth photo';
          
          if($this->input->get_post('emailTemplate'))
          {
              if(strlen($this->input->get_post('emailTemplate'))==2)
              {
                    $emailTemplate = 'html: ' . $this->input->get_post('emailTemplate') . ' #';
              }
              else
              {
                    $emailTemplate = '';
              }
          }
          else
          {
              $emailTemplate = 'html:sb #';
          }

          
          extract(pathinfo($file));
          
          $vars = array();
          $data = array(
          	'base' => $base,
          	'file' => $file,
          	'filename' => $filename,
          	'width' => $width,
          	'height' => $height,
          	'quality'=>$quality,
          	'tag'=>$tag,
          	'livemode'=>$livemode,
          	'emailGateway'=>urlencode('http://swmp.it/app/?subject='.$emailSubject.'&from='.$emailFrom.'&code='.$eventName.'&tpl='.$emailTemplate),
          	'emailTemplate'=>$emailTemplate,
          	'emailFrom'=>$emailFrom,
          	'emailSubject'=>$emailSubject,
          	'emailMessage'=>$emailMessage,
          	'mmsMessage'=>$mmsMessage,
          	'twMessage'=>$twMessage,
          	'twitterSuccessURL'=>$twitterSuccessURL,
          	'emailAttachment'=>urlencode($base . $filename . '.jpg?method=photo&path='.$file.'&quality=88&width=1024&height=1024'),
          	'redirect'=>SMILESERVER.'smilebooth/?msg=show&path='.urlencode($folder)
          );
          $data['js'] = $this->load->view('scripts/jquery.1.5.1.js', $vars, true);
          $data['css'] = $this->load->view('styles/webapp.css', $vars, true);  
          $data['eventName'] = $eventName;
          $this->load->view('webapp/single', $data);
          
     }
     function thumbnail ()
     {
          $quality = $this->input->get_post('quality') ? intval($this->input->get_post('quality')) : 59;
          $height = $this->input->get_post('height') ? $this->input->get_post('height') : 150;
          $width = $this->input->get_post('width') ? $this->input->get_post('width') : 150;
          $tag = $this->input->get_post('tag') ? $this->input->get_post('tag') : NULL;
          $file = $this->input->get_post('path');
          extract(pathinfo($file));
          $this->generateImage($file,$filename,$quality,$width,$height,$tag);
    }
    
    function generateImage($source, $filename,$quality=70,$width=0,$height=0,$tag=NULL)
    {
          $url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
          $new_image_path = $this->filePathForURL($url,$quality,$width,$height,$tag);
          
          if(!is_file($new_image_path))
          {
               $config = array();
               $config['image_library'] = 'gd2';
               $config['source_image']	= $source;
               $config['new_image'] = $new_image_path;
               $config['width']	 = $width;
               $config['quality'] = $quality;
               $config['height']	= $height;
               $this->load->library('image_lib', $config);
               $this->image_lib->resize();
          }
          
          $this->output->set_header('Content-Type: image/jpg');
          $this->output->set_header("Content-Disposition", 'filename="'.$filename.'";');
          $c = file_get_contents($new_image_path);
          $this->output->set_header("Accept-Ranges", "bytes");
		      $this->output->set_header('Last-Modified', date('r', filemtime($new_image_path)));
		      $this->output->set_header("Content-Length", strlen($c));
          $this->output->set_output($c);
     }
     
  
  function share_redirect()
  {
          $shareable = new ShareRequest();
          $shareable->code = $this->input->get_post('code');
          $shareable->username = $this->input->get_post('username');
          $shareable->message = $this->input->get_post('message');
          $shareable->file_url = urldecode($this->input->get_post('file_url'));
          $shareable->destination = $this->input->get_post('destination');
          $shareable->device = $this->input->get_post('device');

          if($shareable->device == 'mms') $shareable->message = 'Just got my @smilebooth photo taken at the #googledcparty';

          $shareable->save();

          $folder = $this->input->get_post('folder') ? $this->input->get_post('folder') : '/smilebooth/';

          if (strtolower($this->input->get_post('share')) === 'yes') {
              $result = $shareable->sendMessage();
          }
          
          if($this->input->get_post('redirect'))
          {
              redirect($this->input->get_post('redirect'));
              exit;
          }
          exit();
  }
    
	function share()
	{
		$shareable = new ShareRequest();
		$shareable->code = $this->input->get_post('code');
		$shareable->username = $this->input->get_post('username');
		$shareable->message = $this->input->get_post('message');
		$shareable->file_url = $this->input->get_post('file_url');
		$shareable->destination = $this->input->get_post('destination');
		$shareable->device = $this->input->get_post('device');

		$shareable->save();
		if (strtolower($this->input->get_post('share')) === 'yes') {
			echo $shareable->sendMessage();
			exit();
		}

		echo 'Thank you. You will receive your message within 24 hours.';
		exit();
	}

	function index()
	{
	     $this->session();
		$method = $this->input->get_post('method');
		if (method_exists($this, $method)) {
			$this->$method();
		}
		else {
			show_error("No method.");
		}
	}
  function organizeVideosFromFiles($files=array())
  {
    $tags  = array();
    foreach($files as $i=>$record)
    {
      $path = pathinfo($record);
      $record = $path['filename'];
      $record = explode("_", $record);
      
      $tag = reset($record);
      $tags[] = $tag;
      
        
    }
    
    //var_dump($tags);
    
    return $tags;
  }
	function getFilesFromFolder($dir, $valid_extensions=array('png','jpg','jpeg','gif','token','mpg','mov','m4v'))
	{
	   $arDir = array();
		$d = dir($dir);
		while (false!== ($entry = $d->read())) {
		  
			if ($entry!= '.' && $entry!= '..' && is_dir($dir.$entry))
			{
				// go inside sub folders
               }
               elseif($entry!= '.' && $entry!= '..' && substr($entry, 0,1) != ".")
               {
                    $ext = strtolower(substr(strrchr($entry, "."), 1));
                    if(in_array($ext, $valid_extensions))
                    {
                         //$e = str_replace(" ","%20",);
                         $arDir[] = $dir.$entry;
                    }
               }
		}
		$d->close();
		return $arDir;
	}
	
	function admin(){
		$this->load->view('webapp/admin');
	}
	
	function twauth() {
	
	      $config = array();
	      $this->load->library('TMHOAuth',$config);
	      $this->load->library('TMHUtilities');
        $creds = array();
        $creds['twuser'] = $this->input->get_post('twuser');
        $creds['twpass'] = $this->input->get_post('twpass');
        $file_path = $this->input->get_post('twitter_file_url');
        
        
        $tmhOAuth = new tmhOAuth(array(
          'consumer_key'    => 'vTWca79MSwE2jBZqbAD3A',
          'consumer_secret' => 'Xuw28EkijObuqUNPcnpHSi088ZanFO9sk1rcgjL3iic',
        ));
        
        $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
          'x_auth_username' => $creds['twuser'],
          'x_auth_password' => $creds['twpass'],
          'x_auth_mode'     => 'client_auth'
        ));
                
        if ($code == 200) {
          $tokens = $tmhOAuth->extract_params($tmhOAuth->response['response']);
          
          $return = array();
          $return['data'] = array();
          $return['data']['screen_name'] = $tokens['screen_name'];
          $return['data']['t1'] = $tokens['oauth_token'];
          $return['data']['t2'] = $tokens['oauth_token_secret'];
          $return['data']['twurl'] = substr(md5(time().$file_path),0,7);
          $return['success'] = true;
          $return = json_encode($return);          
        } else {
          $return = array();
          $return['success'] = false;
          $return['errordata'] = htmlentities($tmhOAuth->response['response']);
          $return = json_encode($return);
        }
        
        echo $return;
        exit;
	}
	
	function tweet() {
	
	      $config = array();
	      
	      $oauth_token = $this->input->get_post('t1');
	      $oauth_token_secret = $this->input->get_post('t2');
	      $tweet_msg = $this->input->get_post('tw');
	      $tweet_msg = str_replace('ggggHASHgggg','#',$tweet_msg);
	      $slughash = $this->input->get_post('slughash');
	      $file_path = $this->input->get_post('file_path');
	      	
        $thmbit_url = 'http://thmb.it/s/smileapp/'.$slughash;
        $postdata = array('pic' => '@'.urldecode($file_path), 'hi' => 'webappuniverse');
        
        $ch = curl_init($thmbit_url);
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        
        $result = curl_exec($ch);
        curl_close($ch);
        $result_json = substr($result, 30);  //File: application/octet-stream
        $result_json = json_decode($result_json);
        
        if(isset($result_json->success) && $result_json->success===true)
        {
    	      $this->load->library('TMHOAuth',$config);
    	      $this->load->library('TMHUtilities');
    	      
    	      $connection = new tmhOAuth(array(
              'consumer_key'    => 'vTWca79MSwE2jBZqbAD3A',
              'consumer_secret' => 'Xuw28EkijObuqUNPcnpHSi088ZanFO9sk1rcgjL3iic',
              'user_token'      => $oauth_token,
              'user_secret'     => $oauth_token_secret,
            ));
            
            $code = $connection->request('POST', $connection->url('1/statuses/update'), array(
              'status' => $tweet_msg
            ));
            
            if ($code == 200) {
              $return = array();
              $return['success'] = true;
              $return['data'] = substr($result, 30);
              $return = json_encode($return);
            } else {
              $return = array();
              $return['success'] = false;
              $return['errordata'] = $connection->response['response'];
              $return = json_encode($return);
            }
	     }
	     else
	     {
	        $return = array();
          $return['success'] = false;
          $return['errordata'] = 'Problem uploading Picture';
          $return['error'] = $result_json;
          $return = json_encode($return);
	    }
      echo $return;
      exit;      
	}


}


?>
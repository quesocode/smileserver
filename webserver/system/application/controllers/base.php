<?php


class Base extends Controller
{
 
  public $css = NULL;
  public $script = NULL;
  public $content = NULL;
  public $head = 'includes/header.html';
  public $foot = 'includes/footer.html';
  public $menu = 'includes/menu.html';
  public $chead = 'includes/content_header.html';
  public $cfoot = 'includes/content_footer.html';
  
  public $flickr_apikey = '24388d661c70673534a721984e4f39fb';
  public $flickr_secret = '16586ccb3e0d849a';
  
  function __construct()
  {
    parent::Controller();
    $this->vars('links',array());
    $this->vars('script_src',array());
    $this->session();
    $this->vars('logged',$this->isLogged());
    $this->user = $this->session('user');
  }
  
  function index ()
  {
    if($this->isLogged())
    {
      $this->load->helper('url');
	    redirect('/backstage/');

    }
    
    
    $data = $this->vars;
    $r = $this->input->get_post('r');
    $data['redirect'] = $r ? $r : '/settings';
    $data['css'] = $this->load->view('styles/webapp.css', $data, true);
    
    

    
    
    $this->load->view('pages/home.html', $data);
  }
  
  function view($content=NULL)
  {
     $this->addStyle('styles/reset.css');
    $this->addStyle('styles/text.css');
    $this->addStyle('styles/grid.css');
    $this->addStyle('styles/lead.css');
    
    $menu = $this->menu();
    $header = $this->head();
    $footer = $this->foot();
    $content_head = $this->chead();
    $content_foot = $this->cfoot();
    
    if(!is_null($header)) $this->content .= $this->load->view($header, $this->vars, true);
    if(!is_null($menu)) $this->content .= $this->load->view($menu, $this->vars, true);
    

    $this->content .= $this->load->view('includes/content_header.html', $this->vars, true);
    $this->content .= $content;
    $this->content .= $this->load->view('includes/content_footer.html', $this->vars, true);
    if(!is_null($footer)) $this->content .= $this->load->view($footer, $this->vars, true);
    
    // output
    echo $this->content;
  }
  
  function addStyle ($style=NULL, $data=array())
  {
    $css = $this->vars('css');
    if(!is_null($style)) $css .= "\r\n" .$this->load->view($style, $this->vars+$data, true);
    $this->vars('css',$css);
  }
  function addScript ($script=NULL,$data=array())
  {
    $str = $this->vars('script');
    if(!is_null($script)) $str .= "\r\n" . $this->load->view($script, $this->vars+$data, true);
    $this->vars('script',$str);
  }
  function addLink ($link=NULL,$media='screen',$type='text/css',$rel='stylesheet')
  {
    
    $links = $this->vars('links');
    $links[] = array('rel'=>$rel,'href'=>$link,'media'=>$media,'type'=>$type);
    $this->vars('links',$links);
  }
  function addScriptSrc ($link=NULL,$type='text/javascript')
  {
    
    $srcs = $this->vars('script_src');
    $srcs[] = array('src'=>$link,'type'=>$type);
    $this->vars('script_src',$srcs);
  }
  public function initVars()
	{
		// get the requesting url
		//$app_url = isset($_SERVER['HTTP_REFERER']) ? $this->parseUrl($_SERVER['HTTP_REFERER']) : NULL;
		$vars = array();
		
		
		
		return $vars;
	}
	function loadFile($file)
	{
		$this->load->helper('file');
		$len = filesize($file);
		$filepath = pathinfo($file);
		$filename = $filepath['basename'];
		//var_dump($filepath);
		$mime = get_mime_by_extension($filename);
		$this->output->set_header('Content-Type: ' . $mime);
		$this->output->set_header("Content-Length: $len;\n");
		$this->output->set_header('Last-Modified: '.gmdate('D, d M Y H:i:s', filectime($file)).' GMT');
		$data = file_get_contents($file);
		echo $data;
	}
	public function isLogged($id=NULL)
	{
	   $user = $this->session('user');
	   if($user && isset($user['id']))
	   {
	     return $user['id'];
	   }
	   return false;
	}
	public function forceLogin($redirect=NULL)
	{
	 
	 if(!$this->isLogged())
	 {
	   $redirect = $redirect ? '?r=' . $redirect : null; 
	   $this->session('user',NULL);
	   $this->load->helper('url');
	   redirect('/login/'.$redirect);
	   exit;
	 }
	 
	}
	public function head($temp=NULL)
	{
	   if(!is_null($temp)) $this->head = $temp === false ? NULL : $temp;
	   
	   return $this->head;
	}
	public function foot($temp=NULL)
	{
	   if(!is_null($temp)) $this->foot = $temp === false ? NULL : $temp;
	   
	   return $this->foot;
	}
	public function menu($temp=NULL)
	{
	   if(!is_null($temp)) $this->menu = $temp === false ? NULL : $temp;
	   
	   return $this->menu;
	}
	public function chead($temp=NULL)
	{
	   if(!is_null($temp)) $this->chead = $temp === false ? NULL : $temp;
	   
	   return $this->chead;
	}
	public function cfoot($temp=NULL)
	{
	   if(!is_null($temp)) $this->cfoot = $temp === false ? NULL : $temp;
	   
	   return $this->cfoot;
	}
	public function vars($key, $value=NULL)
	{
	   if(is_null($value) && isset($this->vars[$key]))
	   {
	     return $this->vars[$key];
	   }
	   $this->vars[$key] = $value;
	}
	public function toJson ($data)
	{
	   $json = json_encode($data);
  	   
  	   echo $this->json($json);
	}
	public function json($json)
	{
	   $len = strlen($json);
  	   $this->output->set_header('Content-Type: ' . 'application/javascript');
  	   $this->output->set_header("Content-Length: $len;\n");
  	   return $json;
	}
	public function parseUrl($url)
   {
   $r  = "^(?:(?P<scheme>\w+)://)?";
   $r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";

   $ip="(?:[0-9]{1,3}+\.){3}+[0-9]{1,3}";//ip check
   $s="(?P<subdomain>[-\w\.]+)\.)?";//subdomain
   $d="(?P<domain>[-\w]+\.)";//domain
   $e="(?P<extension>\w+)";//extension

   $r.="(?P<host>(?(?=".$ip.")(?P<ip>".$ip.")|(?:".$s.$d.$e."))";

   $r .= "(?::(?P<port>\d+))?";
   $r .= "(?P<path>(.*/*)(?P<file>\w+(?:\..*)?)?)?";
   $r .= "(?:\?(?P<arg>[\w=&]+))?";
   $r .= "(?:#(?P<anchor>\w+))?";
   $r = "!$r!";   // Delimiters

    preg_match($r, $url,$out);
    return $out;
   }
  // session
    
  public function session()
  {
    
		$args = func_get_args();
		if (empty($args) && (!isset($this->sessid) || !$this->sessid) ) {
			if ($this->input->get_post('token')) {
				//session_id($this->input->get_post('token'));
			}
			else {
				
				//session_id(md5(uniqid(mt_rand(), true)));
			}
			//$this->sessid = session_id();
			session_start();
			$this->sessid = session_id();
			//session_regenerate_id();
			
		}
		
		if (empty($args) && isset($this->sessid) && isset($_SESSION)) 
		{
			return $_SESSION;
		}
		$key = reset($args);
		if (count($args) > 1) 
		{
		  
		  $value = $args[1];
		}
		else
		{
		
		  return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
		}
		if (is_null($value) && isset($_SESSION[$key])) {
		  
			unset($_SESSION[$key]);
		}
		elseif ($value) {
			$_SESSION[$key] = $value;
		}
	}
	
	function _detect_method()
  {
  	$method = strtolower($_SERVER['REQUEST_METHOD']);
  	
  	if(in_array($method, array('get', 'delete', 'post', 'put')))
  	{
      	return $method;
  	}
  
  	return 'get';
  }
  
       /* 
     Description : A function with a very simple but powerful xor method to encrypt 
                   and/or decrypt a string with an unknown key. Implicitly the key is 
                   defined by the string itself in a character by character way. 
                   There are 4 items to compose the unknown key for the character 
                   in the algorithm 
                   1.- The ascii code of every character of the string itself 
                   2.- The position in the string of the character to encrypt 
                   3.- The length of the string that include the character 
                   4.- Any special formula added by the programmer to the algorithm 
                       to calculate the key to use 
     */ 
     function secret($Str_Message) { 
     //Function : encrypt/decrypt a string message v.1.0  without a known key 
     //Author   : Aitor Solozabal Merino (spain) 
     //Email    : aitor-3@euskalnet.net 
     //Date     : 01-04-2005 
         $Len_Str_Message=STRLEN($Str_Message); 
         $Str_Encrypted_Message=""; 
         FOR ($Position = 0;$Position<$Len_Str_Message;$Position++){ 
             // long code of the function to explain the algoritm 
             //this function can be tailored by the programmer modifyng the formula 
             //to calculate the key to use for every character in the string. 
             $Key_To_Use = (($Len_Str_Message+$Position)+1); // (+5 or *3 or ^2) 
             //after that we need a module division because can´t be greater than 255 
             $Key_To_Use = (255+$Key_To_Use) % 255; 
             $Byte_To_Be_Encrypted = SUBSTR($Str_Message, $Position, 1); 
             $Ascii_Num_Byte_To_Encrypt = ORD($Byte_To_Be_Encrypted); 
             $Xored_Byte = $Ascii_Num_Byte_To_Encrypt ^ $Key_To_Use;  //xor operation 
             $Encrypted_Byte = CHR($Xored_Byte); 
             $Str_Encrypted_Message .= $Encrypted_Byte; 
             
             //short code of  the function once explained 
             //$str_encrypted_message .= chr((ord(substr($str_message, $position, 1))) ^ ((255+(($len_str_message+$position)+1)) % 255)); 
         } 
         RETURN $Str_Encrypted_Message; 
     } //end function 

  
}



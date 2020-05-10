<?php


class Swmpd
{
  const AUTH_BASIC     = 'BASIC';
  const AUTH_CUSTOM     = 'CUSTOM';
  public static $cache = array();
  public static $url = NULL;
  
  public static function parseUrl($url)
   {
   $r  = "^(?:(?P<scheme>\w+)://)?";
   $r .= "(?:(?P<login>\w+):(?P<pass>\w+)@)?";

   $ip="(?:[0-9]{1,3}+\.){3}+[0-9]{1,3}";//ip check
   $s="(?P<subdomain>[-\w\.]+)\.)?";//subdomain
   $d="(?P<domain>[-\w]+\.)";//domain
   $e="(?P<extension>\w+)";//extension

   $r.="(?P<host>(?(?=".$ip.")(?P<ip>".$ip.")|(?:".$s.$d.$e."))";

   $r .= "(?::(?P<port>\d+))?";
   $r .= "(?P<path>[\w/]*/(?P<file>\w+(?:\.\w+)?)?)?";
   $r .= "(?:\?(?P<arg>[\w=&]+))?";
   $r .= "(?:#(?P<anchor>\w+))?";
   $r = "!$r!";   // Delimiters
    preg_match($r, $url,$out);
    return $out;
   }
  public static function setBaseUrl($url=NULL, $protocol = "http://")
  {
  	
  	if(is_null($url) && is_null(self::$url))
  	{
          
		$current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

  		$parsed = self::parseUrl($current_url);

  		$url = 'api.' . $parsed['domain']. $parsed['extension'];
  		

  	}
  	self::$url = $url;
    self::_setCache('base_url', $url);
    self::_setCache('protocol', $protocol);
  }
  public static function setCredentials($username='', $password = '', $auth_method=self::AUTH_CUSTOM)
  {

    self::_setCache('apiuser', $username);
    self::_setCache('apikey', $password);
    if($username AND $password AND $auth_method === self::AUTH_BASIC)
    {
      self::cache("auth", "$username:$password@");
    }
    elseif($username AND $password AND $auth_method === self::AUTH_CUSTOM)
    {

      self::cache("suffix", "username=$username&password=$password");
    }
  }
  
  public static function get($uri,$return_raw=false)
  {
    $request = new RESTClient(self::generateUrl($uri));
    if(!$return_raw)
    {
	    $request->setFormat(self::getFormat());
	    
	    try 
	    {
	        return self::execute($request, self::generateUrl($uri));
	    } 
	    catch (HTTP_Request2_Exception $e) 
	    {
	        throw $e;
	    }
    }
    else
    {
    	$controller = self::cache("controller");
    
    
	    $response = $request->send();
	    self::cache("response", $response);
	    if (200 == $response->getStatus()) {
	        $data = $response->getBody();
	    } else {
	        $data = NULL;
	    }
		
	    self::addToCache(array('uri'=>$uri, "data"=>$data), "requests");
	    
	    
	    return $data;
    }
  }
  public static function put($uri, $data=array(), $files=NULL,$return_raw=false)
  {
    $request = new RESTClient(self::generateUrl($uri), RESTClient::METHOD_PUT);
    $request->setFormat(self::getFormat());
    $request->setBody(http_build_query($data));
    if(is_array($files) && !empty($files))
    {
    	foreach($files as $i=>$file)
    	{
    		$request->addUpload($i, $file['tmp_name'], $file['name'], $file['type']);
    	}
    }
    if(!$return_raw)
    {
	    try 
	    {
	        return self::execute($request, self::generateUrl($uri));
	    } 
	    catch (HTTP_Request2_Exception $e) 
	    {
	        throw $e;
	    }
    }
    else
    {
    	$controller = self::cache("controller");
    
    
	    $response = $request->send();
	    self::cache("response", $response);
	    if (200 == $response->getStatus()) {
	        $data = $response->getBody();
	    } else {
	        $data = NULL;
	    }
		
	    self::addToCache(array('uri'=>$uri, "data"=>$data), "requests");
	    
	    
	    return $data;
    }
  }
  public static function post($uri, $data=array(), $files=NULL,$return_raw=false)
  {
    $request = new RESTClient(self::generateUrl($uri), RESTClient::METHOD_POST);
    error_log("posting: " . self::generateUrl($uri));
    $request->setFormat(self::getFormat());
    $request->addData($data);
    if(is_array($files) && !empty($files))
    {
    	//error_log(print_r($files, true));
    	foreach($files as $i=>$file)
    	{
    		$tmp_name = is_array($file['tmp_name']) ? $file['tmp_name']['file']['media'] : $file['tmp_name'];
    		$name = is_array($file['name']) ? $file['name']['file']['media'] : $file['name'];
    		$type = is_array($file['type']) ? $file['type']['file']['media'] : $file['type'];
    		$request->addUpload($i, $tmp_name, $name, $type);
    	}
    }
    if(!$return_raw)
    {
	    try 
	    {
	        return self::execute($request, self::generateUrl($uri));
	    } 
	    catch (HTTP_Request2_Exception $e) 
	    {
	        throw $e;
	    }
    }
    else
    {
    	$controller = self::cache("controller");
    
    
	    $response = $request->send();
	    self::cache("response", $response);
	    if (200 == $response->getStatus()) {
	        $data = $response->getBody();
	    } else {
	        $data = NULL;
	    }
		
	    self::addToCache(array('uri'=>$uri, "data"=>$data), "requests");
	    
	    
	    return $data;
    }
  }
  public static function delete($uri, $data=array(),$return_raw=false)
  {
    $request = new RESTClient(self::generateUrl($uri), RESTClient::METHOD_DELETE);
    $request->setFormat(self::getFormat());
    
    if(!$return_raw)
    {
	    try 
	    {
	        return self::execute($request, self::generateUrl($uri));
	    } 
	    catch (HTTP_Request2_Exception $e) 
	    {
	        throw $e;
	    }
    }
    else
    {
    	$controller = self::cache("controller");
    
    
	    $response = $request->send();
	    self::cache("response", $response);
	    if (200 == $response->getStatus()) {
	        $data = $response->getBody();
	    } else {
	        $data = NULL;
	    }
		
	    self::addToCache(array('uri'=>$uri, "data"=>$data), "requests");
	    
	    
	    return $data;
    }
  }
  public static function requests()
  {
    return self::cache("requests");
  }
  public static function debug($return=false)
  {
    $str = "<pre>" . print_r(self::requests(), true) . "</pre>";
    if($return)
    {
      return $str;
    }
    else
    {
      echo $str;
    }
  }
  private static function execute($request, $uri)
  {
    $controller = self::cache("controller");
    
    
    $response = $request->send();
    self::cache("response", $response);
    if (200 == $response->getStatus()) {
        $data = $response->parse(self::getFormat());
    } else {
        $data = false;
    }

    self::addToCache(array('uri'=>$uri, "data"=>$data), "requests");
    
    
    return $data;
  }
  
  
  public static function generateUrl($uri)
  {

	  $url_arr = array();
  	if(is_null(self::$url)) self::setBaseUrl();
    $suffix = self::cache("suffix");
    $burl = parse_url($uri);

    if(isset($burl['query'])) parse_str($burl['query'], $url_arr);
    parse_str($suffix, $suffix_arr);
    $new_arr = array_merge($suffix_arr, $url_arr);
    $suffix = http_build_query($new_arr);
    $uri = $burl['path'];
    $suffix = strpos($uri, '?') > -1 ? '&' . $suffix : '?' . $suffix;
    $uri = strpos($uri, '/') !== 0 ? '/' . $uri : $uri;
    $url = self::cache("protocol") . self::cache("auth") . self::cache("base_url") . $uri . $suffix;
    self::cache("url", $url);

    return $url;
  }
  public static function getRawResponse()
  {
    return self::cache('response')->getBody();
  }
  public static function response()
  {
    return self::cache('response')->getBody();
  }
  public static function setFormat($format = "xml")
  {
    self::cache("format", $format);
  }
  public static function getFormat()
  {
    self::cache("format");
  }
  public static function init()
  {
    self::_setCache('start_time', microtime(true));
    self::setBaseUrl();
    self::setFormat();
  }
  public static function addToCache($value=NULL, $cache_array_index=NULL, $value_index=NULL)
  {
    $cache_array_index = is_null($cache_array_index) ? uniqid("array") : $cache_array_index;
    if(!self::cache($cache_array_index)) self::cache($cache_array_index, array());
    $array = self::cache($cache_array_index);
    if(is_null($value_index))
    {
      $array[] = $value;
    }
    else
    {
      $array[$value_index] = $value;
    }
    self::cache($cache_array_index, $array);
    return $cache_array_index;
  }
  public static function cache ($name, $value = NULL)
	{
	  if(isset($name) && !is_null($value))
	  {
  		self::_setCache($name, $value);
  		return $value;
		}
		else
		{
		  return self::_getCache($name);
		}
	}
  private static function _setCache ($name, $value = NULL)
	{
		self::$cache[$name] = $value;
	}
	private static function _getCache ($name)
	{
		return isset(self::$cache[$name]) ? self::$cache[$name] : NULL;
	}
	public static function getModelName($class_name = NULL)
	{
    $object_name_lower = strtolower($class_name);
    $models = self::_getCache('models');
    return isset($models[$object_name_lower]) ? $models[$object_name_lower] : NULL;
	}
	public static function getFriendlyModelNames()
	{
	   $models = self::_getCache('models');
	   foreach($models as $k=>$v)
	   {
	     $models[$k] = array('name'=>substr(preg_replace('|([A-Z])|', ' $1', $v), 1), 'class'=>$v);
	   }
	   return $models;
	}
	public static function cacheModelNames($models=array())
	{
	   // setup a static array to use in determining a models proper name including capitalizations
	   $model_names = array_change_key_case(array_combine(array_values($models), $models));
	   foreach($model_names as $key=>$name)
	   {
	     $model_names[$key . "s"] = $name;
	   }
	   Columbus::_setCache('models', $model_names);
	}
}



class RESTClient extends HTTP_Request2
{
  function addData($data, $value=NULL)
  {
    $this->addPostParameter($data, $value);
  }
}


/**
 * Class representing a HTTP request
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2008, 2009, Alexey Borzov <avb@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTTP
 * @package    HTTP_Request2
 * @author     Alexey Borzov <avb@php.net>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: Request2.php 278226 2009-04-03 21:32:48Z avb $
 * @link       http://pear.php.net/package/HTTP_Request2
 */

/**
 * A class representing an URL as per RFC 3986.
 */




/**
 * Class representing a HTTP request
 *
 * @category   HTTP
 * @package    HTTP_Request2
 * @author     Alexey Borzov <avb@php.net>
 * @version    Release: 0.4.1
 * @link       http://tools.ietf.org/html/rfc2616#section-5
 */
class HTTP_Request2 
{
   /**#@+
    * Constants for HTTP request methods
    *
    * @link http://tools.ietf.org/html/rfc2616#section-5.1.1
    */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
   /**#@-*/

   /**#@+
    * Constants for HTTP authentication schemes 
    *
    * @link http://tools.ietf.org/html/rfc2617
    */
    const AUTH_BASIC  = 'basic';
    const AUTH_DIGEST = 'digest';
   /**#@-*/

   /**
    * Regular expression used to check for invalid symbols in RFC 2616 tokens
    * @link http://pear.php.net/bugs/bug.php?id=15630
    */
    const REGEXP_INVALID_TOKEN = '![\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]!';

   /**
    * Regular expression used to check for invalid symbols in cookie strings
    * @link http://pear.php.net/bugs/bug.php?id=15630
    * @link http://cgi.netscape.com/newsref/std/cookie_spec.html
    */
    const REGEXP_INVALID_COOKIE = '/[\s,;]/';

   /**
    * Fileinfo magic database resource
    * @var  resource
    * @see  detectMimeType()
    */
    private static $_fileinfoDb;

   /**
    * Observers attached to the request (instances of SplObserver)
    * @var  array
    */
    protected $observers = array();

   /**
    * Request URL
    * @var  Net_URL2
    */
    protected $url;

   /**
    * Request method
    * @var  string
    */
    protected $method = self::METHOD_GET;

   /**
    * Authentication data
    * @var  array
    * @see  getAuth()
    */
    protected $auth;

   /**
    * Request headers
    * @var  array
    */
    protected $headers = array();

   /**
    * Configuration parameters
    * @var  array
    * @see  setConfig()
    */
    protected $config = array(
        'adapter'           => 'Request2_Adapter_Socket',
        'connect_timeout'   => 10,
        'timeout'           => 0,
        'use_brackets'      => true,
        'protocol_version'  => '1.1',
        'buffer_size'       => 16384,
        'store_body'        => true,

        'proxy_host'        => '',
        'proxy_port'        => '',
        'proxy_user'        => '',
        'proxy_password'    => '',
        'proxy_auth_scheme' => self::AUTH_BASIC,

        'ssl_verify_peer'   => true,
        'ssl_verify_host'   => true,
        'ssl_cafile'        => null,
        'ssl_capath'        => null,
        'ssl_local_cert'    => null,
        'ssl_passphrase'    => null,

        'digest_compat_ie'  => false
    );
    protected $mime_types = array(
  'aif'  => 'audio/x-aiff',
  'aiff' => 'audio/x-aiff',
  'avi'  => 'video/avi',
  'bmp'  => 'image/bmp',
  'bz2'  => 'application/x-bz2',
  'csv'  => 'text/csv',
  'dmg'  => 'application/x-apple-diskimage',
  'doc'  => 'application/msword',
  'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'eml'  => 'message/rfc822',
  'aps'  => 'application/postscript',
  'exe'  => 'application/x-ms-dos-executable',
  'flv'  => 'video/x-flv',
  'gif'  => 'image/gif',
  'gz'   => 'application/x-gzip',
  'hqx'  => 'application/stuffit',
  'htm'  => 'text/html',
  'html' => 'text/html',
  'jar'  => 'application/x-java-archive',
  'jpeg' => 'image/jpeg',
  'jpg'  => 'image/jpeg',
  'm3u'  => 'audio/x-mpegurl',
  'm4a'  => 'audio/mp4',
  'mdb'  => 'application/x-msaccess',
  'mid'  => 'audio/midi',
  'midi' => 'audio/midi',
  'mov'  => 'video/quicktime',
  'mp3'  => 'audio/mpeg',
  'mp4'  => 'video/mp4',
  'mpeg' => 'video/mpeg',
  'mpg'  => 'video/mpeg',
  'odg'  => 'vnd.oasis.opendocument.graphics',
  'odp'  => 'vnd.oasis.opendocument.presentation',
  'odt'  => 'vnd.oasis.opendocument.text',
  'ods'  => 'vnd.oasis.opendocument.spreadsheet',
  'ogg'  => 'audio/ogg',
  'pdf'  => 'application/pdf',
  'png'  => 'image/png',
  'ppt'  => 'application/vnd.ms-powerpoint',
  'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
  'ps'   => 'application/postscript',
  'rar'  => 'application/x-rar-compressed',
  'rtf'  => 'application/rtf',
  'tar'  => 'application/x-tar',
  'sit'  => 'application/x-stuffit',
  'svg'  => 'image/svg+xml',
  'tif'  => 'image/tiff',
  'tiff' => 'image/tiff',
  'ttf'  => 'application/x-font-truetype',
  'txt'  => 'text/plain',
  'vcf'  => 'text/x-vcard',
  'wav'  => 'audio/wav',
  'wma'  => 'audio/x-ms-wma',
  'wmv'  => 'audio/x-ms-wmv',
  'xls'  => 'application/excel',
  'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'xml'  => 'application/xml',
  'zip'  => 'application/zip'
);

   /**
    * Last event in request / response handling, intended for observers
    * @var  array
    * @see  getLastEvent()
    */
    protected $lastEvent = array(
        'name' => 'start',
        'data' => null
    );

   /**
    * Request body
    * @var  string|resource
    * @see  setBody()
    */
    protected $body = '';

   /**
    * Array of POST parameters
    * @var  array
    */
    protected $postParams = array();

   /**
    * Array of file uploads (for multipart/form-data POST requests) 
    * @var  array
    */
    protected $uploads = array();

   /**
    * Adapter used to perform actual HTTP request
    * @var  HTTP_Request2_Adapter
    */
    protected $adapter;


   /**
    * Constructor. Can set request URL, method and configuration array.
    *
    * Also sets a default value for User-Agent header. 
    *
    * @param    string|Net_Url2     Request URL
    * @param    string              Request method
    * @param    array               Configuration for this Request instance
    */
    public function __construct($url = null, $method = self::METHOD_GET, array $config = array())
    {
        if (!empty($url)) {
            $this->setUrl($url);
        }
        if (!empty($method)) {
            $this->setMethod($method);
        }
        $this->setConfig($config);
        $this->setHeader('user-agent', 'Columbus/0.0.1 ' .
                         '(http://api.columbusapp.com) ' .
                         'PHP/' . phpversion());
    }

   /**
    * Sets the URL for this request
    *
    * If the URL has userinfo part (username & password) these will be removed
    * and converted to auth data. If the URL does not have a path component,
    * that will be set to '/'.
    *
    * @param    string|Net_URL2 Request URL
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception
    */
    public function setUrl($url)
    {
        if (is_string($url)) {
            $url = new Net_URL2($url);
        }
        if (!$url instanceof Net_URL2) {
            throw new HTTP_Request2_Exception('Parameter is not a valid HTTP URL');
        }
        // URL contains username / password?
        if ($url->getUserinfo()) {
            $username = $url->getUser();
            $password = $url->getPassword();
            $this->setAuth(rawurldecode($username), $password? rawurldecode($password): '');
            $url->setUserinfo('');
        }
        if ('' == $url->getPath()) {
            $url->setPath('/');
        }
        $this->url = $url;

        return $this;
    }

   /**
    * Returns the request URL
    *
    * @return   Net_URL2
    */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
    * Sets the request Accept header
    *
    * @return NULL
    */
    public function setFormat($format)
    {
        $format = array_key_exists($format, $this->mime_types) ? $this->mime_types[$format] : $format;
        $this->setHeader('accept', $format); 
    }

   /**
    * Sets the request method
    *
    * @param    string
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception if the method name is invalid
    */
    public function setMethod($method)
    {
        // Method name should be a token: http://tools.ietf.org/html/rfc2616#section-5.1.1
        if (preg_match(self::REGEXP_INVALID_TOKEN, $method)) {
            throw new HTTP_Request2_Exception("Invalid request method '{$method}'");
        }
        $this->method = $method;

        return $this;
    }

   /**
    * Returns the request method
    *
    * @return   string
    */
    public function getMethod()
    {
        return $this->method;
    }

   /**
    * Sets the configuration parameter(s)
    *
    * The following parameters are available:
    * <ul>
    *   <li> 'adapter'           - adapter to use (string)</li>
    *   <li> 'connect_timeout'   - Connection timeout in seconds (integer)</li>
    *   <li> 'timeout'           - Total number of seconds a request can take.
    *                              Use 0 for no limit, should be greater than 
    *                              'connect_timeout' if set (integer)</li>
    *   <li> 'use_brackets'      - Whether to append [] to array variable names (bool)</li>
    *   <li> 'protocol_version'  - HTTP Version to use, '1.0' or '1.1' (string)</li>
    *   <li> 'buffer_size'       - Buffer size to use for reading and writing (int)</li>
    *   <li> 'store_body'        - Whether to store response body in response object.
    *                              Set to false if receiving a huge response and
    *                              using an Observer to save it (boolean)</li>
    *   <li> 'proxy_host'        - Proxy server host (string)</li>
    *   <li> 'proxy_port'        - Proxy server port (integer)</li>
    *   <li> 'proxy_user'        - Proxy auth username (string)</li>
    *   <li> 'proxy_password'    - Proxy auth password (string)</li>
    *   <li> 'proxy_auth_scheme' - Proxy auth scheme, one of HTTP_Request2::AUTH_* constants (string)</li>
    *   <li> 'ssl_verify_peer'   - Whether to verify peer's SSL certificate (bool)</li>
    *   <li> 'ssl_verify_host'   - Whether to check that Common Name in SSL
    *                              certificate matches host name (bool)</li>
    *   <li> 'ssl_cafile'        - Cerificate Authority file to verify the peer
    *                              with (use with 'ssl_verify_peer') (string)</li>
    *   <li> 'ssl_capath'        - Directory holding multiple Certificate 
    *                              Authority files (string)</li>
    *   <li> 'ssl_local_cert'    - Name of a file containing local cerificate (string)</li>
    *   <li> 'ssl_passphrase'    - Passphrase with which local certificate
    *                              was encoded (string)</li>
    *   <li> 'digest_compat_ie'  - Whether to imitate behaviour of MSIE 5 and 6
    *                              in using URL without query string in digest
    *                              authentication (boolean)</li>
    * </ul>
    *
    * @param    string|array    configuration parameter name or array
    *                           ('parameter name' => 'parameter value')
    * @param    mixed           parameter value if $nameOrConfig is not an array
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception If the parameter is unknown
    */
    public function setConfig($nameOrConfig, $value = null)
    {
        if (is_array($nameOrConfig)) {
            foreach ($nameOrConfig as $name => $value) {
                $this->setConfig($name, $value);
            }

        } else {
            if (!array_key_exists($nameOrConfig, $this->config)) {
                throw new HTTP_Request2_Exception(
                    "Unknown configuration parameter '{$nameOrConfig}'"
                );
            }
            $this->config[$nameOrConfig] = $value;
        }

        return $this;
    }

   /**
    * Returns the value(s) of the configuration parameter(s)
    *
    * @param    string  parameter name
    * @return   mixed   value of $name parameter, array of all configuration 
    *                   parameters if $name is not given
    * @throws   HTTP_Request2_Exception If the parameter is unknown
    */
    public function getConfig($name = null)
    {
        if (null === $name) {
            return $this->config;
        } elseif (!array_key_exists($name, $this->config)) {
            throw new HTTP_Request2_Exception(
                "Unknown configuration parameter '{$name}'"
            );
        }
        return $this->config[$name];
    }

   /**
    * Sets the autentification data
    *
    * @param    string  user name
    * @param    string  password
    * @param    string  authentication scheme
    * @return   HTTP_Request2
    */ 
    public function setAuth($user, $password = '', $scheme = self::AUTH_BASIC)
    {
        if (empty($user)) {
            $this->auth = null;
        } else {
            $this->auth = array(
                'user'     => (string)$user,
                'password' => (string)$password,
                'scheme'   => $scheme
            );
        }

        return $this;
    }

   /**
    * Returns the authentication data
    *
    * The array has the keys 'user', 'password' and 'scheme', where 'scheme'
    * is one of the HTTP_Request2::AUTH_* constants.
    *
    * @return   array
    */
    public function getAuth()
    {
        return $this->auth;
    }

   /**
    * Sets request header(s)
    *
    * The first parameter may be either a full header string 'header: value' or
    * header name. In the former case $value parameter is ignored, in the latter 
    * the header's value will either be set to $value or the header will be
    * removed if $value is null. The first parameter can also be an array of
    * headers, in that case method will be called recursively.
    *
    * Note that headers are treated case insensitively as per RFC 2616.
    * 
    * <code>
    * $req->setHeader('Foo: Bar'); // sets the value of 'Foo' header to 'Bar'
    * $req->setHeader('FoO', 'Baz'); // sets the value of 'Foo' header to 'Baz'
    * $req->setHeader(array('foo' => 'Quux')); // sets the value of 'Foo' header to 'Quux'
    * $req->setHeader('FOO'); // removes 'Foo' header from request
    * </code>
    *
    * @param    string|array    header name, header string ('Header: value')
    *                           or an array of headers
    * @param    string|null     header value, header will be removed if null
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception
    */
    public function setHeader($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                if (is_string($k)) {
                    $this->setHeader($k, $v);
                } else {
                    $this->setHeader($v);
                }
            }
        } else {
            if (null === $value && strpos($name, ':')) {
                list($name, $value) = array_map('trim', explode(':', $name, 2));
            }
            // Header name should be a token: http://tools.ietf.org/html/rfc2616#section-4.2
            if (preg_match(self::REGEXP_INVALID_TOKEN, $name)) {
                throw new HTTP_Request2_Exception("Invalid header name '{$name}'");
            }
            // Header names are case insensitive anyway
            $name = strtolower($name);
            if (null === $value) {
                unset($this->headers[$name]);
            } else {
                $this->headers[$name] = $value;
            }
        }
        
        return $this;
    }

   /**
    * Returns the request headers
    *
    * The array is of the form ('header name' => 'header value'), header names
    * are lowercased
    *
    * @return   array
    */
    public function getHeaders()
    {
        return $this->headers;
    }

   /**
    * Appends a cookie to "Cookie:" header
    *
    * @param    string  cookie name
    * @param    string  cookie value
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception
    */
    public function addCookie($name, $value)
    {
        $cookie = $name . '=' . $value;
        if (preg_match(self::REGEXP_INVALID_COOKIE, $cookie)) {
            throw new HTTP_Request2_Exception("Invalid cookie: '{$cookie}'");
        }
        $cookies = empty($this->headers['cookie'])? '': $this->headers['cookie'] . '; ';
        $this->setHeader('cookie', $cookies . $cookie);

        return $this;
    }

   /**
    * Sets the request body
    *
    * @param    string  Either a string with the body or filename containing body
    * @param    bool    Whether first parameter is a filename
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception
    */
    public function setBody($body, $isFilename = false)
    {
        if (!$isFilename) {
            $this->body = (string)$body;
        } else {
            if (!($fp = @fopen($body, 'rb'))) {
                throw new HTTP_Request2_Exception("Cannot open file {$body}");
            }
            $this->body = $fp;
            if (empty($this->headers['content-type'])) {
                $this->setHeader('content-type', self::detectMimeType($body));
            }
        }

        return $this;
    }

   /**
    * Returns the request body
    *
    * @return   string|resource|HTTP_Request2_MultipartBody
    */
    public function getBody()
    {
        if (self::METHOD_POST == $this->method && 
            (!empty($this->postParams) || !empty($this->uploads))
        ) {
            if ('application/x-www-form-urlencoded' == $this->headers['content-type']) {
                $body = http_build_query($this->postParams, '', '&');
                if (!$this->getConfig('use_brackets')) {
                    $body = preg_replace('/%5B\d+%5D=/', '=', $body);
                }
                // support RFC 3986 by not encoding '~' symbol (request #15368)
                return str_replace('%7E', '~', $body);

            } elseif ('multipart/form-data' == $this->headers['content-type']) {
                
                return new HTTP_Request2_MultipartBody(
                    $this->postParams, $this->uploads, $this->getConfig('use_brackets')
                );
            }
        }
        return $this->body;
    }

   /**
    * Adds a file to form-based file upload
    *
    * Used to emulate file upload via a HTML form. The method also sets
    * Content-Type of HTTP request to 'multipart/form-data'.
    *
    * If you just want to send the contents of a file as the body of HTTP
    * request you should use setBody() method.
    *
    * @param    string  name of file-upload field
    * @param    mixed   full name of local file
    * @param    string  filename to send in the request 
    * @param    string  content-type of file being uploaded
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception
    */
    public function addUpload($fieldName, $filename, $sendFilename = null,
                              $contentType = null)
    {
        if (!is_array($filename)) {
            if (!($fp = @fopen($filename, 'rb'))) {
                throw new HTTP_Request2_Exception("Cannot open file {$filename}");
            }
            $this->uploads[$fieldName] = array(
                'fp'        => $fp,
                'filename'  => empty($sendFilename)? basename($filename): $sendFilename,
                'size'      => filesize($filename),
                'type'      => empty($contentType)? self::detectMimeType($filename): $contentType
            );
        } else {
            $fps = $names = $sizes = $types = array();
            foreach ($filename as $f) {
                if (!is_array($f)) {
                    $f = array($f);
                }
                if (!($fp = @fopen($f[0], 'rb'))) {
                    throw new HTTP_Request2_Exception("Cannot open file {$f[0]}");
                }
                $fps[]   = $fp;
                $names[] = empty($f[1])? basename($f[0]): $f[1];
                $sizes[] = filesize($f[0]);
                $types[] = empty($f[2])? self::detectMimeType($f[0]): $f[2];
            }
            $this->uploads[$fieldName] = array(
                'fp' => $fps, 'filename' => $names, 'size' => $sizes, 'type' => $types
            );
        }
        if (empty($this->headers['content-type']) ||
            'application/x-www-form-urlencoded' == $this->headers['content-type']
        ) {
            $this->setHeader('content-type', 'multipart/form-data');
        }

        return $this;
    }

   /**
    * Adds POST parameter(s) to the request.
    *
    * @param    string|array    parameter name or array ('name' => 'value')
    * @param    mixed           parameter value (can be an array)
    * @return   HTTP_Request2
    */
    public function addPostParameter($name, $value = null)
    {
        if (!is_array($name)) {
            $this->postParams[$name] = $value;
        } else {
            foreach ($name as $k => $v) {
                $this->addPostParameter($k, $v);
            }
        }
        if (empty($this->headers['content-type'])) {
            $this->setHeader('content-type', 'application/x-www-form-urlencoded');
        }

        return $this;
    }

   /**
    * Attaches a new observer
    *
    * @param    SplObserver
    */
    public function attach(SplObserver $observer)
    {
        foreach ($this->observers as $attached) {
            if ($attached === $observer) {
                return;
            }
        }
        $this->observers[] = $observer;
    }

   /**
    * Detaches an existing observer
    *
    * @param    SplObserver
    */
    public function detach(SplObserver $observer)
    {
        foreach ($this->observers as $key => $attached) {
            if ($attached === $observer) {
                unset($this->observers[$key]);
                return;
            }
        }
    }

   /**
    * Notifies all observers
    */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

   /**
    * Sets the last event
    *
    * Adapters should use this method to set the current state of the request
    * and notify the observers.
    *
    * @param    string  event name
    * @param    mixed   event data
    */
    public function setLastEvent($name, $data = null)
    {
        $this->lastEvent = array(
            'name' => $name,
            'data' => $data
        );
        $this->notify();
    }

   /**
    * Returns the last event
    *
    * Observers should use this method to access the last change in request.
    * The following event names are possible:
    * <ul>
    *   <li>'connect'                 - after connection to remote server,
    *                                   data is the destination (string)</li>
    *   <li>'disconnect'              - after disconnection from server</li>
    *   <li>'sentHeaders'             - after sending the request headers,
    *                                   data is the headers sent (string)</li>
    *   <li>'sentBodyPart'            - after sending a part of the request body, 
    *                                   data is the length of that part (int)</li>
    *   <li>'receivedHeaders'         - after receiving the response headers,
    *                                   data is HTTP_Request2_Response object</li>
    *   <li>'receivedBodyPart'        - after receiving a part of the response
    *                                   body, data is that part (string)</li>
    *   <li>'receivedEncodedBodyPart' - as 'receivedBodyPart', but data is still
    *                                   encoded by Content-Encoding</li>
    *   <li>'receivedBody'            - after receiving the complete response
    *                                   body, data is HTTP_Request2_Response object</li>
    * </ul>
    * Different adapters may not send all the event types. Mock adapter does
    * not send any events to the observers.
    *
    * @return   array   The array has two keys: 'name' and 'data'
    */
    public function getLastEvent()
    {
        return $this->lastEvent;
    }

   /**
    * Sets the adapter used to actually perform the request
    *
    * You can pass either an instance of a class implementing HTTP_Request2_Adapter
    * or a class name. The method will only try to include a file if the class
    * name starts with HTTP_Request2_Adapter_, it will also try to prepend this
    * prefix to the class name if it doesn't contain any underscores, so that
    * <code>
    * $request->setAdapter('curl');
    * </code>
    * will work.
    *
    * @param    string|HTTP_Request2_Adapter
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_Exception
    */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            if (!class_exists($adapter, false)) {
                if (false === strpos($adapter, '_')) {
                    $adapter = 'Request2_Adapter_' . ucfirst($adapter);
                }
                if (preg_match('/^Request2_Adapter_([a-zA-Z0-9]+)$/', $adapter)) {
                    include_once str_replace('_', DIRECTORY_SEPARATOR, $adapter) . '.php';
                }
                if (!class_exists($adapter, false)) {
                    throw new HTTP_Request2_Exception("Class {$adapter} not found");
                }
            }
            $adapter = new $adapter;
        }
        if (!$adapter instanceof HTTP_Request2_Adapter) {
            throw new HTTP_Request2_Exception('Parameter is not a HTTP request adapter');
        }
        $this->adapter = $adapter;

        return $this;
    }

   /**
    * Sends the request and returns the response
    *
    * @throws   HTTP_Request2_Exception
    * @return   HTTP_Request2_Response
    */
    public function send()
    {
        // Sanity check for URL
        if (!$this->url instanceof Net_URL2) {
            throw new HTTP_Request2_Exception('No URL given');
        } elseif (!$this->url->isAbsolute()) {
            throw new HTTP_Request2_Exception('Absolute URL required');
        } elseif (!in_array(strtolower($this->url->getScheme()), array('https', 'http'))) {
            throw new HTTP_Request2_Exception('Not a HTTP URL');
        }
        if (empty($this->adapter)) {
            $this->setAdapter($this->getConfig('adapter'));
        }
        // magic_quotes_runtime may break file uploads and chunked response
        // processing; see bug #4543
        if ($magicQuotes = ini_get('magic_quotes_runtime')) {
            ini_set('magic_quotes_runtime', false);
        }
        // force using single byte encoding if mbstring extension overloads
        // strlen() and substr(); see bug #1781, bug #10605
        if (extension_loaded('mbstring') && (2 & ini_get('mbstring.func_overload'))) {
            $oldEncoding = mb_internal_encoding();
            mb_internal_encoding('iso-8859-1');
        }

        try {
            $response = $this->adapter->sendRequest($this);
        } catch (Exception $e) {
        }
        // cleanup in either case (poor man's "finally" clause)
        if ($magicQuotes) {
            ini_set('magic_quotes_runtime', true);
        }
        if (!empty($oldEncoding)) {
            mb_internal_encoding($oldEncoding);
        }
        // rethrow the exception
        if (!empty($e)) {
            throw $e;
        }
        return $response;
    }

   /**
    * Tries to detect MIME type of a file
    *
    * The method will try to use fileinfo extension if it is available,
    * deprecated mime_content_type() function in the other case. If neither
    * works, default 'application/octet-stream' MIME type is returned
    *
    * @param    string  filename
    * @return   string  file MIME type
    */
    protected static function detectMimeType($filename)
    {
        // finfo extension from PECL available 
        if (function_exists('finfo_open')) {
            if (!isset(self::$_fileinfoDb)) {
                self::$_fileinfoDb = @finfo_open(FILEINFO_MIME);
            }
            if (self::$_fileinfoDb) { 
                $info = finfo_file(self::$_fileinfoDb, $filename);
            }
        }
        // (deprecated) mime_content_type function available
        if (empty($info) && function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }
        return empty($info)? 'application/octet-stream': $info;
    }
    
    
    
    function addData($data, $value=NULL)
       {
         $this->addPostParameter($data, $value);
       }
}


class HTTP_Request2_Response
{
   /**
    * HTTP protocol version (e.g. 1.0, 1.1)
    * @var  string
    */
    protected $version;

   /**
    * Status code
    * @var  integer
    * @link http://tools.ietf.org/html/rfc2616#section-6.1.1
    */
    protected $code;

   /**
    * Reason phrase
    * @var  string
    * @link http://tools.ietf.org/html/rfc2616#section-6.1.1
    */
    protected $reasonPhrase;

   /**
    * Associative array of response headers
    * @var  array
    */
    protected $headers = array();

   /**
    * Cookies set in the response
    * @var  array
    */
    protected $cookies = array();

   /**
    * Name of last header processed by parseHederLine()
    *
    * Used to handle the headers that span multiple lines
    *
    * @var  string
    */
    protected $lastHeader = null;

   /**
    * Response body
    * @var  string
    */
    protected $body = '';

   /**
    * Whether the body is still encoded by Content-Encoding
    *
    * cURL provides the decoded body to the callback; if we are reading from
    * socket the body is still gzipped / deflated
    *
    * @var  bool
    */
    protected $bodyEncoded;

   /**
    * Associative array of HTTP status code / reason phrase.
    *
    * @var  array
    * @link http://tools.ietf.org/html/rfc2616#section-10
    */
    protected static $phrases = array(

        // 1xx: Informational - Request received, continuing process
        100 => 'Continue',
        101 => 'Switching Protocols',

        // 2xx: Success - The action was successfully received, understood and
        // accepted
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // 3xx: Redirection - Further action must be taken in order to complete
        // the request
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // 4xx: Client Error - The request contains bad syntax or cannot be 
        // fulfilled
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // 5xx: Server Error - The server failed to fulfill an apparently
        // valid request
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded',

    );

   /**
    * Constructor, parses the response status line
    *
    * @param    string  Response status line (e.g. "HTTP/1.1 200 OK")
    * @param    bool    Whether body is still encoded by Content-Encoding
    * @throws   HTTP_Request2_Exception if status line is invalid according to spec
    */
    public function __construct($statusLine, $bodyEncoded = true)
    {
        if (!preg_match('!^HTTP/(\d\.\d) (\d{3})(?: (.+))?!', $statusLine, $m)) {
            throw new HTTP_Request2_Exception("Malformed response: {$statusLine}");
        }
        $this->version = $m[1];
        $this->code    = intval($m[2]);
        if (!empty($m[3])) {
            $this->reasonPhrase = trim($m[3]);
        } elseif (!empty(self::$phrases[$this->code])) {
            $this->reasonPhrase = self::$phrases[$this->code];
        }
        $this->bodyEncoded = (bool)$bodyEncoded;
    }
    
    public function parse($format="xml")
    {
      if(!is_null($format))
      {
	      $method = "parse" . ucfirst($format);
	      if(method_exists($this, $method))
	      {
	        return call_user_method($method, $this);
	      }
      }
      return $this->getBody();
    }
    
    public function parseXml()
    {
      libxml_use_internal_errors(true);
      $response = $this->getBody();
      
      try
      {
        $xml = simplexml_load_string($response);
        $xml = $xml ? $this->simplexml2array($xml) : $xml;
      }
      catch(Exception $e )
      {
        $xml = false;
      }
      return $xml;
    }
    /**
     * Convert SimpleXMLElement object to array
     * Copyright Daniel FAIVRE 2005 - www.geomaticien.com
     * Copyleft GPL license
     */
    
    public function simplexml2array($xml, $level=0) {
        $newlvl = $level+1;
       if (get_class($xml) == 'SimpleXMLElement') {
           $attributes = $xml->attributes();
           foreach($attributes as $k=>$v) {
               if ($v) $a[$k] = (string) $v;
           }
           $x = $xml;
           $xml = get_object_vars($xml);
       }
       if (is_array($xml)) {
           $r = array();
           if (count($xml) == 0) return (string) $x; // for CDATA
           $i = 0;
           
           foreach($xml as $key=>$value) {
              if($key == 'item')
              {
                if(is_array($value))
                {
                  foreach($value as $k=>$v)
                  {
                    $r[$k] = $this->simplexml2array($v, $newlvl);
                  }
                }
                else
                {
                  $r[] = $this->simplexml2array($value, $newlvl);
                }
              }
              else
              {
                $r[$key] = $this->simplexml2array($value, $newlvl);
              }
           }
           if (isset($a)) $r['@'] = $a;    // Attributes
           return $r;
       }
       return (string) $xml;
    }
    
   /**
    * Parses the line from HTTP response filling $headers array
    *
    * The method should be called after reading the line from socket or receiving 
    * it into cURL callback. Passing an empty string here indicates the end of
    * response headers and triggers additional processing, so be sure to pass an
    * empty string in the end.
    *
    * @param    string  Line from HTTP response
    */
    public function parseHeaderLine($headerLine)
    {
        $headerLine = trim($headerLine, "\r\n");

        // empty string signals the end of headers, process the received ones
        if ('' == $headerLine) {
            if (!empty($this->headers['set-cookie'])) {
                $cookies = is_array($this->headers['set-cookie'])?
                           $this->headers['set-cookie']:
                           array($this->headers['set-cookie']);
                foreach ($cookies as $cookieString) {
                    $this->parseCookie($cookieString);
                }
                unset($this->headers['set-cookie']);
            }
            foreach (array_keys($this->headers) as $k) {
                if (is_array($this->headers[$k])) {
                    $this->headers[$k] = implode(', ', $this->headers[$k]);
                }
            }

        // string of the form header-name: header value
        } elseif (preg_match('!^([^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+):(.+)$!', $headerLine, $m)) {
            $name  = strtolower($m[1]);
            $value = trim($m[2]);
            if (empty($this->headers[$name])) {
                $this->headers[$name] = $value;
            } else {
                if (!is_array($this->headers[$name])) {
                    $this->headers[$name] = array($this->headers[$name]);
                }
                $this->headers[$name][] = $value;
            }
            $this->lastHeader = $name;

        // string 
        } elseif (preg_match('!^\s+(.+)$!', $headerLine, $m) && $this->lastHeader) {
            if (!is_array($this->headers[$this->lastHeader])) {
                $this->headers[$this->lastHeader] .= ' ' . trim($m[1]);
            } else {
                $key = count($this->headers[$this->lastHeader]) - 1;
                $this->headers[$this->lastHeader][$key] .= ' ' . trim($m[1]);
            }
        }
    } 

   /**
    * Parses a Set-Cookie header to fill $cookies array
    *
    * @param    string    value of Set-Cookie header
    * @link     http://cgi.netscape.com/newsref/std/cookie_spec.html
    */
    protected function parseCookie($cookieString)
    {
        $cookie = array(
            'expires' => null,
            'domain'  => null,
            'path'    => null,
            'secure'  => false
        );

        // Only a name=value pair
        if (!strpos($cookieString, ';')) {
            $pos = strpos($cookieString, '=');
            $cookie['name']  = trim(substr($cookieString, 0, $pos));
            $cookie['value'] = trim(substr($cookieString, $pos + 1));

        // Some optional parameters are supplied
        } else {
            $elements = explode(';', $cookieString);
            $pos = strpos($elements[0], '=');
            $cookie['name']  = trim(substr($elements[0], 0, $pos));
            $cookie['value'] = trim(substr($elements[0], $pos + 1));

            for ($i = 1; $i < count($elements); $i++) {
                if (false === strpos($elements[$i], '=')) {
                    $elName  = trim($elements[$i]);
                    $elValue = null;
                } else {
                    list ($elName, $elValue) = array_map('trim', explode('=', $elements[$i]));
                }
                $elName = strtolower($elName);
                if ('secure' == $elName) {
                    $cookie['secure'] = true;
                } elseif ('expires' == $elName) {
                    $cookie['expires'] = str_replace('"', '', $elValue);
                } elseif ('path' == $elName || 'domain' == $elName) {
                    $cookie[$elName] = urldecode($elValue);
                } else {
                    $cookie[$elName] = $elValue;
                }
            }
        }
        $this->cookies[] = $cookie;
    }

   /**
    * Appends a string to the response body
    * @param    string
    */
    public function appendBody($bodyChunk)
    {
        $this->body .= $bodyChunk;
    }

   /**
    * Returns the status code
    * @return   integer 
    */
    public function getStatus()
    {
        return $this->code;
    }

   /**
    * Returns the reason phrase
    * @return   string
    */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

   /**
    * Returns either the named header or all response headers
    *
    * @param    string          Name of header to return
    * @return   string|array    Value of $headerName header (null if header is
    *                           not present), array of all response headers if
    *                           $headerName is null
    */
    public function getHeader($headerName = null)
    {
        if (null === $headerName) {
            return $this->headers;
        } else {
            $headerName = strtolower($headerName);
            return isset($this->headers[$headerName])? $this->headers[$headerName]: null;
        }
    }

   /**
    * Returns cookies set in response
    *
    * @return   array
    */
    public function getCookies()
    {
        return $this->cookies;
    }

   /**
    * Returns the body of the response
    *
    * @return   string
    * @throws   HTTP_Request2_Exception if body cannot be decoded
    */
    public function getBody()
    {
        if (!$this->bodyEncoded ||
            !in_array(strtolower($this->getHeader('content-encoding')), array('gzip', 'deflate'))
        ) {
            return $this->body;

        } else {
            if (extension_loaded('mbstring') && (2 & ini_get('mbstring.func_overload'))) {
                $oldEncoding = mb_internal_encoding();
                mb_internal_encoding('iso-8859-1');
            }

            try {
                switch (strtolower($this->getHeader('content-encoding'))) {
                    case 'gzip':
                        $decoded = self::decodeGzip($this->body);
                        break;
                    case 'deflate':
                        $decoded = self::decodeDeflate($this->body);
                }
            } catch (Exception $e) {
            }

            if (!empty($oldEncoding)) {
                mb_internal_encoding($oldEncoding);
            }
            if (!empty($e)) {
                throw $e;
            }
            return $decoded;
        }
    }

   /**
    * Get the HTTP version of the response
    *
    * @return   string
    */ 
    public function getVersion()
    {
        return $this->version;
    }

   /**
    * Decodes the message-body encoded by gzip
    *
    * The real decoding work is done by gzinflate() built-in function, this
    * method only parses the header and checks data for compliance with
    * RFC 1952
    *
    * @param    string  gzip-encoded data
    * @return   string  decoded data
    * @throws   HTTP_Request2_Exception
    * @link     http://tools.ietf.org/html/rfc1952
    */
    public static function decodeGzip($data)
    {
        $length = strlen($data);
        // If it doesn't look like gzip-encoded data, don't bother
        if (18 > $length || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            return $data;
        }
        if (!function_exists('gzinflate')) {
            throw new HTTP_Request2_Exception('Unable to decode body: gzip extension not available');
        }
        $method = ord(substr($data, 2, 1));
        if (8 != $method) {
            throw new HTTP_Request2_Exception('Error parsing gzip header: unknown compression method');
        }
        $flags = ord(substr($data, 3, 1));
        if ($flags & 224) {
            throw new HTTP_Request2_Exception('Error parsing gzip header: reserved bits are set');
        }

        // header is 10 bytes minimum. may be longer, though.
        $headerLength = 10;
        // extra fields, need to skip 'em
        if ($flags & 4) {
            if ($length - $headerLength - 2 < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $extraLength = unpack('v', substr($data, 10, 2));
            if ($length - $headerLength - 2 - $extraLength[1] < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $headerLength += $extraLength[1] + 2;
        }
        // file name, need to skip that
        if ($flags & 8) {
            if ($length - $headerLength - 1 < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $filenameLength = strpos(substr($data, $headerLength), chr(0));
            if (false === $filenameLength || $length - $headerLength - $filenameLength - 1 < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $headerLength += $filenameLength + 1;
        }
        // comment, need to skip that also
        if ($flags & 16) {
            if ($length - $headerLength - 1 < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $commentLength = strpos(substr($data, $headerLength), chr(0));
            if (false === $commentLength || $length - $headerLength - $commentLength - 1 < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $headerLength += $commentLength + 1;
        }
        // have a CRC for header. let's check
        if ($flags & 2) {
            if ($length - $headerLength - 2 < 8) {
                throw new HTTP_Request2_Exception('Error parsing gzip header: data too short');
            }
            $crcReal   = 0xffff & crc32(substr($data, 0, $headerLength));
            $crcStored = unpack('v', substr($data, $headerLength, 2));
            if ($crcReal != $crcStored[1]) {
                throw new HTTP_Request2_Exception('Header CRC check failed');
            }
            $headerLength += 2;
        }
        // unpacked data CRC and size at the end of encoded data
        $tmp = unpack('V2', substr($data, -8));
        $dataCrc  = $tmp[1];
        $dataSize = $tmp[2];

        // finally, call the gzinflate() function
        // don't pass $dataSize to gzinflate, see bugs #13135, #14370
        $unpacked = gzinflate(substr($data, $headerLength, -8));
        if (false === $unpacked) {
            throw new HTTP_Request2_Exception('gzinflate() call failed');
        } elseif ($dataSize != strlen($unpacked)) {
            throw new HTTP_Request2_Exception('Data size check failed');
        } elseif ((0xffffffff & $dataCrc) != (0xffffffff & crc32($unpacked))) {
            throw new HTTP_Request2_Exception('Data CRC check failed');
        }
        return $unpacked;
    }

   /**
    * Decodes the message-body encoded by deflate
    *
    * @param    string  deflate-encoded data
    * @return   string  decoded data
    * @throws   HTTP_Request2_Exception
    */
    public static function decodeDeflate($data)
    {
        if (!function_exists('gzuncompress')) {
            throw new HTTP_Request2_Exception('Unable to decode body: gzip extension not available');
        }
        // RFC 2616 defines 'deflate' encoding as zlib format from RFC 1950,
        // while many applications send raw deflate stream from RFC 1951.
        // We should check for presence of zlib header and use gzuncompress() or
        // gzinflate() as needed. See bug #15305
        $header = unpack('n', substr($data, 0, 2));
        return (0 == $header[1] % 31)? gzuncompress($data): gzinflate($data);
    }
}

class HTTP_Request2_Observer_Log implements SplObserver
{
    // properties {{{

    /**
     * The log target, it can be a a resource or a PEAR Log instance.
     *
     * @var resource|Log $target
     */
    protected $target = null;

    /**
     * The events to log.
     *
     * @var array $events
     */
    public $events = array(
        'connect',
        'sentHeaders',
        'sentBodyPart',
        'receivedHeaders',
        'receivedBody',
        'disconnect',
    );

    // }}}
    // __construct() {{{

    /**
     * Constructor.
     *
     * @param mixed $target Can be a file path (default: php://output), a resource,
     *                      or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     */
    public function __construct($target = 'php://output', array $events = array())
    {
        if (!empty($events)) {
            $this->events = $events;
        }
        if (is_resource($target) || $target instanceof Log) {
            $this->target = $target;
        } elseif (false === ($this->target = @fopen($target, 'w'))) {
            throw new HTTP_Request2_Exception("Unable to open '{$target}'");
        }
    }

    // }}}
    // update() {{{

    /**
     * Called when the request notify us of an event.
     *
     * @param HTTP_Request2 $subject The HTTP_Request2 instance
     *
     * @return void
     */
    public function update(SplSubject $subject)
    {
        $event = $subject->getLastEvent();
        if (!in_array($event['name'], $this->events)) {
            return;
        }

        switch ($event['name']) {
        case 'connect':
            $this->log('* Connected to ' . $event['data']);
            break;
        case 'sentHeaders':
            $headers = explode("\r\n", $event['data']);
            array_pop($headers);
            foreach ($headers as $header) {
                $this->log('> ' . $header);
            }
            break;
        case 'sentBodyPart':
            $this->log('> ' . $event['data']);
            break;
        case 'receivedHeaders':
            $this->log(sprintf('< HTTP/%s %s %s',
                $event['data']->getVersion(),
                $event['data']->getStatus(),
                $event['data']->getReasonPhrase()));
            $headers = $event['data']->getHeader();
            foreach ($headers as $key => $val) {
                $this->log('< ' . $key . ': ' . $val);
            }
            $this->log('< ');
            break;
        case 'receivedBody':
            $this->log($event['data']->getBody());
            break;
        case 'disconnect':
            $this->log('* Disconnected');
            break;
        }
    }
    
    // }}}
    // log() {{{

    /**
     * Log the given message to the configured target.
     *
     * @param string $message Message to display
     *
     * @return void
     */
    protected function log($message)
    {
        if ($this->target instanceof Log) {
            $this->target->debug($message);
        } elseif (is_resource($this->target)) {
            fwrite($this->target, $message . "\r\n");
        }
    }

    // }}}
}


class HTTP_Request2_MultipartBody
{
   /**
    * MIME boundary
    * @var  string
    */
    private $_boundary;

   /**
    * Form parameters added via {@link HTTP_Request2::addPostParameter()}
    * @var  array
    */
    private $_params = array();

   /**
    * File uploads added via {@link HTTP_Request2::addUpload()}
    * @var  array
    */
    private $_uploads = array();

   /**
    * Header for parts with parameters
    * @var  string
    */
    private $_headerParam = "--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n";

   /**
    * Header for parts with uploads
    * @var  string
    */
    private $_headerUpload = "--%s\r\nContent-Disposition: form-data; name=\"%s\"; filename=\"%s\"\r\nContent-Type: %s\r\n\r\n";

   /**
    * Current position in parameter and upload arrays
    *
    * First number is index of "current" part, second number is position within
    * "current" part
    *
    * @var  array
    */
    private $_pos = array(0, 0);


   /**
    * Constructor. Sets the arrays with POST data.
    *
    * @param    array   values of form fields set via {@link HTTP_Request2::addPostParameter()}
    * @param    array   file uploads set via {@link HTTP_Request2::addUpload()}
    * @param    bool    whether to append brackets to array variable names
    */
    public function __construct(array $params, array $uploads, $useBrackets = true)
    {
        $this->_params = self::_flattenArray('', $params, $useBrackets);
        foreach ($uploads as $fieldName => $f) {
            if (!is_array($f['fp'])) {
                $this->_uploads[] = $f + array('name' => $fieldName);
            } else {
                for ($i = 0; $i < count($f['fp']); $i++) {
                    $upload = array(
                        'name' => ($useBrackets? $fieldName . '[' . $i . ']': $fieldName)
                    );
                    foreach (array('fp', 'filename', 'size', 'type') as $key) {
                        $upload[$key] = $f[$key][$i];
                    }
                    $this->_uploads[] = $upload;
                }
            }
        }
    }

   /**
    * Returns the length of the body to use in Content-Length header
    *
    * @return   integer
    */
    public function getLength()
    {
        $boundaryLength     = strlen($this->getBoundary());
        $headerParamLength  = strlen($this->_headerParam) - 4 + $boundaryLength;
        $headerUploadLength = strlen($this->_headerUpload) - 8 + $boundaryLength;
        $length             = $boundaryLength + 6;
        foreach ($this->_params as $p) {
            $length += $headerParamLength + strlen($p[0]) + strlen($p[1]) + 2;
        }
        foreach ($this->_uploads as $u) {
            $length += $headerUploadLength + strlen($u['name']) + strlen($u['type']) +
                       strlen($u['filename']) + $u['size'] + 2;
        }
        return $length;
    }

   /**
    * Returns the boundary to use in Content-Type header
    *
    * @return   string
    */
    public function getBoundary()
    {
        if (empty($this->_boundary)) {
            $this->_boundary = '--' . md5('PEAR-HTTP_Request2-' . microtime());
        }
        return $this->_boundary;
    }

   /**
    * Returns next chunk of request body
    *
    * @param    integer Amount of bytes to read
    * @return   string  Up to $length bytes of data, empty string if at end
    */
    public function read($length)
    {
        $ret         = '';
        $boundary    = $this->getBoundary();
        $paramCount  = count($this->_params);
        $uploadCount = count($this->_uploads);
        while ($length > 0 && $this->_pos[0] <= $paramCount + $uploadCount) {
            $oldLength = $length;
            if ($this->_pos[0] < $paramCount) {
                $param = sprintf($this->_headerParam, $boundary, 
                                 $this->_params[$this->_pos[0]][0]) .
                         $this->_params[$this->_pos[0]][1] . "\r\n";
                $ret    .= substr($param, $this->_pos[1], $length);
                $length -= min(strlen($param) - $this->_pos[1], $length);

            } elseif ($this->_pos[0] < $paramCount + $uploadCount) {
                $pos    = $this->_pos[0] - $paramCount;
                $header = sprintf($this->_headerUpload, $boundary,
                                  $this->_uploads[$pos]['name'],
                                  $this->_uploads[$pos]['filename'],
                                  $this->_uploads[$pos]['type']);
                if ($this->_pos[1] < strlen($header)) {
                    $ret    .= substr($header, $this->_pos[1], $length);
                    $length -= min(strlen($header) - $this->_pos[1], $length);
                }
                $filePos  = max(0, $this->_pos[1] - strlen($header));
                if ($length > 0 && $filePos < $this->_uploads[$pos]['size']) {
                    $ret     .= fread($this->_uploads[$pos]['fp'], $length);
                    $length  -= min($length, $this->_uploads[$pos]['size'] - $filePos);
                }
                if ($length > 0) {
                    $start   = $this->_pos[1] + ($oldLength - $length) -
                               strlen($header) - $this->_uploads[$pos]['size'];
                    $ret    .= substr("\r\n", $start, $length);
                    $length -= min(2 - $start, $length);
                }

            } else {
                $closing  = '--' . $boundary . "--\r\n";
                $ret     .= substr($closing, $this->_pos[1], $length);
                $length  -= min(strlen($closing) - $this->_pos[1], $length);
            }
            if ($length > 0) {
                $this->_pos     = array($this->_pos[0] + 1, 0);
            } else {
                $this->_pos[1] += $oldLength;
            }
        }
        return $ret;
    }

   /**
    * Sets the current position to the start of the body
    *
    * This allows reusing the same body in another request
    */
    public function rewind()
    {
        $this->_pos = array(0, 0);
        foreach ($this->_uploads as $u) {
            rewind($u['fp']);
        }
    }

   /**
    * Returns the body as string
    *
    * Note that it reads all file uploads into memory so it is a good idea not
    * to use this method with large file uploads and rely on read() instead.
    *
    * @return   string
    */
    public function __toString()
    {
        $this->rewind();
        return $this->read($this->getLength());
    }


   /**
    * Helper function to change the (probably multidimensional) associative array
    * into the simple one.
    *
    * @param    string  name for item
    * @param    mixed   item's values
    * @param    bool    whether to append [] to array variables' names
    * @return   array   array with the following items: array('item name', 'item value');
    */
    private static function _flattenArray($name, $values, $useBrackets)
    {
        if (!is_array($values)) {
            return array(array($name, $values));
        } else {
            $ret = array();
            foreach ($values as $k => $v) {
                if (empty($name)) {
                    $newName = $k;
                } elseif ($useBrackets) {
                    $newName = $name . '[' . $k . ']';
                } else {
                    $newName = $name;
                }
                $ret = array_merge($ret, self::_flattenArray($newName, $v, $useBrackets));
            }
            return $ret;
        }
    }
}

class HTTP_Request2_Exception extends Exception
{
}

abstract class HTTP_Request2_Adapter
{
   /**
    * A list of methods that MUST NOT have a request body, per RFC 2616
    * @var  array
    */
    protected static $bodyDisallowed = array('TRACE');

   /**
    * Methods having defined semantics for request body
    *
    * Content-Length header (indicating that the body follows, section 4.3 of
    * RFC 2616) will be sent for these methods even if no body was added
    *
    * @var  array
    * @link http://pear.php.net/bugs/bug.php?id=12900
    * @link http://pear.php.net/bugs/bug.php?id=14740
    */
    protected static $bodyRequired = array('POST', 'PUT');

   /**
    * Request being sent
    * @var  HTTP_Request2
    */
    protected $request;

   /**
    * Request body
    * @var  string|resource|HTTP_Request2_MultipartBody
    * @see  HTTP_Request2::getBody()
    */
    protected $requestBody;

   /**
    * Length of the request body
    * @var  integer
    */
    protected $contentLength;

   /**
    * Sends request to the remote server and returns its response
    *
    * @param    HTTP_Request2
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    abstract public function sendRequest(HTTP_Request2 $request);

   /**
    * Calculates length of the request body, adds proper headers
    *
    * @param    array   associative array of request headers, this method will 
    *                   add proper 'Content-Length' and 'Content-Type' headers 
    *                   to this array (or remove them if not needed)
    */
    protected function calculateRequestLength(&$headers)
    {
        $this->requestBody = $this->request->getBody();

        if (is_string($this->requestBody)) {
            $this->contentLength = strlen($this->requestBody);
        } elseif (is_resource($this->requestBody)) {
            $stat = fstat($this->requestBody);
            $this->contentLength = $stat['size'];
            rewind($this->requestBody);
        } else {
            $this->contentLength = $this->requestBody->getLength();
            $headers['content-type'] = 'multipart/form-data; boundary=' .
                                       $this->requestBody->getBoundary();
            $this->requestBody->rewind();
        }

        if (in_array($this->request->getMethod(), self::$bodyDisallowed) ||
            0 == $this->contentLength
        ) {
            unset($headers['content-type']);
            // No body: send a Content-Length header nonetheless (request #12900),
            // but do that only for methods that require a body (bug #14740)
            if (in_array($this->request->getMethod(), self::$bodyRequired)) {
                $headers['content-length'] = 0;
            } else {
                unset($headers['content-length']);
            }
        } else {
            if (empty($headers['content-type'])) {
                $headers['content-type'] = 'application/x-www-form-urlencoded';
            }
            $headers['content-length'] = $this->contentLength;
        }
    }
}

class Request2_Adapter_Socket extends HTTP_Request2_Adapter
{
   /**
    * Regular expression for 'token' rule from RFC 2616
    */ 
    const REGEXP_TOKEN = '[^\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]+';

   /**
    * Regular expression for 'quoted-string' rule from RFC 2616
    */
    const REGEXP_QUOTED_STRING = '"(?:\\\\.|[^\\\\"])*"';

   /**
    * Connected sockets, needed for Keep-Alive support
    * @var  array
    * @see  connect()
    */
    protected static $sockets = array();

   /**
    * Data for digest authentication scheme
    *
    * The keys for the array are URL prefixes. 
    *
    * The values are associative arrays with data (realm, nonce, nonce-count, 
    * opaque...) needed for digest authentication. Stored here to prevent making 
    * duplicate requests to digest-protected resources after we have already 
    * received the challenge.
    *
    * @var  array
    */
    protected static $challenges = array();

   /**
    * Connected socket
    * @var  resource
    * @see  connect()
    */
    protected $socket;

   /**
    * Challenge used for server digest authentication
    * @var  array
    */
    protected $serverChallenge;

   /**
    * Challenge used for proxy digest authentication
    * @var  array
    */
    protected $proxyChallenge;

   /**
    * Global timeout, exception will be raised if request continues past this time
    * @var  integer
    */
    protected $timeout = null;

   /**
    * Remaining length of the current chunk, when reading chunked response
    * @var  integer
    * @see  readChunked()
    */ 
    protected $chunkLength = 0;

   /**
    * Sends request to the remote server and returns its response
    *
    * @param    HTTP_Request2
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    public function sendRequest(HTTP_Request2 $request)
    {
        $this->request = $request;
        $keepAlive     = $this->connect();
        $headers       = $this->prepareHeaders();

        // Use global request timeout if given, see feature requests #5735, #8964 
        if ($timeout = $request->getConfig('timeout')) {
            $this->timeout = time() + $timeout;
        } else {
            $this->timeout = null;
        }

        try {
            if (false === @fwrite($this->socket, $headers, strlen($headers))) {
                throw new HTTP_Request2_Exception('Error writing request');
            }
            // provide request headers to the observer, see request #7633
            $this->request->setLastEvent('sentHeaders', $headers);
            $this->writeBody();

            if ($this->timeout && time() > $this->timeout) {
                throw new HTTP_Request2_Exception(
                    'Request timed out after ' . 
                    $request->getConfig('timeout') . ' second(s)'
                );
            }

            $response = $this->readResponse();

            if (!$this->canKeepAlive($keepAlive, $response)) {
                $this->disconnect();
            }

            if ($this->shouldUseProxyDigestAuth($response)) {
                return $this->sendRequest($request);
            }
            if ($this->shouldUseServerDigestAuth($response)) {
                return $this->sendRequest($request);
            }
            if ($authInfo = $response->getHeader('authentication-info')) {
                $this->updateChallenge($this->serverChallenge, $authInfo);
            }
            if ($proxyInfo = $response->getHeader('proxy-authentication-info')) {
                $this->updateChallenge($this->proxyChallenge, $proxyInfo);
            }

        } catch (Exception $e) {
            $this->disconnect();
            throw $e;
        }

        return $response;
    }

   /**
    * Connects to the remote server
    *
    * @return   bool    whether the connection can be persistent
    * @throws   HTTP_Request2_Exception
    */
    protected function connect()
    {
        $secure  = 0 == strcasecmp($this->request->getUrl()->getScheme(), 'https');
        $tunnel  = HTTP_Request2::METHOD_CONNECT == $this->request->getMethod();
        $headers = $this->request->getHeaders();
        $reqHost = $this->request->getUrl()->getHost();
        if (!($reqPort = $this->request->getUrl()->getPort())) {
            $reqPort = $secure? 443: 80;
        }

        if ($host = $this->request->getConfig('proxy_host')) {
            if (!($port = $this->request->getConfig('proxy_port'))) {
                throw new HTTP_Request2_Exception('Proxy port not provided');
            }
            $proxy = true;
        } else {
            $host  = $reqHost;
            $port  = $reqPort;
            $proxy = false;
        }

        if ($tunnel && !$proxy) {
            throw new HTTP_Request2_Exception(
                "Trying to perform CONNECT request without proxy"
            );
        }
        if ($secure && !in_array('ssl', stream_get_transports())) {
            throw new HTTP_Request2_Exception(
                'Need OpenSSL support for https:// requests'
            );
        }

        // RFC 2068, section 19.7.1: A client MUST NOT send the Keep-Alive
        // connection token to a proxy server...
        if ($proxy && !$secure && 
            !empty($headers['connection']) && 'Keep-Alive' == $headers['connection']
        ) {
            $this->request->setHeader('connection');
        }

        $keepAlive = ('1.1' == $this->request->getConfig('protocol_version') && 
                      empty($headers['connection'])) ||
                     (!empty($headers['connection']) &&
                      'Keep-Alive' == $headers['connection']);
        $host = ((!$secure || $proxy)? 'tcp://': 'ssl://') . $host;

        $options = array();
        if ($secure || $tunnel) {
            foreach ($this->request->getConfig() as $name => $value) {
                if ('ssl_' == substr($name, 0, 4) && null !== $value) {
                    if ('ssl_verify_host' == $name) {
                        if ($value) {
                            $options['CN_match'] = $reqHost;
                        }
                    } else {
                        $options[substr($name, 4)] = $value;
                    }
                }
            }
            ksort($options);
        }

        // Changing SSL context options after connection is established does *not*
        // work, we need a new connection if options change
        $remote    = $host . ':' . $port;
        $socketKey = $remote . (($secure && $proxy)? "->{$reqHost}:{$reqPort}": '') .
                     (empty($options)? '': ':' . serialize($options));
        unset($this->socket);

        // We use persistent connections and have a connected socket?
        // Ensure that the socket is still connected, see bug #16149
        if ($keepAlive && !empty(self::$sockets[$socketKey]) &&
            !feof(self::$sockets[$socketKey])
        ) {
            $this->socket =& self::$sockets[$socketKey];

        } elseif ($secure && $proxy && !$tunnel) {
            $this->establishTunnel();
            $this->request->setLastEvent(
                'connect', "ssl://{$reqHost}:{$reqPort} via {$host}:{$port}"
            );
            self::$sockets[$socketKey] =& $this->socket;

        } else {
            // Set SSL context options if doing HTTPS request or creating a tunnel
            $context = stream_context_create();
            foreach ($options as $name => $value) {
                if (!stream_context_set_option($context, 'ssl', $name, $value)) {
                    throw new HTTP_Request2_Exception(
                        "Error setting SSL context option '{$name}'"
                    );
                }
            }
            $this->socket = @stream_socket_client(
                $remote, $errno, $errstr,
                $this->request->getConfig('connect_timeout'),
                STREAM_CLIENT_CONNECT, $context
            );
            if (!$this->socket) {
                throw new HTTP_Request2_Exception(
                    "Unable to connect to {$remote}. Error #{$errno}: {$errstr}"
                );
            }
            $this->request->setLastEvent('connect', $remote);
            self::$sockets[$socketKey] =& $this->socket;
        }
        return $keepAlive;
    }

   /**
    * Establishes a tunnel to a secure remote server via HTTP CONNECT request
    *
    * This method will fail if 'ssl_verify_peer' is enabled. Probably because PHP
    * sees that we are connected to a proxy server (duh!) rather than the server
    * that presents its certificate.
    *
    * @link     http://tools.ietf.org/html/rfc2817#section-5.2
    * @throws   HTTP_Request2_Exception
    */
    protected function establishTunnel()
    {
        $donor   = new self;
        $connect = new HTTP_Request2(
            $this->request->getUrl(), HTTP_Request2::METHOD_CONNECT,
            array_merge($this->request->getConfig(),
                        array('adapter' => $donor))
        );
        $response = $connect->send();
        // Need any successful (2XX) response
        if (200 > $response->getStatus() || 300 <= $response->getStatus()) {
            throw new HTTP_Request2_Exception(
                'Failed to connect via HTTPS proxy. Proxy response: ' .
                $response->getStatus() . ' ' . $response->getReasonPhrase()
            );
        }
        $this->socket = $donor->socket;

        $modes = array(
            STREAM_CRYPTO_METHOD_TLS_CLIENT, 
            STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv23_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv2_CLIENT 
        );

        foreach ($modes as $mode) {
            if (stream_socket_enable_crypto($this->socket, true, $mode)) {
                return;
            }
        }
        throw new HTTP_Request2_Exception(
            'Failed to enable secure connection when connecting through proxy'
        );
    }

   /**
    * Checks whether current connection may be reused or should be closed
    *
    * @param    boolean                 whether connection could be persistent 
    *                                   in the first place
    * @param    HTTP_Request2_Response  response object to check
    * @return   boolean
    */
    protected function canKeepAlive($requestKeepAlive, HTTP_Request2_Response $response)
    {
        // Do not close socket on successful CONNECT request
        if (HTTP_Request2::METHOD_CONNECT == $this->request->getMethod() &&
            200 <= $response->getStatus() && 300 > $response->getStatus()
        ) {
            return true;
        }

        $lengthKnown = 'chunked' == strtolower($response->getHeader('transfer-encoding')) ||
                       null !== $response->getHeader('content-length');
        $persistent  = 'keep-alive' == strtolower($response->getHeader('connection')) ||
                       (null === $response->getHeader('connection') &&
                        '1.1' == $response->getVersion());
        return $requestKeepAlive && $lengthKnown && $persistent;
    }

   /**
    * Disconnects from the remote server
    */
    protected function disconnect()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
            $this->socket = null;
            $this->request->setLastEvent('disconnect');
        }
    }

   /**
    * Checks whether another request should be performed with server digest auth
    *
    * Several conditions should be satisfied for it to return true:
    *   - response status should be 401
    *   - auth credentials should be set in the request object
    *   - response should contain WWW-Authenticate header with digest challenge
    *   - there is either no challenge stored for this URL or new challenge
    *     contains stale=true parameter (in other case we probably just failed 
    *     due to invalid username / password)
    *
    * The method stores challenge values in $challenges static property
    *
    * @param    HTTP_Request2_Response  response to check
    * @return   boolean whether another request should be performed
    * @throws   HTTP_Request2_Exception in case of unsupported challenge parameters
    */
    protected function shouldUseServerDigestAuth(HTTP_Request2_Response $response)
    {
        // no sense repeating a request if we don't have credentials
        if (401 != $response->getStatus() || !$this->request->getAuth()) {
            return false;
        }
        if (!$challenge = $this->parseDigestChallenge($response->getHeader('www-authenticate'))) {
            return false;
        }

        $url    = $this->request->getUrl();
        $scheme = $url->getScheme();
        $host   = $scheme . '://' . $url->getHost();
        if ($port = $url->getPort()) {
            if ((0 == strcasecmp($scheme, 'http') && 80 != $port) ||
                (0 == strcasecmp($scheme, 'https') && 443 != $port)
            ) {
                $host .= ':' . $port;
            }
        }

        if (!empty($challenge['domain'])) {
            $prefixes = array();
            foreach (preg_split('/\\s+/', $challenge['domain']) as $prefix) {
                // don't bother with different servers
                if ('/' == substr($prefix, 0, 1)) {
                    $prefixes[] = $host . $prefix;
                }
            }
        }
        if (empty($prefixes)) {
            $prefixes = array($host . '/');
        }

        $ret = true;
        foreach ($prefixes as $prefix) {
            if (!empty(self::$challenges[$prefix]) &&
                (empty($challenge['stale']) || strcasecmp('true', $challenge['stale']))
            ) {
                // probably credentials are invalid
                $ret = false;
            }
            self::$challenges[$prefix] =& $challenge;
        }
        return $ret;
    }

   /**
    * Checks whether another request should be performed with proxy digest auth
    *
    * Several conditions should be satisfied for it to return true:
    *   - response status should be 407
    *   - proxy auth credentials should be set in the request object
    *   - response should contain Proxy-Authenticate header with digest challenge
    *   - there is either no challenge stored for this proxy or new challenge
    *     contains stale=true parameter (in other case we probably just failed 
    *     due to invalid username / password)
    *
    * The method stores challenge values in $challenges static property
    *
    * @param    HTTP_Request2_Response  response to check
    * @return   boolean whether another request should be performed
    * @throws   HTTP_Request2_Exception in case of unsupported challenge parameters
    */
    protected function shouldUseProxyDigestAuth(HTTP_Request2_Response $response)
    {
        if (407 != $response->getStatus() || !$this->request->getConfig('proxy_user')) {
            return false;
        }
        if (!($challenge = $this->parseDigestChallenge($response->getHeader('proxy-authenticate')))) {
            return false;
        }

        $key = 'proxy://' . $this->request->getConfig('proxy_host') .
               ':' . $this->request->getConfig('proxy_port');

        if (!empty(self::$challenges[$key]) &&
            (empty($challenge['stale']) || strcasecmp('true', $challenge['stale']))
        ) {
            $ret = false;
        } else {
            $ret = true;
        }
        self::$challenges[$key] = $challenge;
        return $ret;
    }

   /**
    * Extracts digest method challenge from (WWW|Proxy)-Authenticate header value
    *
    * There is a problem with implementation of RFC 2617: several of the parameters
    * here are defined as quoted-string and thus may contain backslash escaped
    * double quotes (RFC 2616, section 2.2). However, RFC 2617 defines unq(X) as
    * just value of quoted-string X without surrounding quotes, it doesn't speak
    * about removing backslash escaping.
    *
    * Now realm parameter is user-defined and human-readable, strange things
    * happen when it contains quotes:
    *   - Apache allows quotes in realm, but apparently uses realm value without
    *     backslashes for digest computation
    *   - Squid allows (manually escaped) quotes there, but it is impossible to
    *     authorize with either escaped or unescaped quotes used in digest,
    *     probably it can't parse the response (?)
    *   - Both IE and Firefox display realm value with backslashes in 
    *     the password popup and apparently use the same value for digest
    *
    * HTTP_Request2 follows IE and Firefox (and hopefully RFC 2617) in
    * quoted-string handling, unfortunately that means failure to authorize 
    * sometimes
    *
    * @param    string  value of WWW-Authenticate or Proxy-Authenticate header
    * @return   mixed   associative array with challenge parameters, false if
    *                   no challenge is present in header value
    * @throws   HTTP_Request2_Exception in case of unsupported challenge parameters
    */
    protected function parseDigestChallenge($headerValue)
    {
        $authParam   = '(' . self::REGEXP_TOKEN . ')\\s*=\\s*(' .
                       self::REGEXP_TOKEN . '|' . self::REGEXP_QUOTED_STRING . ')';
        $challenge   = "!(?<=^|\\s|,)Digest ({$authParam}\\s*(,\\s*|$))+!";
        if (!preg_match($challenge, $headerValue, $matches)) {
            return false;
        }

        preg_match_all('!' . $authParam . '!', $matches[0], $params);
        $paramsAry   = array();
        $knownParams = array('realm', 'domain', 'nonce', 'opaque', 'stale',
                             'algorithm', 'qop');
        for ($i = 0; $i < count($params[0]); $i++) {
            // section 3.2.1: Any unrecognized directive MUST be ignored.
            if (in_array($params[1][$i], $knownParams)) {
                if ('"' == substr($params[2][$i], 0, 1)) {
                    $paramsAry[$params[1][$i]] = substr($params[2][$i], 1, -1);
                } else {
                    $paramsAry[$params[1][$i]] = $params[2][$i];
                }
            }
        }
        // we only support qop=auth
        if (!empty($paramsAry['qop']) && 
            !in_array('auth', array_map('trim', explode(',', $paramsAry['qop'])))
        ) {
            throw new HTTP_Request2_Exception(
                "Only 'auth' qop is currently supported in digest authentication, " .
                "server requested '{$paramsAry['qop']}'"
            );
        }
        // we only support algorithm=MD5
        if (!empty($paramsAry['algorithm']) && 'MD5' != $paramsAry['algorithm']) {
            throw new HTTP_Request2_Exception(
                "Only 'MD5' algorithm is currently supported in digest authentication, " .
                "server requested '{$paramsAry['algorithm']}'"
            );
        }

        return $paramsAry; 
    }

   /**
    * Parses [Proxy-]Authentication-Info header value and updates challenge
    *
    * @param    array   challenge to update
    * @param    string  value of [Proxy-]Authentication-Info header
    * @todo     validate server rspauth response
    */ 
    protected function updateChallenge(&$challenge, $headerValue)
    {
        $authParam   = '!(' . self::REGEXP_TOKEN . ')\\s*=\\s*(' .
                       self::REGEXP_TOKEN . '|' . self::REGEXP_QUOTED_STRING . ')!';
        $paramsAry   = array();

        preg_match_all($authParam, $headerValue, $params);
        for ($i = 0; $i < count($params[0]); $i++) {
            if ('"' == substr($params[2][$i], 0, 1)) {
                $paramsAry[$params[1][$i]] = substr($params[2][$i], 1, -1);
            } else {
                $paramsAry[$params[1][$i]] = $params[2][$i];
            }
        }
        // for now, just update the nonce value
        if (!empty($paramsAry['nextnonce'])) {
            $challenge['nonce'] = $paramsAry['nextnonce'];
            $challenge['nc']    = 1;
        }
    }

   /**
    * Creates a value for [Proxy-]Authorization header when using digest authentication
    *
    * @param    string  user name
    * @param    string  password
    * @param    string  request URL
    * @param    array   digest challenge parameters
    * @return   string  value of [Proxy-]Authorization request header
    * @link     http://tools.ietf.org/html/rfc2617#section-3.2.2
    */ 
    protected function createDigestResponse($user, $password, $url, &$challenge)
    {
        if (false !== ($q = strpos($url, '?')) && 
            $this->request->getConfig('digest_compat_ie')
        ) {
            $url = substr($url, 0, $q);
        }

        $a1 = md5($user . ':' . $challenge['realm'] . ':' . $password);
        $a2 = md5($this->request->getMethod() . ':' . $url);

        if (empty($challenge['qop'])) {
            $digest = md5($a1 . ':' . $challenge['nonce'] . ':' . $a2);
        } else {
            $challenge['cnonce'] = 'Req2.' . rand();
            if (empty($challenge['nc'])) {
                $challenge['nc'] = 1;
            }
            $nc     = sprintf('%08x', $challenge['nc']++);
            $digest = md5($a1 . ':' . $challenge['nonce'] . ':' . $nc . ':' .
                          $challenge['cnonce'] . ':auth:' . $a2);
        }
        return 'Digest username="' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $user) . '", ' .
               'realm="' . $challenge['realm'] . '", ' .
               'nonce="' . $challenge['nonce'] . '", ' .
               'uri="' . $url . '", ' .
               'response="' . $digest . '"' .
               (!empty($challenge['opaque'])? 
                ', opaque="' . $challenge['opaque'] . '"':
                '') .
               (!empty($challenge['qop'])?
                ', qop="auth", nc=' . $nc . ', cnonce="' . $challenge['cnonce'] . '"':
                '');
    }

   /**
    * Adds 'Authorization' header (if needed) to request headers array
    *
    * @param    array   request headers
    * @param    string  request host (needed for digest authentication)
    * @param    string  request URL (needed for digest authentication)
    * @throws   HTTP_Request2_Exception
    */
    protected function addAuthorizationHeader(&$headers, $requestHost, $requestUrl)
    {
        if (!($auth = $this->request->getAuth())) {
            return;
        }
        switch ($auth['scheme']) {
            case HTTP_Request2::AUTH_BASIC:
                $headers['authorization'] = 
                    'Basic ' . base64_encode($auth['user'] . ':' . $auth['password']);
                break;

            case HTTP_Request2::AUTH_DIGEST:
                unset($this->serverChallenge);
                $fullUrl = ('/' == $requestUrl[0])?
                           $this->request->getUrl()->getScheme() . '://' .
                            $requestHost . $requestUrl:
                           $requestUrl;
                foreach (array_keys(self::$challenges) as $key) {
                    if ($key == substr($fullUrl, 0, strlen($key))) {
                        $headers['authorization'] = $this->createDigestResponse(
                            $auth['user'], $auth['password'], 
                            $requestUrl, self::$challenges[$key]
                        );
                        $this->serverChallenge =& self::$challenges[$key];
                        break;
                    }
                }
                break;

            default:
                throw new HTTP_Request2_Exception(
                    "Unknown HTTP authentication scheme '{$auth['scheme']}'"
                );
        }
    }

   /**
    * Adds 'Proxy-Authorization' header (if needed) to request headers array
    *
    * @param    array   request headers
    * @param    string  request URL (needed for digest authentication)
    * @throws   HTTP_Request2_Exception
    */
    protected function addProxyAuthorizationHeader(&$headers, $requestUrl)
    {
        if (!$this->request->getConfig('proxy_host') ||
            !($user = $this->request->getConfig('proxy_user')) ||
            (0 == strcasecmp('https', $this->request->getUrl()->getScheme()) &&
             HTTP_Request2::METHOD_CONNECT != $this->request->getMethod())
        ) {
            return;
        }

        $password = $this->request->getConfig('proxy_password');
        switch ($this->request->getConfig('proxy_auth_scheme')) {
            case HTTP_Request2::AUTH_BASIC:
                $headers['proxy-authorization'] =
                    'Basic ' . base64_encode($user . ':' . $password);
                break;

            case HTTP_Request2::AUTH_DIGEST:
                unset($this->proxyChallenge);
                $proxyUrl = 'proxy://' . $this->request->getConfig('proxy_host') .
                            ':' . $this->request->getConfig('proxy_port');
                if (!empty(self::$challenges[$proxyUrl])) {
                    $headers['proxy-authorization'] = $this->createDigestResponse(
                        $user, $password,
                        $requestUrl, self::$challenges[$proxyUrl]
                    );
                    $this->proxyChallenge =& self::$challenges[$proxyUrl];
                }
                break;

            default:
                throw new HTTP_Request2_Exception(
                    "Unknown HTTP authentication scheme '" .
                    $this->request->getConfig('proxy_auth_scheme') . "'"
                );
        }
    }


   /**
    * Creates the string with the Request-Line and request headers
    *
    * @return   string
    * @throws   HTTP_Request2_Exception
    */
    protected function prepareHeaders()
    {
        $headers = $this->request->getHeaders();
        $url     = $this->request->getUrl();
        $connect = HTTP_Request2::METHOD_CONNECT == $this->request->getMethod();
        $host    = $url->getHost();

        $defaultPort = 0 == strcasecmp($url->getScheme(), 'https')? 443: 80;
        if (($port = $url->getPort()) && $port != $defaultPort || $connect) {
            $host .= ':' . (empty($port)? $defaultPort: $port);
        }
        // Do not overwrite explicitly set 'Host' header, see bug #16146
        if (!isset($headers['host'])) {
            $headers['host'] = $host;
        }

        if ($connect) {
            $requestUrl = $host;

        } else {
            if (!$this->request->getConfig('proxy_host') ||
                0 == strcasecmp($url->getScheme(), 'https')
            ) {
                $requestUrl = '';
            } else {
                $requestUrl = $url->getScheme() . '://' . $host;
            }
            $path        = $url->getPath();
            $query       = $url->getQuery();
            $requestUrl .= (empty($path)? '/': $path) . (empty($query)? '': '?' . $query);
        }

        if ('1.1' == $this->request->getConfig('protocol_version') &&
            extension_loaded('zlib') && !isset($headers['accept-encoding'])
        ) {
            $headers['accept-encoding'] = 'gzip, deflate';
        }

        $this->addAuthorizationHeader($headers, $host, $requestUrl);
        $this->addProxyAuthorizationHeader($headers, $requestUrl);
        $this->calculateRequestLength($headers);

        $headersStr = $this->request->getMethod() . ' ' . $requestUrl . ' HTTP/' .
                      $this->request->getConfig('protocol_version') . "\r\n";
        foreach ($headers as $name => $value) {
            $canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
            $headersStr   .= $canonicalName . ': ' . $value . "\r\n";
        }
        return $headersStr . "\r\n";
    }

   /**
    * Sends the request body
    *
    * @throws   HTTP_Request2_Exception
    */
    protected function writeBody()
    {
        if (in_array($this->request->getMethod(), self::$bodyDisallowed) ||
            0 == $this->contentLength
        ) {
            return;
        }

        $position   = 0;
        $bufferSize = $this->request->getConfig('buffer_size');
        while ($position < $this->contentLength) {
            if (is_string($this->requestBody)) {
                $str = substr($this->requestBody, $position, $bufferSize);
            } elseif (is_resource($this->requestBody)) {
                $str = fread($this->requestBody, $bufferSize);
            } else {
                $str = $this->requestBody->read($bufferSize);
            }
            if (false === @fwrite($this->socket, $str, strlen($str))) {
                throw new HTTP_Request2_Exception('Error writing request');
            }
            // Provide the length of written string to the observer, request #7630
            $this->request->setLastEvent('sentBodyPart', strlen($str));
            $position += strlen($str); 
        }
    }

   /**
    * Reads the remote server's response
    *
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    protected function readResponse()
    {
        $bufferSize = $this->request->getConfig('buffer_size');

        do {
            $response = new HTTP_Request2_Response($this->readLine($bufferSize), true);
            do {
                $headerLine = $this->readLine($bufferSize);
                $response->parseHeaderLine($headerLine);
            } while ('' != $headerLine);
        } while (in_array($response->getStatus(), array(100, 101)));

        $this->request->setLastEvent('receivedHeaders', $response);

        // No body possible in such responses
        if (HTTP_Request2::METHOD_HEAD == $this->request->getMethod() ||
            (HTTP_Request2::METHOD_CONNECT == $this->request->getMethod() &&
             200 <= $response->getStatus() && 300 > $response->getStatus()) ||
            in_array($response->getStatus(), array(204, 304))
        ) {
            return $response;
        }

        $chunked = 'chunked' == $response->getHeader('transfer-encoding');
        $length  = $response->getHeader('content-length');
        $hasBody = false;
        if ($chunked || null === $length || 0 < intval($length)) {
            // RFC 2616, section 4.4:
            // 3. ... If a message is received with both a
            // Transfer-Encoding header field and a Content-Length header field,
            // the latter MUST be ignored.
            $toRead = ($chunked || null === $length)? null: $length;
            $this->chunkLength = 0;

            while (!feof($this->socket) && (is_null($toRead) || 0 < $toRead)) {
                if ($chunked) {
                    $data = $this->readChunked($bufferSize);
                } elseif (is_null($toRead)) {
                    $data = $this->fread($bufferSize);
                } else {
                    $data    = $this->fread(min($toRead, $bufferSize));
                    $toRead -= strlen($data);
                }
                if ('' == $data && (!$this->chunkLength || feof($this->socket))) {
                    break;
                }

                $hasBody = true;
                if ($this->request->getConfig('store_body')) {
                    $response->appendBody($data);
                }
                if (!in_array($response->getHeader('content-encoding'), array('identity', null))) {
                    $this->request->setLastEvent('receivedEncodedBodyPart', $data);
                } else {
                    $this->request->setLastEvent('receivedBodyPart', $data);
                }
            }
        }

        if ($hasBody) {
            $this->request->setLastEvent('receivedBody', $response);
        }
        return $response;
    }

   /**
    * Reads until either the end of the socket or a newline, whichever comes first 
    *
    * Strips the trailing newline from the returned data, handles global 
    * request timeout. Method idea borrowed from Net_Socket PEAR package. 
    *
    * @param    int     buffer size to use for reading
    * @return   Available data up to the newline (not including newline)
    * @throws   HTTP_Request2_Exception     In case of timeout
    */
    protected function readLine($bufferSize)
    {
        $line = '';
        while (!feof($this->socket)) {
            if ($this->timeout) {
                stream_set_timeout($this->socket, max($this->timeout - time(), 1));
            }
            $line .= @fgets($this->socket, $bufferSize);
            $info  = stream_get_meta_data($this->socket);
            if ($info['timed_out'] || $this->timeout && time() > $this->timeout) {
                throw new HTTP_Request2_Exception(
                    'Request timed out after ' . 
                    $this->request->getConfig('timeout') . ' second(s)'
                );
            }
            if (substr($line, -1) == "\n") {
                return rtrim($line, "\r\n");
            }
        }
        return $line;
    }

   /**
    * Wrapper around fread(), handles global request timeout
    *
    * @param    int     Reads up to this number of bytes
    * @return   Data read from socket
    * @throws   HTTP_Request2_Exception     In case of timeout
    */
    protected function fread($length)
    {
        if ($this->timeout) {
            stream_set_timeout($this->socket, max($this->timeout - time(), 1));
        }
        $data = fread($this->socket, $length);
        $info = stream_get_meta_data($this->socket);
        if ($info['timed_out'] || $this->timeout && time() > $this->timeout) {
            throw new HTTP_Request2_Exception(
                'Request timed out after ' . 
                $this->request->getConfig('timeout') . ' second(s)'
            );
        }
        return $data;
    }

   /**
    * Reads a part of response body encoded with chunked Transfer-Encoding
    *
    * @param    int     buffer size to use for reading
    * @return   string
    * @throws   HTTP_Request2_Exception
    */
    protected function readChunked($bufferSize)
    {
        // at start of the next chunk?
        if (0 == $this->chunkLength) {
            $line = $this->readLine($bufferSize);
            if (!preg_match('/^([0-9a-f]+)/i', $line, $matches)) {
                throw new HTTP_Request2_Exception(
                    "Cannot decode chunked response, invalid chunk length '{$line}'"
                );
            } else {
                $this->chunkLength = hexdec($matches[1]);
                // Chunk with zero length indicates the end
                if (0 == $this->chunkLength) {
                    $this->readLine($bufferSize);
                    return '';
                }
            }
        }
        $data = $this->fread(min($this->chunkLength, $bufferSize));
        $this->chunkLength -= strlen($data);
        if (0 == $this->chunkLength) {
            $this->readLine($bufferSize); // Trailing CRLF
        }
        return $data;
    }
}

class HTTP_Request2_Adapter_Mock extends HTTP_Request2_Adapter
{
   /**
    * A queue of responses to be returned by sendRequest()
    * @var  array 
    */
    protected $responses = array();

   /**
    * Returns the next response from the queue built by addResponse()
    *
    * If the queue is empty will return default empty response with status 400,
    * if an Exception object was added to the queue it will be thrown.
    *
    * @param    HTTP_Request2
    * @return   HTTP_Request2_Response
    * @throws   Exception
    */
    public function sendRequest(HTTP_Request2 $request)
    {
        if (count($this->responses) > 0) {
            $response = array_shift($this->responses);
            if ($response instanceof HTTP_Request2_Response) {
                return $response;
            } else {
                // rethrow the exception,
                $class   = get_class($response);
                $message = $response->getMessage();
                $code    = $response->getCode();
                throw new $class($message, $code);
            }
        } else {
            return self::createResponseFromString("HTTP/1.1 400 Bad Request\r\n\r\n");
        }
    }

   /**
    * Adds response to the queue
    *
    * @param    mixed   either a string, a pointer to an open file,
    *                   a HTTP_Request2_Response or Exception object
    * @throws   HTTP_Request2_Exception
    */
    public function addResponse($response)
    {
        if (is_string($response)) {
            $response = self::createResponseFromString($response);
        } elseif (is_resource($response)) {
            $response = self::createResponseFromFile($response);
        } elseif (!$response instanceof HTTP_Request2_Response &&
                  !$response instanceof Exception
        ) {
            throw new HTTP_Request2_Exception('Parameter is not a valid response');
        }
        $this->responses[] = $response;
    }

   /**
    * Creates a new HTTP_Request2_Response object from a string
    *
    * @param    string
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    public static function createResponseFromString($str)
    {
        $parts       = preg_split('!(\r?\n){2}!m', $str, 2);
        $headerLines = explode("\n", $parts[0]); 
        $response    = new HTTP_Request2_Response(array_shift($headerLines));
        foreach ($headerLines as $headerLine) {
            $response->parseHeaderLine($headerLine);
        }
        $response->parseHeaderLine('');
        if (isset($parts[1])) {
            $response->appendBody($parts[1]);
        }
        return $response;
    }

   /**
    * Creates a new HTTP_Request2_Response object from a file
    *
    * @param    resource    file pointer returned by fopen()
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    public static function createResponseFromFile($fp)
    {
        $response = new HTTP_Request2_Response(fgets($fp));
        do {
            $headerLine = fgets($fp);
            $response->parseHeaderLine($headerLine);
        } while ('' != trim($headerLine));

        while (!feof($fp)) {
            $response->appendBody(fread($fp, 8192));
        }
        return $response;
    }
}

class HTTP_Request2_Adapter_Curl extends HTTP_Request2_Adapter
{
   /**
    * Mapping of header names to cURL options
    * @var  array
    */
    protected static $headerMap = array(
        'accept-encoding' => CURLOPT_ENCODING,
        'cookie'          => CURLOPT_COOKIE,
        'referer'         => CURLOPT_REFERER,
        'user-agent'      => CURLOPT_USERAGENT
    );

   /**
    * Mapping of SSL context options to cURL options
    * @var  array
    */
    protected static $sslContextMap = array(
        'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
        'ssl_cafile'      => CURLOPT_CAINFO,
        'ssl_capath'      => CURLOPT_CAPATH,
        'ssl_local_cert'  => CURLOPT_SSLCERT,
        'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD
   );

   /**
    * Response being received
    * @var  HTTP_Request2_Response
    */
    protected $response;

   /**
    * Whether 'sentHeaders' event was sent to observers
    * @var  boolean
    */
    protected $eventSentHeaders = false;

   /**
    * Whether 'receivedHeaders' event was sent to observers
    * @var boolean
    */
    protected $eventReceivedHeaders = false;

   /**
    * Position within request body
    * @var  integer
    * @see  callbackReadBody()
    */
    protected $position = 0;

   /**
    * Information about last transfer, as returned by curl_getinfo()
    * @var  array
    */
    protected $lastInfo;

   /**
    * Sends request to the remote server and returns its response
    *
    * @param    HTTP_Request2
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    public function sendRequest(HTTP_Request2 $request)
    {
        if (!extension_loaded('curl')) {
            throw new HTTP_Request2_Exception('cURL extension not available');
        }

        $this->request              = $request;
        $this->response             = null;
        $this->position             = 0;
        $this->eventSentHeaders     = false;
        $this->eventReceivedHeaders = false;

        try {
            if (false === curl_exec($ch = $this->createCurlHandle())) {
                $errorMessage = 'Error sending request: #' . curl_errno($ch) .
                                                       ' ' . curl_error($ch);
            }
        } catch (Exception $e) {
        }
        $this->lastInfo = curl_getinfo($ch);
        curl_close($ch);

        if (!empty($e)) {
            throw $e;
        } elseif (!empty($errorMessage)) {
            throw new HTTP_Request2_Exception($errorMessage);
        }

        if (0 < $this->lastInfo['size_download']) {
            $this->request->setLastEvent('receivedBody', $this->response);
        }
        return $this->response;
    }

   /**
    * Returns information about last transfer
    *
    * @return   array   associative array as returned by curl_getinfo()
    */
    public function getInfo()
    {
        return $this->lastInfo;
    }

   /**
    * Creates a new cURL handle and populates it with data from the request
    *
    * @return   resource    a cURL handle, as created by curl_init()
    * @throws   HTTP_Request2_Exception
    */
    protected function createCurlHandle()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            // setup callbacks
            CURLOPT_READFUNCTION   => array($this, 'callbackReadBody'),
            CURLOPT_HEADERFUNCTION => array($this, 'callbackWriteHeader'),
            CURLOPT_WRITEFUNCTION  => array($this, 'callbackWriteBody'),
            // disallow redirects
            CURLOPT_FOLLOWLOCATION => false,
            // buffer size
            CURLOPT_BUFFERSIZE     => $this->request->getConfig('buffer_size'),
            // connection timeout
            CURLOPT_CONNECTTIMEOUT => $this->request->getConfig('connect_timeout'),
            // save full outgoing headers, in case someone is interested
            CURLINFO_HEADER_OUT    => true,
            // request url
            CURLOPT_URL            => $this->request->getUrl()->getUrl()
        ));

        // request timeout
        if ($timeout = $this->request->getConfig('timeout')) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }

        // set HTTP version
        switch ($this->request->getConfig('protocol_version')) {
            case '1.0':
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
                break;
            case '1.1':
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }

        // set request method
        switch ($this->request->getMethod()) {
            case HTTP_Request2::METHOD_GET:
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case HTTP_Request2::METHOD_POST:
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->request->getMethod());
        }

        // set proxy, if needed
        if ($host = $this->request->getConfig('proxy_host')) {
            if (!($port = $this->request->getConfig('proxy_port'))) {
                throw new HTTP_Request2_Exception('Proxy port not provided');
            }
            curl_setopt($ch, CURLOPT_PROXY, $host . ':' . $port);
            if ($user = $this->request->getConfig('proxy_user')) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $user . ':' .
                            $this->request->getConfig('proxy_password'));
                switch ($this->request->getConfig('proxy_auth_scheme')) {
                    case HTTP_Request2::AUTH_BASIC:
                        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                        break;
                    case HTTP_Request2::AUTH_DIGEST:
                        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_DIGEST);
                }
            }
        }

        // set authentication data
        if ($auth = $this->request->getAuth()) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth['user'] . ':' . $auth['password']);
            switch ($auth['scheme']) {
                case HTTP_Request2::AUTH_BASIC:
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    break;
                case HTTP_Request2::AUTH_DIGEST:
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            }
        }

        // set SSL options
        if (0 == strcasecmp($this->request->getUrl()->getScheme(), 'https')) {
            foreach ($this->request->getConfig() as $name => $value) {
                if ('ssl_verify_host' == $name && null !== $value) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $value? 2: 0);
                } elseif (isset(self::$sslContextMap[$name]) && null !== $value) {
                    curl_setopt($ch, self::$sslContextMap[$name], $value);
                }
            }
        }

        $headers = $this->request->getHeaders();
        // make cURL automagically send proper header
        if (!isset($headers['accept-encoding'])) {
            $headers['accept-encoding'] = '';
        }

        // set headers having special cURL keys
        foreach (self::$headerMap as $name => $option) {
            if (isset($headers[$name])) {
                curl_setopt($ch, $option, $headers[$name]);
                unset($headers[$name]);
            }
        }

        $this->calculateRequestLength($headers);

        // set headers not having special keys
        $headersFmt = array();
        foreach ($headers as $name => $value) {
            $canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
            $headersFmt[]  = $canonicalName . ': ' . $value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFmt);

        return $ch;
    }

   /**
    * Callback function called by cURL for reading the request body
    *
    * @param    resource    cURL handle
    * @param    resource    file descriptor (not used)
    * @param    integer     maximum length of data to return
    * @return   string      part of the request body, up to $length bytes 
    */
    protected function callbackReadBody($ch, $fd, $length)
    {
        if (!$this->eventSentHeaders) {
            $this->request->setLastEvent(
                'sentHeaders', curl_getinfo($ch, CURLINFO_HEADER_OUT)
            );
            $this->eventSentHeaders = true;
        }
        if (in_array($this->request->getMethod(), self::$bodyDisallowed) ||
            0 == $this->contentLength || $this->position >= $this->contentLength
        ) {
            return '';
        }
        if (is_string($this->requestBody)) {
            $string = substr($this->requestBody, $this->position, $length);
        } elseif (is_resource($this->requestBody)) {
            $string = fread($this->requestBody, $length);
        } else {
            $string = $this->requestBody->read($length);
        }
        $this->request->setLastEvent('sentBodyPart', strlen($string));
        $this->position += strlen($string);
        return $string;
    }

   /**
    * Callback function called by cURL for saving the response headers
    *
    * @param    resource    cURL handle
    * @param    string      response header (with trailing CRLF)
    * @return   integer     number of bytes saved
    * @see      HTTP_Request2_Response::parseHeaderLine()
    */
    protected function callbackWriteHeader($ch, $string)
    {
        // we may receive a second set of headers if doing e.g. digest auth
        if ($this->eventReceivedHeaders || !$this->eventSentHeaders) {
            // don't bother with 100-Continue responses (bug #15785)
            if (!$this->eventSentHeaders ||
                $this->response->getStatus() >= 200
            ) {
                $this->request->setLastEvent(
                    'sentHeaders', curl_getinfo($ch, CURLINFO_HEADER_OUT)
                );
            }
            $this->eventSentHeaders = true;
            // we'll need a new response object
            if ($this->eventReceivedHeaders) {
                $this->eventReceivedHeaders = false;
                $this->response             = null;
            }
        }
        if (empty($this->response)) {
            $this->response = new HTTP_Request2_Response($string, false);
        } else {
            $this->response->parseHeaderLine($string);
            if ('' == trim($string)) {
                // don't bother with 100-Continue responses (bug #15785)
                if (200 <= $this->response->getStatus()) {
                    $this->request->setLastEvent('receivedHeaders', $this->response);
                }
                $this->eventReceivedHeaders = true;
            }
        }
        return strlen($string);
    }

   /**
    * Callback function called by cURL for saving the response body
    *
    * @param    resource    cURL handle (not used)
    * @param    string      part of the response body
    * @return   integer     number of bytes saved
    * @see      HTTP_Request2_Response::appendBody()
    */
    protected function callbackWriteBody($ch, $string)
    {
        // cURL calls WRITEFUNCTION callback without calling HEADERFUNCTION if 
        // response doesn't start with proper HTTP status line (see bug #15716)
        if (empty($this->response)) {
            throw new HTTP_Request2_Exception("Malformed response: {$string}");
        }
        if ($this->request->getConfig('store_body')) {
            $this->response->appendBody($string);
        }
        $this->request->setLastEvent('receivedBodyPart', $string);
        return strlen($string);
    }
}

class Net_URL2
{
    /**
     * Do strict parsing in resolve() (see RFC 3986, section 5.2.2). Default
     * is true.
     */
    const OPTION_STRICT = 'strict';

    /**
     * Represent arrays in query using PHP's [] notation. Default is true.
     */
    const OPTION_USE_BRACKETS = 'use_brackets';

    /**
     * URL-encode query variable keys. Default is true.
     */
    const OPTION_ENCODE_KEYS = 'encode_keys';

    /**
     * Query variable separators when parsing the query string. Every character
     * is considered a separator. Default is specified by the
     * arg_separator.input php.ini setting (this defaults to "&").
     */
    const OPTION_SEPARATOR_INPUT = 'input_separator';

    /**
     * Query variable separator used when generating the query string. Default
     * is specified by the arg_separator.output php.ini setting (this defaults
     * to "&").
     */
    const OPTION_SEPARATOR_OUTPUT = 'output_separator';

    /**
     * Default options corresponds to how PHP handles $_GET.
     */
    private $_options = array(
        self::OPTION_STRICT           => true,
        self::OPTION_USE_BRACKETS     => true,
        self::OPTION_ENCODE_KEYS      => true,
        self::OPTION_SEPARATOR_INPUT  => 'x&',
        self::OPTION_SEPARATOR_OUTPUT => 'x&',
        );

    /**
     * @var  string|bool
     */
    private $_scheme = false;

    /**
     * @var  string|bool
     */
    private $_userinfo = false;

    /**
     * @var  string|bool
     */
    private $_host = false;

    /**
     * @var  int|bool
     */
    private $_port = false;

    /**
     * @var  string
     */
    private $_path = '';

    /**
     * @var  string|bool
     */
    private $_query = false;

    /**
     * @var  string|bool
     */
    private $_fragment = false;

    /**
     * Constructor.
     *
     * @param string $url     an absolute or relative URL
     * @param array  $options an array of OPTION_xxx constants
     */
    public function __construct($url, $options = null)
    {
        $this->setOption(self::OPTION_SEPARATOR_INPUT,
                         ini_get('arg_separator.input'));
        $this->setOption(self::OPTION_SEPARATOR_OUTPUT,
                         ini_get('arg_separator.output'));
        if (is_array($options)) {
            foreach ($options as $optionName => $value) {
                $this->setOption($optionName, $value);
            }
        }

        if (preg_match('@^([a-z][a-z0-9.+-]*):@i', $url, $reg)) {
            $this->_scheme = $reg[1];
            $url = substr($url, strlen($reg[0]));
        }

        if (preg_match('@^//([^/#?]+)@', $url, $reg)) {
            $this->setAuthority($reg[1]);
            $url = substr($url, strlen($reg[0]));
        }

        $i = strcspn($url, '?#');
        $this->_path = substr($url, 0, $i);
        $url = substr($url, $i);

        if (preg_match('@^\?([^#]*)@', $url, $reg)) {
            $this->_query = $reg[1];
            $url = substr($url, strlen($reg[0]));
        }

        if ($url) {
            $this->_fragment = substr($url, 1);
        }
    }

    /**
     * Magic Setter.
     *
     * This method will magically set the value of a private variable ($var)
     * with the value passed as the args
     *
     * @param  string $var      The private variable to set.
     * @param  mixed  $arg      An argument of any type.
     * @return void
     */
    public function __set($var, $arg)
    {
        $method = 'set' . $var;
        if (method_exists($this, $method)) {
            $this->$method($arg);
        }
    }
    
    /**
     * Magic Getter.
     *
     * This is the magic get method to retrieve the private variable 
     * that was set by either __set() or it's setter...
     * 
     * @param  string $var         The property name to retrieve.
     * @return mixed  $this->$var  Either a boolean false if the
     *                             property is not set or the value
     *                             of the private property.
     */
    public function __get($var)
    {
        $method = 'get' . $var;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        return false;
    }
    
    /**
     * Returns the scheme, e.g. "http" or "urn", or false if there is no
     * scheme specified, i.e. if this is a relative URL.
     *
     * @return  string|bool
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * Sets the scheme, e.g. "http" or "urn". Specify false if there is no
     * scheme specified, i.e. if this is a relative URL.
     *
     * @param string|bool $scheme e.g. "http" or "urn", or false if there is no
     *                            scheme specified, i.e. if this is a relative
     *                            URL
     *
     * @return void
     * @see    getScheme()
     */
    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
    }

    /**
     * Returns the user part of the userinfo part (the part preceding the first
     *  ":"), or false if there is no userinfo part.
     *
     * @return  string|bool
     */
    public function getUser()
    {
        return $this->_userinfo !== false
            ? preg_replace('@:.*$@', '', $this->_userinfo)
            : false;
    }

    /**
     * Returns the password part of the userinfo part (the part after the first
     *  ":"), or false if there is no userinfo part (i.e. the URL does not
     * contain "@" in front of the hostname) or the userinfo part does not
     * contain ":".
     *
     * @return  string|bool
     */
    public function getPassword()
    {
        return $this->_userinfo !== false
            ? substr(strstr($this->_userinfo, ':'), 1)
            : false;
    }

    /**
     * Returns the userinfo part, or false if there is none, i.e. if the
     * authority part does not contain "@".
     *
     * @return  string|bool
     */
    public function getUserinfo()
    {
        return $this->_userinfo;
    }

    /**
     * Sets the userinfo part. If two arguments are passed, they are combined
     * in the userinfo part as username ":" password.
     *
     * @param string|bool $userinfo userinfo or username
     * @param string|bool $password optional password, or false
     *
     * @return void
     */
    public function setUserinfo($userinfo, $password = false)
    {
        $this->_userinfo = $userinfo;
        if ($password !== false) {
            $this->_userinfo .= ':' . $password;
        }
    }

    /**
     * Returns the host part, or false if there is no authority part, e.g.
     * relative URLs.
     *
     * @return  string|bool a hostname, an IP address, or false
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Sets the host part. Specify false if there is no authority part, e.g.
     * relative URLs.
     *
     * @param string|bool $host a hostname, an IP address, or false
     *
     * @return void
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * Returns the port number, or false if there is no port number specified,
     * i.e. if the default port is to be used.
     *
     * @return  int|bool
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Sets the port number. Specify false if there is no port number specified,
     * i.e. if the default port is to be used.
     *
     * @param int|bool $port a port number, or false
     *
     * @return void
     */
    public function setPort($port)
    {
        $this->_port = intval($port);
    }

    /**
     * Returns the authority part, i.e. [ userinfo "@" ] host [ ":" port ], or
     * false if there is no authority.
     *
     * @return string|bool
     */
    public function getAuthority()
    {
        if (!$this->_host) {
            return false;
        }

        $authority = '';

        if ($this->_userinfo !== false) {
            $authority .= $this->_userinfo . '@';
        }

        $authority .= $this->_host;

        if ($this->_port !== false) {
            $authority .= ':' . $this->_port;
        }

        return $authority;
    }

    /**
     * Sets the authority part, i.e. [ userinfo "@" ] host [ ":" port ]. Specify
     * false if there is no authority.
     *
     * @param string|false $authority a hostname or an IP addresse, possibly
     *                                with userinfo prefixed and port number
     *                                appended, e.g. "foo:bar@example.org:81".
     *
     * @return void
     */
    public function setAuthority($authority)
    {
        $this->_userinfo = false;
        $this->_host     = false;
        $this->_port     = false;
        if (preg_match('@^(([^\@]*)\@)?([^:]+)(:(\d*))?$@', $authority, $reg)) {
            if ($reg[1]) {
                $this->_userinfo = $reg[2];
            }

            $this->_host = $reg[3];
            if (isset($reg[5])) {
                $this->_port = intval($reg[5]);
            }
        }
    }

    /**
     * Returns the path part (possibly an empty string).
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Sets the path part (possibly an empty string).
     *
     * @param string $path a path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * Returns the query string (excluding the leading "?"), or false if "?"
     * is not present in the URL.
     *
     * @return  string|bool
     * @see     self::getQueryVariables()
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Sets the query string (excluding the leading "?"). Specify false if "?"
     * is not present in the URL.
     *
     * @param string|bool $query a query string, e.g. "foo=1&bar=2"
     *
     * @return void
     * @see   self::setQueryVariables()
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * Returns the fragment name, or false if "#" is not present in the URL.
     *
     * @return  string|bool
     */
    public function getFragment()
    {
        return $this->_fragment;
    }

    /**
     * Sets the fragment name. Specify false if "#" is not present in the URL.
     *
     * @param string|bool $fragment a fragment excluding the leading "#", or
     *                              false
     *
     * @return void
     */
    public function setFragment($fragment)
    {
        $this->_fragment = $fragment;
    }

    /**
     * Returns the query string like an array as the variables would appear in
     * $_GET in a PHP script. If the URL does not contain a "?", an empty array
     * is returned.
     *
     * @return  array
     */
    public function getQueryVariables()
    {
        $pattern = '/[' .
                   preg_quote($this->getOption(self::OPTION_SEPARATOR_INPUT), '/') .
                   ']/';
        $parts   = preg_split($pattern, $this->_query, -1, PREG_SPLIT_NO_EMPTY);
        $return  = array();

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                list($key, $value) = explode('=', $part, 2);
            } else {
                $key   = $part;
                $value = null;
            }

            if ($this->getOption(self::OPTION_ENCODE_KEYS)) {
                $key = rawurldecode($key);
            }
            $value = rawurldecode($value);

            if ($this->getOption(self::OPTION_USE_BRACKETS) &&
                preg_match('#^(.*)\[([0-9a-z_-]*)\]#i', $key, $matches)) {

                $key = $matches[1];
                $idx = $matches[2];

                // Ensure is an array
                if (empty($return[$key]) || !is_array($return[$key])) {
                    $return[$key] = array();
                }

                // Add data
                if ($idx === '') {
                    $return[$key][] = $value;
                } else {
                    $return[$key][$idx] = $value;
                }
            } elseif (!$this->getOption(self::OPTION_USE_BRACKETS)
                      && !empty($return[$key])
            ) {
                $return[$key]   = (array) $return[$key];
                $return[$key][] = $value;
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Sets the query string to the specified variable in the query string.
     *
     * @param array $array (name => value) array
     *
     * @return void
     */
    public function setQueryVariables(array $array)
    {
        if (!$array) {
            $this->_query = false;
        } else {
            foreach ($array as $name => $value) {
                if ($this->getOption(self::OPTION_ENCODE_KEYS)) {
                    $name = self::urlencode($name);
                }

                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $parts[] = $this->getOption(self::OPTION_USE_BRACKETS)
                            ? sprintf('%s[%s]=%s', $name, $k, $v)
                            : ($name . '=' . $v);
                    }
                } elseif (!is_null($value)) {
                    $parts[] = $name . '=' . self::urlencode($value);
                } else {
                    $parts[] = $name;
                }
            }
            $this->_query = implode($this->getOption(self::OPTION_SEPARATOR_OUTPUT),
                                    $parts);
        }
    }

    /**
     * Sets the specified variable in the query string.
     *
     * @param string $name  variable name
     * @param mixed  $value variable value
     *
     * @return  array
     */
    public function setQueryVariable($name, $value)
    {
        $array = $this->getQueryVariables();
        $array[$name] = $value;
        $this->setQueryVariables($array);
    }

    /**
     * Removes the specifed variable from the query string.
     *
     * @param string $name a query string variable, e.g. "foo" in "?foo=1"
     *
     * @return void
     */
    public function unsetQueryVariable($name)
    {
        $array = $this->getQueryVariables();
        unset($array[$name]);
        $this->setQueryVariables($array);
    }

    /**
     * Returns a string representation of this URL.
     *
     * @return  string
     */
    public function getURL()
    {
        // See RFC 3986, section 5.3
        $url = "";

        if ($this->_scheme !== false) {
            $url .= $this->_scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== false) {
            $url .= '//' . $authority;
        }
        $url .= $this->_path;

        if ($this->_query !== false) {
            $url .= '?' . $this->_query;
        }

        if ($this->_fragment !== false) {
            $url .= '#' . $this->_fragment;
        }
    
        return $url;
    }

    /**
     * Returns a string representation of this URL.
     *
     * @return  string
     * @see toString()
     */
    public function __toString()
    {
        return $this->getURL();
    }

    /** 
     * Returns a normalized string representation of this URL. This is useful
     * for comparison of URLs.
     *
     * @return  string
     */
    public function getNormalizedURL()
    {
        $url = clone $this;
        $url->normalize();
        return $url->getUrl();
    }

    /** 
     * Returns a normalized Net_URL2 instance.
     *
     * @return  Net_URL2
     */
    public function normalize()
    {
        // See RFC 3886, section 6

        // Schemes are case-insensitive
        if ($this->_scheme) {
            $this->_scheme = strtolower($this->_scheme);
        }

        // Hostnames are case-insensitive
        if ($this->_host) {
            $this->_host = strtolower($this->_host);
        }

        // Remove default port number for known schemes (RFC 3986, section 6.2.3)
        if ($this->_port &&
            $this->_scheme &&
            $this->_port == getservbyname($this->_scheme, 'tcp')) {

            $this->_port = false;
        }

        // Normalize case of %XX percentage-encodings (RFC 3986, section 6.2.2.1)
        foreach (array('_userinfo', '_host', '_path') as $part) {
            if ($this->$part) {
                $this->$part = preg_replace('/%[0-9a-f]{2}/ie',
                                            'strtoupper("\0")',
                                            $this->$part);
            }
        }

        // Path segment normalization (RFC 3986, section 6.2.2.3)
        $this->_path = self::removeDotSegments($this->_path);

        // Scheme based normalization (RFC 3986, section 6.2.3)
        if ($this->_host && !$this->_path) {
            $this->_path = '/';
        }
    }

    /**
     * Returns whether this instance represents an absolute URL.
     *
     * @return  bool
     */
    public function isAbsolute()
    {
        return (bool) $this->_scheme;
    }

    /**
     * Returns an Net_URL2 instance representing an absolute URL relative to
     * this URL.
     *
     * @param Net_URL2|string $reference relative URL
     *
     * @return Net_URL2
     */
    public function resolve($reference)
    {
        if (!$reference instanceof Net_URL2) {
            $reference = new self($reference);
        }
        if (!$this->isAbsolute()) {
            throw new Exception('Base-URL must be absolute');
        }

        // A non-strict parser may ignore a scheme in the reference if it is
        // identical to the base URI's scheme.
        if (!$this->getOption(self::OPTION_STRICT) && $reference->_scheme == $this->_scheme) {
            $reference->_scheme = false;
        }

        $target = new self('');
        if ($reference->_scheme !== false) {
            $target->_scheme = $reference->_scheme;
            $target->setAuthority($reference->getAuthority());
            $target->_path  = self::removeDotSegments($reference->_path);
            $target->_query = $reference->_query;
        } else {
            $authority = $reference->getAuthority();
            if ($authority !== false) {
                $target->setAuthority($authority);
                $target->_path  = self::removeDotSegments($reference->_path);
                $target->_query = $reference->_query;
            } else {
                if ($reference->_path == '') {
                    $target->_path = $this->_path;
                    if ($reference->_query !== false) {
                        $target->_query = $reference->_query;
                    } else {
                        $target->_query = $this->_query;
                    }
                } else {
                    if (substr($reference->_path, 0, 1) == '/') {
                        $target->_path = self::removeDotSegments($reference->_path);
                    } else {
                        // Merge paths (RFC 3986, section 5.2.3)
                        if ($this->_host !== false && $this->_path == '') {
                            $target->_path = '/' . $this->_path;
                        } else {
                            $i = strrpos($this->_path, '/');
                            if ($i !== false) {
                                $target->_path = substr($this->_path, 0, $i + 1);
                            }
                            $target->_path .= $reference->_path;
                        }
                        $target->_path = self::removeDotSegments($target->_path);
                    }
                    $target->_query = $reference->_query;
                }
                $target->setAuthority($this->getAuthority());
            }
            $target->_scheme = $this->_scheme;
        }

        $target->_fragment = $reference->_fragment;

        return $target;
    }

    /**
     * Removes dots as described in RFC 3986, section 5.2.4, e.g.
     * "/foo/../bar/baz" => "/bar/baz"
     *
     * @param string $path a path
     *
     * @return string a path
     */
    public static function removeDotSegments($path)
    {
        $output = '';

        // Make sure not to be trapped in an infinite loop due to a bug in this
        // method
        $j = 0; 
        while ($path && $j++ < 100) {
            if (substr($path, 0, 2) == './') {
                // Step 2.A
                $path = substr($path, 2);
            } elseif (substr($path, 0, 3) == '../') {
                // Step 2.A
                $path = substr($path, 3);
            } elseif (substr($path, 0, 3) == '/./' || $path == '/.') {
                // Step 2.B
                $path = '/' . substr($path, 3);
            } elseif (substr($path, 0, 4) == '/../' || $path == '/..') {
                // Step 2.C
                $path   = '/' . substr($path, 4);
                $i      = strrpos($output, '/');
                $output = $i === false ? '' : substr($output, 0, $i);
            } elseif ($path == '.' || $path == '..') {
                // Step 2.D
                $path = '';
            } else {
                // Step 2.E
                $i = strpos($path, '/');
                if ($i === 0) {
                    $i = strpos($path, '/', 1);
                }
                if ($i === false) {
                    $i = strlen($path);
                }
                $output .= substr($path, 0, $i);
                $path = substr($path, $i);
            }
        }

        return $output;
    }

    /**
     * Percent-encodes all non-alphanumeric characters except these: _ . - ~
     * Similar to PHP's rawurlencode(), except that it also encodes ~ in PHP
     * 5.2.x and earlier.
     *
     * @param  $raw the string to encode
     * @return string
     */
    public static function urlencode($string)
    {
    	$encoded = rawurlencode($string);
	// This is only necessary in PHP < 5.3.
	$encoded = str_replace('%7E', '~', $encoded);
	return $encoded;
    }

    /**
     * Returns a Net_URL2 instance representing the canonical URL of the
     * currently executing PHP script.
     * 
     * @return  string
     */
    public static function getCanonical()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            // ALERT - no current URL
            throw new Exception('Script was not called through a webserver');
        }

        // Begin with a relative URL
        $url = new self($_SERVER['PHP_SELF']);
        $url->_scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $url->_host   = $_SERVER['SERVER_NAME'];
        $port = intval($_SERVER['SERVER_PORT']);
        if ($url->_scheme == 'http' && $port != 80 ||
            $url->_scheme == 'https' && $port != 443) {

            $url->_port = $port;
        }
        return $url;
    }

    /**
     * Returns the URL used to retrieve the current request.
     *
     * @return  string
     */
    public static function getRequestedURL()
    {
        return self::getRequested()->getUrl();
    }

    /**
     * Returns a Net_URL2 instance representing the URL used to retrieve the
     * current request.
     *
     * @return  Net_URL2
     */
    public static function getRequested()
    {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            // ALERT - no current URL
            throw new Exception('Script was not called through a webserver');
        }

        // Begin with a relative URL
        $url = new self($_SERVER['REQUEST_URI']);
        $url->_scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        // Set host and possibly port
        $url->setAuthority($_SERVER['HTTP_HOST']);
        return $url;
    }

    /**
     * Sets the specified option.
     *
     * @param string $optionName a self::OPTION_ constant
     * @param mixed  $value      option value  
     *
     * @return void
     * @see  self::OPTION_STRICT
     * @see  self::OPTION_USE_BRACKETS
     * @see  self::OPTION_ENCODE_KEYS
     */
    function setOption($optionName, $value)
    {
        if (!array_key_exists($optionName, $this->_options)) {
            return false;
        }
        $this->_options[$optionName] = $value;
    }

    /**
     * Returns the value of the specified option.
     *
     * @param string $optionName The name of the option to retrieve
     *
     * @return  mixed
     */
    function getOption($optionName)
    {
        return isset($this->_options[$optionName])
            ? $this->_options[$optionName] : false;
    }
}

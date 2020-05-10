<?php
class Filer
{
	private static $cache = array();
	public static function getContent ($file)
	{
		if(isset(self::$cache[$file . "content"])) return self::$cache[$file. "content"];
		ob_start();
	    $retval = include($file);
	    $content = ob_get_clean();
	    
	    self::$cache[$file] = $content;
	    return $content;
	    
	}
	public static function getType($file)
	{
		$n = pathinfo($file);
		$ext = isset($n['extension']) ? $n['extension'] : self::getExtensionFromUrl($file);
		$ext = $ext ? MimeType::getType($ext) : NULL;
		return $ext;
	}
	public static function getExtensionFromUrl($url)
	{

	    $p = strrpos($url,'.');
	    if($p && substr_count($url,'/') >= 3 && $p > strrpos($url,'/'))
	    {
	         return substr($url, $p+1);
	    }
	    return null;
	}
	// gets the full current URL
	public static function selfURL(){
         if(!isset($_SERVER['REQUEST_URI'])){
             $serverrequri = $_SERVER['PHP_SELF'];
         }else{
             $serverrequri =    $_SERVER['REQUEST_URI'];
         }
         $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
         $protocol = self::strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
         $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
         return $protocol."://".$_SERVER['SERVER_NAME'].$port.$serverrequri;   
     }
     public static function strleft($s1, $s2) {
     	return substr($s1, 0, strpos($s1, $s2));
     }
	public static function isSame($oldfile, $newfile)
	{
		if(is_file($oldfile) && is_file($newfile))
		{
			$o = filesize($oldfile);
			$n = filesize($newfile);
			if($o == $n)
			{
				return true;
			}
		}
		return false;
	}
	public static function unique($filepath, $compare_file = NULL, $index = 2, $breakOnSameFile = true)
	{
		if(is_file($filepath))
		{
			if(!is_null($compare_file) && self::isSame($compare_file, $filepath) && $breakOnSameFile) return false;
			$file = pathinfo($filepath);
			$filename = $file["filename"];
			if($e = explode(".", $filename))
			{
				$filename = $e[0];
			}
			$newfile = $file["dirname"] . DIRECTORY_SEPARATOR . $filename . '.' . $index. '.' . $file["extension"];
			$filepath = self::unique($newfile, $compare_file, $index+1, $breakOnSameFile); 
		}
		return $filepath;
	}
	public static function makeUploadDir($path)
	{
		if(!is_dir($path))
		{
			$content = "deny from all";
			$res = self::makeDir($path);
			$res2 = self::save($content, ".htaccess", $path);
			return ($res && $res2) ? true : false;
		}
		return true;
	}
	public static function makeDir($path, $mode = 0777)
	{
		if(!is_dir($path))
		{
			$paths = (explode(DIRECTORY_SEPARATOR, $path));
			
			$first_folder = reset($paths);
			if(strstr($first_folder, ":"))
			{
			   $new_path = "";
			}
			else
			{
			   $new_path = DIRECTORY_SEPARATOR;
			 }
			foreach($paths as $pathe)
			{
				$new_path .=  $pathe. DIRECTORY_SEPARATOR;
				if(!is_dir($new_path))
				{
					if(!@mkdir($new_path, $mode))
					{
						return false;
					}
					@chmod($new_path, $mode);
				}
			}
			
			
		}
		return $path;
	}
	public static function type($file=NULL)
	{
	    return self::getType($file);
	}
	
	public static function mime($file)
	{
	    
          $n = pathinfo($file);
          if(isset($n['extension']))
          {
               return MimeType::mime_content_type($n['extension']);
          }

	}
	public static function filename($file)
	{
          $n = pathinfo($file);
          return $n['basename'];

	}
	public static function extension($file)
	{
          $n = pathinfo($file);
          if(isset($n['extension']))
          {
          return $n['extension'];
          }

	}
	public static function size($file=NULL)
	{
	    return filesize($file);
	}
	public static function modified($file=NULL)
	{
	    return filemtime($file);
	}
	public static function accessed($file=NULL)
	{
	    return fileatime($file);
	}
	public static function save ($content = NULL, $filename = NULL, $dir = NULL, $encode=true)
	{
		$response = false;
		
			$path = pathinfo($dir . $filename);
			$path = $path['dirname'];
			self::makeDir($path);
			if(!$handle = fopen($dir . $filename, "w"))
			{
				trigger_error("Filer: $filename cannot be opened or created");
				exit;
			}
			$content = $encode ? utf8_encode($content) : $content;
			if($content && !$result = fwrite($handle, $content))
			{
				fclose($handle);
				trigger_error("Filer: $filename cannot be written to. Check the dir & file permissions, and to see if it exists.");
				exit;
			}
			fclose($handle);
			@chmod($dir . $filename, 0777);
			$response = $content;
		
		
		return $response;
	}
}

class MimeType
{
	public static function getType($ext)
	{
		$mime = self::mime_content_type($ext);
		if($m = explode("/", $mime))
		{
			$mime = $m[0];
		}
		return $mime;
	}
	public static function mime_content_type( $extension )
	{
		if(strstr($extension, ".")) $extension = substr($extension, strrpos($extension, ".") + 1) ;
		switch(strtolower($extension))
		{
			case "podcast":
				return "application/rss+xml";
				break;
			case "atom":
				return "application/atom+xml";
				break;
			case "rdf":
				return "application/rdf+xml";
				break;
			case "rss":
				return "application/rss+xml";
				break;
			case "rss1":
				return "application/rss+xml";
				break;
			case "rss2":
				return "application/rss+xml";
				break;
			case "323":
				return "text/h323";
				break;
			case "acx":
				return 	"application/internet-property-stream";
				break;
			case "ai":
				return 	"application/postscript";
				break;
			case "aif":
				return 	"audio/x-aiff";
				break;
			case "aifc":
				return 	"audio/x-aiff";
				break;
			case "aiff":
				return 	"audio/x-aiff";
				break;
			case "asf":
				return	"video/x-ms-asf";
				break;
			case "asr":
				return 	"video/x-ms-asf";
				break;
			case "asx":
				return "video/x-ms-asf";
				break;
			case "au":
				return 	"audio/basic";
				break;
			case "avi":
				return 	"video/x-msvideo";
				break;
			case "axs":
				return	"application/olescript";
				break;
			case "bas":
				return	"text/plain";
				break;
			case "bcpio":
				return 	"application/x-bcpio";
				break;
			case "bin":
				return	"application/octet-stream";
				break;
			case "bmp":
				return 	"image/bmp";
				break;
			case "c":
				return 	"text/plain";
				break;
			case "cat":
				return 	"application/vnd.ms-pkiseccat";
				break;
			case "cdf":
				return 	"application/x-cdf";
				break;
			case "cer":
				return 	"application/x-x509-ca-cert";
				break;
			case "class":
				return 	"application/octet-stream";
				break;
			case "clp":
				return 	"application/x-msclip";
				break;
			case "cmx":
				return 	"image/x-cmx";
				break;
			case "cod":
				return 	"image/cis-cod";
				break;
			case "cpio":
				return	"application/x-cpio";
				break;
			case "crd":
				return 	"application/x-mscardfile";
				break;
			case "crl":
				return 	"application/pkix-crl";
				break;
			case "crt":
				return 	"application/x-x509-ca-cert";
				break;
			case "csh":
				return 	"application/x-csh";
				break;
			case "css":
				return 	"text/css";
				break;
			case "dcr":
				return 	"application/x-director";
				break;
			case "der":
				return 	"application/x-x509-ca-cert";
				break;
			case "dir":
				return "application/x-director";
				break;
			case "dll":
				return 	"application/x-msdownload";
				break;
			case "dms":
				return 	"application/octet-stream";
				break;
			case "doc":
				return 	"application/msword";
				break;
			case "dot":
				return 	"application/msword";
				break;
			case "dvi":
				return 	"application/x-dvi";
				break;
			case "dxr":
				return 	"application/x-director";
				break;
			case "eps":
				return 	"application/postscript";
				break;
			case "etx":
				return 	"text/x-setext";
				break;
			case "evy":
				return 	"application/envoy";
				break;
			case "exe":
				return 	"application/octet-stream";
				break;
			case "fif":
				return 	"application/fractals";
				break;
			case "flr":
				return 	"x-world/x-vrml";
				break;
			case "gif":
				return 	"image/gif";
				break;
			case "gtar":
				return 	"application/x-gtar";
				break;
			case "gz":
				return 	"application/x-gzip";
				break;
			case "h":
				return 	"text/plain";
				break;
			case "hdf":
				return 	"application/x-hdf";
				break;
			case "hlp":
				return 	"application/winhlp";
				break;
			case "hqx":
				return "application/mac-binhex40";
				break;
			case "hta":
				return 	"application/hta";
				break;
			case "htc":
				return 	"text/x-component";
				break;
			case "htm":
				return 	"text/html";
				break;
			case "xhtml":
				return "application/xhtml+xml";
				break;
			case "html":
				return 	"text/html";
				break;
			case "htt":
				return 	"text/webviewhtml";
				break;
			case "ico":
				return 	"image/x-icon";
				break;
			case "ief":
				return 	"image/ief";
				break;
			case "iii":
				return 	"application/x-iphone";
				break;
			case "ins":
				return 	"application/x-internet-signup";
				break;
			case "isp":
				return 	"application/x-internet-signup";
				break;
			case "jfif":
				return 	"image/pipeg";
				break;
			case "jpe":
				return 	"image/jpeg";
				break;
			case "jpeg":
				return 	"image/jpeg";
				break;
			case "jpg":
				return 	"image/jpeg";
				break;
		  case "png":
				return 	"image/png";
				break;
			case "js":
				return 	"application/x-javascript";
				break;
			case "latex":
				return "application/x-latex";
				break;
			case "lha":
				return 	"application/octet-stream";
				break;
			case "lsf":
				return 	"video/x-la-asf";
				break;
			case "lsx":
				return 	"video/x-la-asf";
				break;
			case "lzh":
				return 	"application/octet-stream";
				break;
			case "m13":
				return 	"application/x-msmediaview";
				break;
			case "m14":
				return 	"application/x-msmediaview";
				break;
			case "m3u":
				return 	"audio/x-mpegurl";
				break;
			case "man":
				return 	"application/x-troff-man";
				break;
			case "mdb":
				return 	"application/x-msaccess";
				break;
			case "me":
				return 	"application/x-troff-me";
				break;
			case "mht":
				return "message/rfc822";
				break;
			case "mhtml":
				return 	"message/rfc822";
				break;
			case "mid":
				return 	"audio/mid";
				break;
			case "mny":
				return 	"application/x-msmoney";
				break;
			case "mov":
				return 	"video/quicktime";
				break;
			case "movie":
				return 	"video/x-sgi-movie";
				break;
			case "mp2":
				return 	"video/mpeg";
				break;
			case "mp3":
				return 	"audio/mpeg";
				break;
			case "mp4":
				return 	"video/mp4";
				break;
			case "mpa":
				return 	"video/mpeg";
				break;
			case "mpe":
				return 	"video/mpeg";
				break;
			case "mpeg":
				return 	"video/mpeg";
				break;
			case "mpg":
				return 	"video/mpeg";
				break;
			case "mpp":
				return 	"application/vnd.ms-project";
				break;
			case "mpv2":
				return 	"video/mpeg";
				break;
			case "ms":
				return  "application/x-troff-ms";
				break;
			case "mvb":
				return	"application/x-msmediaview";
				break;
			case "nws":
				return 	"message/rfc822";
				break;
			case "oda":
				return 	"application/oda";
				break;
			case "p10":
				return "application/pkcs10";
				break;
			case "p12":
				return 	"application/x-pkcs12";
				break;
			case "p7b":
				return "application/x-pkcs7-certificates";
				break;
			case "p7c":
				return	"application/x-pkcs7-mime";
				break;
			case "p7m":
				return 	"application/x-pkcs7-mime";
				break;
			case "p7r":
				return 	"application/x-pkcs7-certreqresp";
				break;
			case "p7s":
				return 	"application/x-pkcs7-signature";
				break;
			case "pbm":
				return 	"image/x-portable-bitmap";
				break;
			case "pdf":
				return "application/pdf";
				break;
			case "pfx":
				return	"application/x-pkcs12";
				break;
			case "pgm":
				return 	"image/x-portable-graymap";
				break;
			case "pko":
				return 	"application/ynd.ms-pkipko";
				break;
			case "pma":
				return 	"application/x-perfmon";
				break;
			case "pmc":
				return 	"application/x-perfmon";
				break;
			case "pml":
				return 	"application/x-perfmon";
				break;
			case "pmr":
				return 	"application/x-perfmon";
				break;
			case "pmw":
				return 	"application/x-perfmon";
				break;
			case "pnm":
				return "image/x-portable-anymap";
				break;
			case "pot":
				return 	"application/vnd.ms-powerpoint";
				break;
			case "ppm":
				return 	"image/x-portable-pixmap";
				break;
			case "pps":
				return 	"application/vnd.ms-powerpoint";
				break;
			case "ppt":
				return 	"application/vnd.ms-powerpoint";
				break;
			case "prf":
				return 	"application/pics-rules";
				break;
			case "ps":
				return 	"application/postscript";
				break;
			case "pub":
				return 	"application/x-mspublisher";
				break;
			case "qt":
				return "video/quicktime";
				break;
			case "ra":
				return 	"audio/x-pn-realaudio";
				break;
			case "ram":
				return 	"audio/x-pn-realaudio";
				break;
			case "ras":
				return 	"image/x-cmu-raster";
				break;
			case "rgb":
				return 	"image/x-rgb";
				break;
			case "rmi":
				return 	"audio/mid";
				break;
			case "roff":
				return 	"application/x-troff";
				break;
			case "rtf":
				return 	"application/rtf";
				break;
			case "rtx":
				return 	"text/richtext";
				break;
			case "scd":
				return 	"application/x-msschedule";
				break;
			case "sct":
				return 	"text/scriptlet";
				break;
			case "setpay":
				return 	"application/set-payment-initiation";
				break;
			case "setreg":
				return 	"application/set-registration-initiation";
				break;
			case "sh":
				return 	"application/x-sh";
				break;
			case "shar":
				return 	"application/x-shar";
				break;
			case "sit":
				return 	"application/x-stuffit";
				break;
			case "snd":
				return 	"audio/basic";
				break;
			case "spc":
				return 	"application/x-pkcs7-certificates";
				break;
			case "spl":
				return 	"application/futuresplash";
				break;
			case "src":
				return 	"application/x-wais-source";
				break;
			case "sst":
				return 	"application/vnd.ms-pkicertstore";
				break;
			case "stl":
				return 	"application/vnd.ms-pkistl";
				break;
			case "stm":
				return 	"text/html";
				break;
			case "svg":
				return 	"image/svg+xml";
				break;
			case "sv4cpio":
				return 	"application/x-sv4cpio";
				break;
			case "sv4crc":
				return 	"application/x-sv4crc";
				break;
			case "swf":
				return 	"application/x-shockwave-flash";
				break;
			case "t":
				return 	"application/x-troff";
				break;
			case "tar":
				return 	"application/x-tar";
				break;
			case "tcl":
				return 	"application/x-tcl";
				break;
			case "tex":
				return 	"application/x-tex";
				break;
			case "texi":
				return 	"application/x-texinfo";
				break;
			case "texinfo":
				return 	"application/x-texinfo";
				break;
			case "tgz":
				return 	"application/x-compressed";
				break;
			case "tif":
				return 	"image/tiff";
				break;
			case "tiff":
				return 	"image/tiff";
				break;
			case "tr":
				return 	"application/x-troff";
				break;
			case "trm":
				return 	"application/x-msterminal";
				break;
			case "tsv":
				return 	"text/tab-separated-values";
				break;
			case "txt":
				return 	"text/plain";
				break;
			case "uls":
				return 	"text/iuls";
				break;
			case "ustar":
				return 	"application/x-ustar";
				break;
			case "vcf":
				return 	"text/x-vcard";
				break;
			case "vrml":
				return 	"x-world/x-vrml";
				break;
			case "wav":
				return 	"audio/x-wav";
				break;
			case "wcm":
				return 	"application/vnd.ms-works";
				break;
			case "wdb":
				return 	"application/vnd.ms-works";
				break;
			case "wks":
				return 	"application/vnd.ms-works";
				break;
			case "wmf":
				return 	"application/x-msmetafile";
				break;
			case "wps":
				return 	"application/vnd.ms-works";
				break;
			case "wri":
				return 	"application/x-mswrite";
				break;
			case "wrl":
				return 	"x-world/x-vrml";
				break;
			case "wrz":
				return 	"x-world/x-vrml";
				break;
			case "xaf":
				return 	"x-world/x-vrml";
				break;
			case "xbm":
				return 	"image/x-xbitmap";
				break;
			case "xla":
				return 	"application/vnd.ms-excel";
				break;
			case "xlc":
				return 	"application/vnd.ms-excel";
				break;
			case "xlm":
				return 	"application/vnd.ms-excel";
				break;
			case "xls":
				return 	"application/vnd.ms-excel";
				break;
			case "xlt":
				return 	"application/vnd.ms-excel";
				break;
			case "xlw":
				return 	"application/vnd.ms-excel";
				break;
			case "xml":
				return "text/xml";
				break;
			case "xof":	
				return 	"x-world/x-vrml";
				break;
			case "xpm":
				return 	"image/x-xpixmap";
				break;
			case "xwd":
				return	"image/x-xwindowdump";
				break;
			case "z":
				return 	"application/x-compress";
				break;
			case "zip":
				return 	"application/zip";
				break;
			default:
				return "text/plain";
				break;
		}
		
	
	}
}

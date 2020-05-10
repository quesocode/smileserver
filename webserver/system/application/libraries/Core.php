<?php

class Core
{
  public static $cache = array();
  
  public static function init()
  {
    self::set('start_time', microtime(true));
  }
  public static function set ($name, $value = NULL)
	{
		self::$cache[$name] = $value;
	}
	public static function get ($name)
	{
		return isset(self::$cache[$name]) ? self::$cache[$name] : NULL;
	}
	public static function getModelName($class_name = NULL)
	{
    $object_name_lower = strtolower($class_name);
    $models = self::get('models');
    return isset($models[$object_name_lower]) ? $models[$object_name_lower] : NULL;
	}
	public static function getFriendlyModelNames()
	{
	   $models = self::get('models');
	   foreach($models as $k=>$v)
	   {
	   	
	   	
	     $models[$k] = array('name'=>substr(preg_replace('|([A-Z])|', ' $1', $v), 1), 'class'=>$v);
	   }
	   return $models;
	}
	public static function cacheModelNames($models=array())
	{
	   // setup a static array to use in determining a models proper name including capitalizations
	   if(count($models))
	   {
  	   $model_names = array_change_key_case(array_combine(array_values($models), $models));
  	   foreach($model_names as $key=>$name)
  	   {
  	     $model_names[$key . "s"] = $name;
  	   }
  	   self::set('models', $model_names);
	   }
	}
	public static function isSameFile($oldfile, $newfile)
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
	public static function makeUniqueFile($filepath, $compare_file = NULL, $index = 2, $breakOnSameFile = true)
	{
		if(is_file($filepath))
		{
			if(!is_null($compare_file) && self::isSameFile($compare_file, $filepath) && $breakOnSameFile) return false;
			$file = pathinfo($filepath);
			$filename = $file["filename"];
			if($e = explode(".", $filename))
			{
				$filename = $e[0];
			}
			$new_filename = $filename . '.' . $index. '.' . $file["extension"];
			$newfile = $file["dirname"] . DIRECTORY_SEPARATOR . $new_filename;
			$filepath = self::makeUniqueFile($newfile, $compare_file, $index+1, $breakOnSameFile); 
		}
		return $filepath;
	}
	function purify ($value, $length = NULL)
	{
		// clean up the user specified foldername
		$shortname = stripslashes ( $value );
		//This erase white-spaces on the beginning and the end in each line of a string:
		$shortname = preg_replace('~^(\s*)(.*?)(\s*)$~m', "\\2", $shortname);
		//erases all NON-alfanumerics
		//$shortname = ereg_replace("[^[:word:] ]","",$shortname);
		
		
		//$shortname = 
		
		
		// take out repetative spaces:
		$shortname = preg_replace('/\s\s+/', ' ', $shortname);
		$shortname = str_replace(" ", "_", $shortname);
		if($length)
		{
			if(strlen($shortname) > $length)
			{
				$shortname = substr($shortname, 0, $length);
			}
		}
		return strtolower($shortname);
	}
	
}
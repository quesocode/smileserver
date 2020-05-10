<?php

// YOU MUST EDIT THIS TO BE THE PATH TO YOUR DOCTRINE LIBRARY
// I put mine in a 'libs' folder at the same level as the 
// CI application folder, but it can be anywhere.
require_once APPPATH   	  .  DIRECTORY_SEPARATOR .
				'libraries'     .  DIRECTORY_SEPARATOR . 
				'Doctrine' . DIRECTORY_SEPARATOR .
				'lib' . DIRECTORY_SEPARATOR . 
				'Doctrine.php';
				
require_once 'Inflect.class.php';
				
define('DOCTRINE_PATH', APPPATH . DIRECTORY_SEPARATOR. 'libraries' . DIRECTORY_SEPARATOR . 'Doctrine' . DIRECTORY_SEPARATOR . 'lib'. DIRECTORY_SEPARATOR . 'Doctrine');
define('DATA_FIXTURES_PATH', APPPATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'fixtures');
define('MODELS_PATH', APPPATH . DIRECTORY_SEPARATOR .'models');
define('MIGRATIONS_PATH', APPPATH . DIRECTORY_SEPARATOR .'migrations');
define('SQL_PATH', APPPATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'sql');
define('YAML_SCHEMA_PATH', APPPATH . DIRECTORY_SEPARATOR . 'schema');

$model_names = array();

function bootstrap_doctrine() {
 
	include APPPATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database' . EXT;
  //Doctrine::setModelsDirectory(APPPATH  . 'models');
	// Set the autoloader
	Doctrine::setExtensionsPath(DOCTRINE_PATH . DIRECTORY_SEPARATOR .'Extensions');
	spl_autoload_register(array('Doctrine', 'autoload'));
	spl_autoload_register(array('Doctrine', 'modelsAutoload'));
  spl_autoload_register(array('Doctrine', 'extensionsAutoload'));
  // create instance of doctrine manager
  $manager = Doctrine_Manager::getInstance();
	//optional, you can set this to whatever you want, or not set it at all
	$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);
     
	// Load the Doctrine connection
	// (Notice the use of $active_group here, to make it easy to swap out
	//  you connection based on you database.php configs)
 
	if (!isset($db[$active_group]['dsn'])) {
		//try to create the dsn, if it has not been manually set
		//in your config. I personally would opt to set my
		//dsn manually, but it works either way
		$db[$active_group]['dsn'] = $db[$active_group]['dbdriver'] . 
                        '://' . $db[$active_group]['username'] . 
                        ':' . $db[$active_group]['password']. 
                        '@' . $db[$active_group]['hostname'] . 
                        '/' . $db[$active_group]['database'];
	}
	

 
	$resp = Doctrine_Manager::connection($db[$active_group]['dsn'], "main");
	
	
	// set the validation type
	$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_NONE);
	
	// setup the doctrine extensions
	
  

  
  
  $manager->registerExtension('Sortable');
  //$manager->registerExtension('Taggable');
  $manager->registerExtension('Blameable');
  
	// Load the models for the autoloader
	// This assumes all of your models will exist in you
	// application/models folder

	Doctrine::loadModels(MODELS_PATH);
	
	
	
	
	Core::cacheModelNames(Doctrine::getLoadedModels());
	//var_dump(Doctrine::getLoadedModels());
	
	if(isset($_REQUEST['loadMethods']) && $_REQUEST['loadMethods'] == 'yes')
	{
		$treeObject = Doctrine_Core::getTable('ApiMethod')->getTree();
		foreach(Doctrine::getLoadedModels() as $model)
		{
			$http = array('get'=>'get','create'=>'post','delete'=>'delete', 'update'=>'put');
			
			$model_plural = Inflect::pluralize($model);
			$model_singular = Inflect::singularize($model);
			
			$api_obj = new ApiMethod();
			$api_obj->name = $model_singular;
			$api_obj->class_name = $model_singular;
			$api_obj->function_type = 'other';
			$api_obj->doc_type = 'object';
			$api_obj->save();
			
			$treeObject->createRoot($api_obj);
			
			
			
			foreach($http as $title=>$method)
			{
				// add the method
				$api_method_pl = new ApiMethod();
				$api_method_pl->name = $method . $model_plural;
				$thread_pl = $api_method_pl->thread = $method . $model_plural;
				$api_method_pl->class_name = $model_plural;
				$api_method_pl->function_type = $title;
				$api_method_pl->doc_type = 'method';
				$api_method_pl->save();
				$api_method_pl->getNode()->insertAsLastChildOf($api_obj);
				
				if($thread_pl == 'getUsers')
				{
					$root_id = $api_method_pl->id;
				}
				
				// add the function
				$api_function_pl = new ApiMethod();
				$api_function_pl->name = $method . $model_plural;
				$api_function_pl->thread = $thread_pl;
				$api_function_pl->class_name = $model_plural;
				$api_function_pl->function_type = $title;
				$api_function_pl->doc_type = 'function';
				$api_function_pl->save();
				$api_function_pl->getNode()->insertAsLastChildOf($api_method_pl);
				
				
				
				// add the method
				$api_method_s = new ApiMethod();
				$api_method_s->name = $method . $model_singular;
				$thread_s = $api_method_s->thread = $method . $model_singular;
				$api_method_s->class_name = $model_plural;
				$api_method_s->function_type = $title;
				$api_method_s->doc_type = 'method';
				$api_method_s->save();
				$api_method_s->getNode()->insertAsLastChildOf($api_obj);
				
				// add the function
				$api_function_s = new ApiMethod();
				$api_function_s->name = $method . $model_singular;
				$api_function_s->thread = $thread_s;
				$api_function_s->class_name = $model_plural;
				$api_function_s->function_type = $title;
				$api_function_s->doc_type = 'function';
				$api_function_s->save();
				$api_function_s->getNode()->insertAsLastChildOf($api_method_s);
				
				
				
				$api_method_pl->free(true);
				$api_function_pl->free(true);
				$api_method_s->free(true);
				$api_function_s->free(true);

				
				$api_method_pl = $api_function_pl = $api_method_s = $api_function_s = NULL;
	
			}
		}
		$root = Doctrine::getTable('ApiMethod')->find($root_id);
		$api = new ApiMethod();
		$api->name = 'getUsersAsAdmin';
		$api->thread = 'getUsers';
		$api->class_name = 'Users';
		$api->function_type= 'get';
		$api->doc_type='function';
		$api->save();
		$api->getNode()->insertAsFirstChildOf($root);
		
		
		$um = new UserMethod();
		$um->user_id = 1;
		$um->method_id = $api->id;
		$um->save();
		
		
		$um->free(true);
		
		
		$api2 = new ApiMethod();
		$api2->name = 'getUsersAsDev';
		$api2->thread = 'getUsers';
		$api2->class_name = 'Users';
		$api2->function_type= 'get';
		$api2->doc_type='function';
		$api2->save();
		$api2->getNode()->insertAsNextSiblingOf($api);
		
		$um = new UserMethod();
		$um->user_id = 1;
		$um->method_id = $api2->id;
		$um->save();
		
		$api->free(true);
		$api2->free(true);
		$um->free(true);
		$root->free(true);
		
		$root = $um = $api = $api2 = NULL;
		
	
	}
	
	if(isset($_REQUEST['loadUserMethods']) && $_REQUEST['loadUserMethods'] == 'yes')
	{
		$query = Doctrine_Query::create()->from('ApiMethod a')->where('a.doc_type = ?','function');
		$records = $query->execute();
		var_dump($records->count());
		foreach($records as $record)
		{
			$um = new UserMethod();
			$um->user_id = 1;
			$um->method_id = $record->id;
			$um->save();
			$um->free(true);
			
			$um = new UserMethod();
			$um->user_id = 2;
			$um->method_id = $record->id;
			$um->save();
			$um->free(true);
		}
		
		exit;
	}
	
	
	
	


}
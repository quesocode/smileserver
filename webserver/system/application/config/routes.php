<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
| 	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['scaffolding_trigger'] = 'scaffolding';
|
| This route lets you set a "secret" word that will trigger the
| scaffolding feature for added security. Note: Scaffolding must be
| enabled in the controller in which you intend to use it.   The reserved 
| routes must come before any wildcard or regular expression routes.
|
*/



function _detect_method()
{
	$method = strtolower($_SERVER['REQUEST_METHOD']);
	
	if(in_array($method, array('get', 'delete', 'post', 'put')))
	{
    	     return $method;
	}

	return 'get';
}

$route['scaffolding_trigger'] = "";
$route['default_controller'] = "base";

$route['smilebooth'] = "app/smilebooth";
$route['photo'] = "app/viewphoto";
$route['admin'] = "app/admin";

$route['app/(:any)'] = "app/index";
$route['app'] = "app/index";
$route['thumbit'] = "thumbit/index";
$route['client'] = "client/index";
$route['img'] = "files/images/index";
$route['img/(:any)'] = "files/images/index";
$route['mobile'] = 'mobile/gateway/index';
$route['mobile/send'] = 'mobile/gateway/send_msg';

$route['xd_receiver.htm'] = 'activate/facebook_activate/xd';
$route['(:any)xd_receiver.htm'] = 'activate/facebook_activate/xd';

$route['logout'] = "users/logout";
$route['login'] = "base/index";
$route['src/:any'] = "pagecntrl/script";
$route['users/(:any)'] = "users/userfiles";
$route['register'] = 'users/register';
$route['up'] = 'updates/up';

$route['check'] = 'users/check';
$route['check/:any'] = 'users/check';

$route['login/json'] = 'users/login_json';
$route['logout/json'] = 'users/logout_json';
$route['backstage'] = "users/backstage";
$route['profile'] = "users/profile";
$route['settings'] = "users/settings";
$route['password'] = "users/password";
$route['account/(:any)'] = "users/$1";
$route['recent'] = "users/recent";
$route['post'] = "users/post";
$route['updates/page/:num'] = "users/user_updates";
$route['updates/:num'] = "users/updates";
$route['updates'] = "users/updates";
$route['plugs/page/:num'] = "users/user_plugs";
$route['plugs/:num'] = "users/plugs";
$route['plugs'] = "users/plugs";
$route['plug'] = "plugs/plug";
//$route['twitter'] = 'twittercntrl/index';
$route['twitter/(:any)'] = 'social/tweet/$1';
$route['twitter'] = 'social/tweet';

/* facebook routes */
$route['facebook'] = 'social/facebookapp/canvas';
$route['facebook/test'] = 'social/facebookapp/test';


/* auth routes */

$route['activate/(:any)'] = 'activate/$1_activate';
$route['reactivate/(:any)'] = 'activate/$1_activate/reactivate';

/* core routes */



$method = _detect_method();
$models = Core::getFriendlyModelNames();




/* custom routes */





/* do not add routes past here */


$models_route = array();
foreach($models as $i => $model)
{
	if (file_exists(APPPATH.'controllers/api/'.$i.'_api'.EXT))
	{
		$models_route[$i . '/(:any)'] = 'api/' . $i . '_api';
		$models_route[$i . 's/(:any)'] = 'api/' . $i . '_api';
	}
	else
	{
		$models_route[$i . '/(:any)'] = 'api/api_base/'.$i.'/';
		$models_route[$i . 's/(:any)'] = 'api/api_base/'.$i.'/';
		
		
	}
}

//print '<pre>' . print_r($models_route, true) . '</pre>';
$route = $route+$models_route;
$route['(:any)'] = "updates/share";




/* End of file routes.php */
/* Location: ./system/application/config/routes.php */
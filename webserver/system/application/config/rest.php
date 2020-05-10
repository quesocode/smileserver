<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| REST Login
|--------------------------------------------------------------------------
|
| Is login required and if so, which type of login?
|
|	'' = no login required, 'basic' = unsecure login, 'digest' = more secure login, 'custom' = application level authorization
|
*/
$config['rest_auth'] = 'custom';

/*
|--------------------------------------------------------------------------
| REST Realm
|--------------------------------------------------------------------------
|
| Name for the password protected REST API displayed on login dialogs
|
|
*/
$config['rest_realm'] = 'Columbus API';

/*
|--------------------------------------------------------------------------
| REST Login usernames
|--------------------------------------------------------------------------
|
| Key/Value pair of "backdoor, admin level" usernames and passwords
|
|	
|
*/
$config['rest_valid_logins'] = array('mickeymouse' => 'superapikey', 'demo' => 'password');

/*
|--------------------------------------------------------------------------
| REST Cache
|--------------------------------------------------------------------------
|
| How many MINUTES should output be cached?
|
|	0 = no cache
|
*/
$config['rest_cache'] = 0;

/*
|--------------------------------------------------------------------------
| REST Ignore HTTP Accept
|--------------------------------------------------------------------------
|
| Set to TRUE to ignore the HTTP Accept and speed up each request a little.
| Only do this if you are using the $this->rest_format or /format/xml in URLs 
|
|	FALSE
|
*/
$config['rest_ignore_http_accept'] = FALSE;

$config['default_api_server'] = 'http://dev.columbusapp.com/';
$config['default_media_server'] = 'http://api.columbusapp.com/';
$config['default_app_url'] = 'http://dev.columbusapp.com/app';
$config['default_app_post_url'] = 'http://dev.columbusapp.com/';




/* End of file config.php */
/* Location: ./system/application/config/rest.php */
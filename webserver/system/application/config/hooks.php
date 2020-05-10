<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/


$hook['pre_system'][] = array(
	'function' => 'bootstrap_core',
	'filename' => 'core.php',
	'filepath' => 'hooks'
);

/* Doctrine Hook added by Travis 
  for info on what this does: http://blog.medryx.org/2008/10/04/codeigniter-and-doctrine/

*/


$hook['pre_system'][] = array(
	'function' => 'bootstrap_doctrine',
	'filename' => 'doctrine.php',
	'filepath' => 'hooks'
);



/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */
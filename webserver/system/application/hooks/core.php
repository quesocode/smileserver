<?php

require_once APPPATH   	  . 
  'libraries'     .  DIRECTORY_SEPARATOR . 
  'Core.php';
  
  require_once APPPATH   	  . 
  'libraries'     .  DIRECTORY_SEPARATOR . 
  'Filer.php';
  
function bootstrap_core ()
{
  Core::init();
}


Core::set('twitter_key', 'Ks9ehAel4lygevBD3aqZQg');
Core::set('twitter_secret', 'OUTu777Zb5HuFZEgLsWvSRRf1xqBZQNx2JQdgM');
Core::set('facebook_key', '9d8c34034fd0943d45d01c3e3c5750c7');
Core::set('facebook_secret', 'f6ee2c936d28e41a0e4885a68582f192');
Core::set('flickr_key', 'a76d6d2b11665d119e3ee059f098c2f9');
Core::set('flickr_secret','91aec77005006470');
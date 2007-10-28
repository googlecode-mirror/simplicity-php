<?
//DEBUG MODE
define("SIMPLICITY_DEBUG",true);

//MEMCACHE
define("MEMCACHE_HOST",'localhost');
define("MEMCACHE_PORT",'11211');

//APPLICATION
define("SIMPLICITY_APPNAME","accountability");
define("APPLICATION_BACKEND",'memcache');

//SESSION
define("SESSION_BACKEND",'memcache');
define("SESSION_TIMEOUT",strtotime('1 day') - time());
?>
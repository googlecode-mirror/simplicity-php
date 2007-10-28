<?
$routes = array(
	
	//Defaults
	"default"							=>	'/home',
	"404"								=> 	'/error/view/404',
	"500"								=> 	'/error/view/500',
	
	//Tests
	"/test"								=> 	'/other/their_test',
	"/test/bla"							=> 	'/other/their_test',
	"/test/([a-z0-9_]*)/([a-z0-9_]*)"	=> 	'/other/their_test'

);
?>
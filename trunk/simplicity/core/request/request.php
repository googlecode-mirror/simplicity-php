<?
class Request extends Core {
	
	const LOAD_STATIC = false;
	
	public $url;
	public $params;
	public $post;
	public $get;
	public $method;
	public $client;
	public $server;
	
	public $error;
	
	public $original_request;

	function __construct() {
		$url = isset($_GET['url']) ? $_GET['url'] : ""; 
		$spl = explode("/",$url);
		$spl = Utils::array_strip_empty($spl);
		$spl = Sanitize::string($spl,'._');
		unset($_GET['url']);
		
		$this->url 	= $spl;
		$this->post = $_POST;
		$this->get 	= $_GET;
		
		$this->method = $_SERVER['REQUEST_METHOD'];

		$this->client['ip'] = $_SERVER['REMOTE_ADDR'];
		$lang = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$this->client['language'] = $lang[0];

		$this->server['host'] = $_SERVER['HTTP_HOST'];
		
		$this->original_request = implode('/',$spl);
	}
}
?>
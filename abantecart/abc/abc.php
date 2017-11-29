<?php
namespace abc;
use abc\core\engine\Registry;
use abc\core\helper\AHelperUtils;
use abc\core\engine\ARouter;
use abc\lib\ADebug;

require 'abc_base.php';

/**
 * Class ABC
 * @package abc
  */
class ABC extends ABCBase{

	protected static $env = [];
	/**
	 * ABC constructor.
	 */
	public function __construct(){
		//load and put config into environment
		$files = glob(__DIR__.'/config/*.php');
		foreach($files as $file){
			$config = include_once($file);
			self::env((array)$config);
		}
	}

	/**
	 * Static method for saving environment values into static property
	 * @param $name
	 * @param null $value
	 * @return null
	 */
	public static function env($name, $value = null){
		//if need to get
		if($value === null && !is_array($name)) {
			return array_key_exists($name, self::$env) ? self::$env[$name] : null;
		}
		// if need to set batch of values
		else{
			if(is_array($name)){
				self::$env = array_merge(self::$env,$name);
				return true;
			}else {
				//when set one value
				if (!array_key_exists($name, self::$env)) {
					self::$env[$name] = $value;
					return true;
				}
			}
		}
		return null;
	}

	public function run(){
		$this->_validate_app();

		// New Installation
		if (!self::env('DB_DATABASE')) {
			header('Location: install/index.php');
			exit;
		}

		require 'core/init/app.php';
		$registry = Registry::getInstance();
		ADebug::checkpoint('init end');

		//Route to request process
		$router = new ARouter($registry);
		$registry->set('router', $router);
		$router->processRoute(self::env('ROUTE'));

		// Output
		$registry->get('response')->output();

		if( self::env('IS_ADMIN') === true && $registry->get('config')->get('config_maintenance') && $registry->get('user')->isLogged() ) {
			$user_id = $registry->get('user')->getId();
			AHelperUtils::startStorefrontSession($user_id);
		}

		//Show cache stats if debugging
		if($registry->get('config')->get('config_debug')){
		    ADebug::variable('Cache statistics: ', $registry->get('cache')->stats() . "\n");
		}

		ADebug::checkpoint('app end');

		//display debug info
		if ( $router->getRequestType() == 'page' ) {
		    ADebug::display();
		}
	}

	protected function _validate_app(){

		// Required PHP Version

		if (version_compare(phpversion(), self::env('MIN_PHP_VERSION'), '<') == TRUE) {
			exit( self::env('MIN_PHP_VERSION') . '+ Required for AbanteCart to work properly! Please contact your system administrator or host service provider.');
		}

		if (!function_exists('simplexml_load_file')) {
			exit("simpleXML functions are not available. Please contact your system administrator or host service provider.");
		}


	}
}
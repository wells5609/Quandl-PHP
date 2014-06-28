<?php

class Quandl {
	
	/**
	 * User's API key.
	 * @var string
	 */
	protected static $authtoken;
	
	/**
	 * Cache object
	 * @var Quandl_Cache
	 */
	protected static $cache;
	
	/**
	 * Set the auth token (API key) to use for all requests.
	 * 
	 * @param string $token
	 */
	public static function setAuthToken($token) {
		static::$authtoken = $token;
	}
	
	/**
	 * Returns the auth token, if exists.
	 * 
	 * @return string
	 */
	public static function getAuthToken() {
		return isset(static::$authtoken) ? static::$authtoken : null;
	}
	
	/**
	 * Sets the directory to use for caching Quandl responses.
	 * 
	 * @param string $dir
	 */
	public static function setCacheDirectory($dir) {
			
		if (! $realpath = realpath($dir)) {
			throw new InvalidArgumentException("Directory does not exist.");
		}
		
		static::$cache = new \Quandl\Cache($realpath);
	}
	
	/**
	 * Returns a new Quandl\Url for the given code and manipulations.
	 * 
	 * @param string $quandl_code
	 * @param array $manipulations [Optional]
	 * @param string $format [Optional]
	 * @return Quandl_Url
	 */
	public static function url($quandl_code, array $manipulations = null, $format = null) {
		
		$url = new \Quandl\Url($quandl_code);
		
		if (isset($manipulations)) {
			$url->addManipulations($manipulations);
		}
		
		if (isset($format)) {
			$url->setFormat($format);
		}
		
		return $url;
	}
	
	/**
	 * Returns a new Quandl\Request for the given code and manipulations.
	 * 
	 * @param string $quandl_code
	 * @param array $manipulations [Optional]
	 * @param string $format [Optional]
	 * @return Quandl\Request
	 */
	public static function request($quandl_code, array $manipulations = null, $format = null) {
		
		$request = new \Quandl\Request($quandl_code);
		
		if (isset($manipulations)) {
			$request->addManipulations($manipulations);
		}
		
		if (isset($format)) {
			$request->setFormat($format);
		}
		
		return $request;
	}
	
	/**
	 * Returns a cached response for the given Quandl code, if it exists, otherwise false.
	 * 
	 * @param string $quandl_code
	 * @param array $manipulations [Optional]
	 * @param int $ttl [Optional]
	 * @return Quandl\Response|boolean
	 */
	public static function getCached($quandl_code, $manipulations = null, $ttl = null) {
			
		if (isset(static::$cache)) {
			static::$cache->get($quandl_code, $manipulations, $ttl);
		}
		
		return false;
	}
	
	/**
	 * Caches a response for a given Quandl code, if cache has been set.
	 * 
	 * @param string $quandl_code
	 * @param mixed $response
	 * @param array $manipulations [Optional]
	 * @return boolean
	 */
	public static function cache($quandl_code, $response, $manipulations = null) {
			
		if (isset(static::$cache)) {
			return static::$cache->put($quandl_code, $response, $manipulations);
		}
		
		return false;
	}
	
	/**
	 * Registers an autoloader for the Quandl_* classes.
	 */
	public static function registerAutoloader() {
		return spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	public static function autoload($class) {
		if (0 === strpos($class, 'Quandl\\')) {
			include __DIR__.'/'.str_replace(array('Quandl\\', '\\'), array('', '/'), $class).'.php';
		}
	}
	
}

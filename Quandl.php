<?php

namespace Quandl;

class Quandl {
	
	/**
	 * User's Quandl auth token/API key.
	 * 
	 * @var string
	 */
	protected static $authtoken;
	
	/**
	 * Request adapter object.
	 * 
	 * @var \Quandl\Adapter\AdapterInterface
	 */
	protected static $adapter;
	
	/**
	 * Cache object.
	 * 
	 * @var \Quandl\Cache\CacheInterface
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
	 * Checks whether an auth token is set.
	 * 
	 * @return boolean
	 */
	public static function hasAuthToken() {
		return isset(static::$authtoken);
	}
	
	/**
	 * Sets the request adapter to use for all requests.
	 * 
	 * @param \Quandl\Adapter\AdapterInterface $adapter
	 */
	public static function setAdapter(Adapter\AdapterInterface $adapter) {
		static::$adapter = $adapter;
	}
	
	/**
	 * Returns the request adapter, if set.
	 * 
	 * @return \Quandl\Adapter\AdapterInterface
	 */
	public static function getAdapter() {
		return isset(static::$adapter) ? static::$adapter : null;
	}
	
	/**
	 * Checks whether a request adapter is set.
	 * 
	 * @return boolean
	 */
	public static function hasAdapter() {
		return isset(static::$adapter);
	}
	
	/**
	 * Sets the cache object instance.
	 * 
	 * @param \Quandl\Cache\CacheInterface $cache
	 */
	public static function setCache(Cache\CacheInterface $cache) {
		static::$cache = $cache;
	}
	
	/**
	 * Returns the cache object, if set.
	 * 
	 * @return \Quandl\Cache\CacheInterface
	 */
	public static function getCache() {
		return isset(static::$cache) ? static::$cache : null;
	}
	
	/**
	 * Checks whether a cache is set.
	 * 
	 * @return boolean
	 */
	public static function hasCache() {
		return isset(static::$cache);
	}
	
	/**
	 * Returns a new \Quandl\Url for the given Quandl code and manipulations.
	 * 
	 * @param string $qcode
	 * @param array $manipulations [Optional]
	 * @param string $format [Optional]
	 * @return \Quandl\Url
	 */
	public static function url($qcode, array $manipulations = null, $format = null) {
		
		$url = new Url($qcode);
		
		if (isset($manipulations)) {
			$url->addManipulations($manipulations);
		}
		
		if (isset($format)) {
			$url->setFormat($format);
		}
		
		return $url;
	}
	
	/**
	 * Returns a new \Quandl\Request for the given Quandl code and manipulations.
	 * 
	 * @param string $qcode
	 * @param array $manipulations [Optional]
	 * @param string $format [Optional]
	 * @return \Quandl\Request
	 */
	public static function request($qcode, array $manipulations = null, $format = null) {
		
		$request = new Request($qcode);
		
		if (isset($manipulations)) {
			$request->addManipulations($manipulations);
		}
		
		if (isset($format)) {
			$request->setFormat($format);
		}
		
		return $request;
	}
	
	/**
	 * Returns the best Quandl cache class available on the system.
	 * 
	 * @return string
	 */
	public static function detectCacheClass() {
		
		$caches = array(
			'xcache_get' => 'XCacheCache',
			'apcu_fetch' => 'ApcuCache',
			'apc_fetch' => 'ApcCache',
		);
		
		foreach($caches as $func => $class) {
			if (function_exists($func)) {
				if ('apcu_fetch' === $func && ! apcu_enabled()) {
					continue;
				}
				return 'Quandl\\Cache\\'.$class;
			}
		}
		
		return 'Quandl\\Cache\\FileCache';	
	}
	
	/**
	 * Returns the best Quandl request adapter class available.
	 * 
	 * @return string Class name, or null if none are available.
	 */
	public static function detectAdapterClass() {
		
		$adapters = array(
			'GuzzleHttp\\Client' => 'GuzzleAdapter',
			'Buzz\\Browser' => 'BuzzAdapter',
			'Requests' => 'RequestsAdapter',
		);
		
		foreach($adapters as $class => $qclass) {
			if (class_exists($class, true)) {
				return 'Quandl\\Adapter\\'.$qclass;
			}
		}
		
		if (ini_get('allow_url_fopen')) {
			return 'Quandl\\Adapter\\FopenAdapter';
		}
		
		return null;
	}
	
	/**
	 * Registers an autoloader for the Quandl namespace.
	 */
	public static function registerAutoloader() {
		return spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	/**
	 * Includes a class file in the Quandl\ namespace.
	 */
	public static function autoload($class) {
		if (0 === strpos($class, 'Quandl\\')) {
			include __DIR__.'/'.str_replace(array('Quandl\\', '\\'), array('', '/'), $class).'.php';
		}
	}
	
}

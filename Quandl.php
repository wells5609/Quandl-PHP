<?php

class Quandl {
	
	protected static $authtoken;
	
	public static function setAuthToken($token) {
		self::$authtoken = $token;
	}
	
	public static function getAuthToken() {
		return isset(self::$authtoken) ? self::$authtoken : null;
	}
	
	public static function url($quandl_code, array $manipulations = null, $format = null) {
		
		$url = new Quandl_Url($quandl_code);
		
		if (isset($manipulations)) {
			$url->addManipulations($manipulations);
		}
		
		if (isset($format)) {
			$url->setFormat($format);
		}
		
		return $url;
	}
	
	public static function request($quandl_code, array $manipulations = null, $format = null) {
		
		$url = self::url($quandl_code, $manipulations, $format);
		
		$format = $url->getFormat();
		
		$response = file_get_contents($url);
		
		if (empty($response)) {
			return null;
		}
		
		if ('.json' === $format) {
			return new Quandl_Response(json_decode($response));	
		}
			
		return $response;
	}
	
	public static function registerAutoloader() {
		return spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	public static function autoload($class) {
		if (0 === strpos($class, 'Quandl_')) {
			include __DIR__.'/'.str_replace(array('Quandl_', '_'), array('', '/'), $class).'.php';
		}
	}
	
}

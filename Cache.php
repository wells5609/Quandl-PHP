<?php

namespace Quandl;

class Cache {
	
	const DEFAULT_TTL = 86400;
	
	protected $directory;
	
	/**
	 * Sets the directory in which all cached Quandl responses are stored.
	 */
	public function __construct($directory) {
		
		$this->directory = $directory.DIRECTORY_SEPARATOR;
		
		// make sure classes have methods when unserialized
		ini_set('unserialize_callback_func', 'spl_autoload_call');
	}
	
	/**
	 * Gets a cached response for the given Quandl code and manipulations.
	 */
	public function get($quandl_code, $manipulations = null, $ttl = null) {
		
		isset($ttl) OR $ttl = self::DEFAULT_TTL;
		
		$file = $this->filename($quandl_code, $manipulations);
		
		if (file_exists($file) && filemtime($file) + $ttl > time()) {
			return unserialize(file_get_contents($file));
		}
		
		return null;
	}
	
	/**
	 * Caches a response for the given Quandl code and manipulations.
	 */
	public function put($quandl_code, $response, $manipulations = null) {
		
		if (! is_dir($this->directory.$quandl_code) && ! mkdir($this->directory.$quandl_code, 0777, true)) {
			return false;
		}
		
		$file = $this->filename($quandl_code, $manipulations);
		
		// we dont need all those decimal places
		$precision = ini_set('serialize_precision', 10);
		
		$result = file_put_contents($file, serialize($response), LOCK_EX);
		
		ini_set('serialize_precision', $precision);
		
		return $result;
	}
	
	/**
	 * Returns filename for a given Quandl code and manipulation(s).
	 * If there are manipulations, appends MD5 digest of serialized value to filename.
	 */
	protected function filename($quandl_code, $manipulations = null) {
		
		$file = $this->directory.$quandl_code.'/_';
		
		if (! empty($manipulations)) {
			$file .= md5(serialize($manipulations));
		}
		
		$file .= '.txt';
		
		return $file;
	}
	
}

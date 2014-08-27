<?php

namespace Quandl\Cache;

use Quandl\Response;

class FileCache extends AbstractCache {
	
	const DEFAULT_TTL = 86400;
	
	protected $directory;
	
	public function __construct($dir) {
		$this->setDirectory($dir);
	}
	
	/**
	 * Sets the directory in which all cached Quandl responses are stored.
	 */
	public function setDirectory($directory) {
		
		$this->directory = $directory.DIRECTORY_SEPARATOR;
		
		// make sure classes have methods when unserialized
		ini_set('unserialize_callback_func', 'spl_autoload_call');
	}
	
	/**
	 * Gets a cached response for the given Quandl code and manipulations.
	 */
	public function get($quandl_code, array $manipulations = null, $ttl = null) {
		
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
	public function put($quandl_code, Response $response, array $manipulations = null, $ttl = null) {
		
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
	protected function filename($quandl_code, array $manipulations = null) {
		
		if (! isset($this->directory)) {
			throw new \RuntimeException("Cannot get filename - cache directory not set.");
		}
		
		$file = $this->directory.$quandl_code.'/_';
		
		if (! empty($manipulations)) {
			$file .= md5(serialize($manipulations));
		}
		
		$file .= '.txt';
		
		return $file;
	}
	
}

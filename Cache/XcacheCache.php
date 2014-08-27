<?php

namespace Quandl\Cache;

use Quandl\Response;

class XcacheCache extends AbstractCache {
	
	public function put($quandl_code, Response $response, array $manipulations = null, $ttl = null) {
		
		$quandl_code = $this->prefixCode($quandl_code);
		
		if (isset($manipulations)) {
			$quandl_code .= $this->prepareManipulations($manipulations);
		}
		
		if (! isset($ttl)) {
			$ttl = static::DEFAULT_TTL;
		}
		
		return xcache_set($quandl_code, serialize($response), $ttl);
	}
	
	public function get($quandl_code, array $manipulations = null, $ttl = null) {
		
		$quandl_code = $this->prefixCode($quandl_code);
		
		if (isset($manipulations)) {
			$quandl_code .= $this->prepareManipulations($manipulations);
		}
		
		if ($cached = xcache_get($quandl_code)) {
			return unserialize($cached);
		}
		
		return null;
	}
	
}

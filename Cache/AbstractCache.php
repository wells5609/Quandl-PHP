<?php

namespace Quandl\Cache;

use Quandl\Response;

/**
 * Base class for Quandl cache implementations.
 */
abstract class AbstractCache implements CacheInterface {
	
	const DEFAULT_TTL = 86400;
	
	protected function prefixCode($code) {
		return 'Quandl_'.strtoupper($code);
	}
	
	protected function prepareManipulations(array $manipulations) {
		asort($manipulations);
		return md5(serialize($manipulations));
	}
	
	abstract public function put($quandl_code, Response $response, array $manipulations = null, $ttl = null);
	
	abstract public function get($quandl_code, array $manipulations = null, $ttl = null);
	
}

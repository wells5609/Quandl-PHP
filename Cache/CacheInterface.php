<?php

namespace Quandl\Cache;

use Quandl\Response;

/**
 * Contract for a Quandl cache.
 */
interface CacheInterface {
	
	/**
	 * Caches a response for the given Quandl code and manipulations.
	 * 
	 * @param string $quandl_code Quandl code for the item to store.
	 * @param \Quandl\Response $response Response to store.
	 * @param array $manipulations [Optional] Manipulations corresponding to the 
	 * stored response used to identify it later when retrieving.
	 * @param int $ttl [Optional] Time-to-live.
	 * @return boolean True if successfully stored, otherwise false.
	 */
	public function put($quandl_code, Response $response, array $manipulations = null, $ttl = null);
	
	/**
	 * Retrieves a cached response for the given Quandl code and manipulations.
	 * 
	 * @param string $quandl_code Quandl code for the item to retrieve.
	 * @param array $manipulations [Optional] Manipulations on the item.
	 * @param int $ttl [Optional] Time-to-live.
	 * @return \Quandl\Response|null Stored value if found, otherwise null.
	 */
	public function get($quandl_code, array $manipulations = null, $ttl = null);

}

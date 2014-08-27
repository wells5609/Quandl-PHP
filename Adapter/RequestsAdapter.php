<?php

namespace Quandl\Adapter;

class RequestsAdapter implements AdapterInterface {
	
	public function __construct() {
		\Requests::register_autoloader();
	}
	
	public function request($url, array $options = array()) {
		
		try {
			
			$response = \Requests::get((string)$url);
			
			return $response->body;
		
		} catch (\Exception $e) {
			throw new \RuntimeException('Error fetching URL: '.$e->getMessage(), $e->getCode(), $e);
		}
	}
	
}


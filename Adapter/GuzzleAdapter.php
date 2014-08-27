<?php

namespace Quandl\Adapter;

class GuzzleAdapter implements AdapterInterface {
	
	public function request($url, array $options = array()) {
		
		$client = new \GuzzleHttp\Client();
		
		$response = $client->get((string)$url);
		
		return $response->getBody();
	}
	
}

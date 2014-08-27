<?php

namespace Quandl\Adapter;

class BuzzAdapter implements AdapterInterface {
	
	public function request($url, array $options = array()) {
		
		$client = new \Buzz\Browser();
		
		$response = $client->get((string)$url);
		
		return $response->getContent();
	}
	
}

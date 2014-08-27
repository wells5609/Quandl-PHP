<?php

namespace Quandl\Adapter;

class FopenAdapter implements AdapterInterface {
	
	public function request($url, array $options = array()) {
		
		return file_get_contents((string)$url);
	}
	
}

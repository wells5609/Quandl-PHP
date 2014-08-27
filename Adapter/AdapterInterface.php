<?php

namespace Quandl\Adapter;

interface AdapterInterface {
	
	public function request($url, array $options = array());
	
}

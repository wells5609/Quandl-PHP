<?php

require __DIR__.'/Quandl.php';

\Quandl\Quandl::registerAutoloader();

if (defined('QUANDL_AUTH_TOKEN')) {
	\Quandl\Quandl::setAuthToken(QUANDL_AUTH_TOKEN);
}

function quandl_auth_token($token = null) {
	
	if (! isset($token)) {
		return \Quandl\Quandl::getAuthToken();
	}
	
	\Quandl\Quandl::setAuthToken($token);
}

function quandl_url($quandl_code, array $manipulations = null, $format = null) {
	return \Quandl\Quandl::url($quandl_code, $manipulations, $format);
}

function quandl_request($quandl_code, array $manipulations = null, $format = null) {
	return \Quandl\Quandl::request($quandl_code, $manipulations, $format);
}

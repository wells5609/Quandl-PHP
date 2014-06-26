<?php

class Quandl_Url {
		
	const BASEURL = 'http://www.quandl.com/api/v1/datasets';
	
	protected $source_code;
	protected $table_code;
	protected $manipulations;
	protected $format;
	
	protected static $formats = array(
		'CSV'	=> '.csv',
		'JSON'	=> '.json',
		'XML'	=> '.xml',
		'HTML'	=> '.plain',
	);
	
	public function __construct($quandl_code = null) {
			
		if (isset($quandl_code)) {
			$this->setQuandlCode($quandl_code);
		}
		
		$this->manipulations = array();
		
		// default to JSON
		$this->format = '.json';
	}
	
	public function setQuandlCode($code) {
			
		if (false === strpos($code, '/')) {
			throw new InvalidArgumentException("Quandl code must have both a source code and table code.");
		}
		
		list($source, $table) = explode('/', strtoupper($code));
		
		$this->source_code = $source;
		$this->table_code = $table;
		
		return $this;
	}
	
	public function setSourceCode($code) {
		$this->source_code = strtoupper($code);
		return $this;
	}
	
	public function setTableCode($code) {
		$this->table_code = strtoupper($code);
		return $this;
	}
	
	public function setFormat($format) {

		$format = strtoupper($format);

		if (isset(self::$formats[$format])) {
			$this->format = self::$formats[$format];
		}

		return $this;
	}
	
	public function getQuandlCode() {
		if (isset($this->source_code) && isset($this->table_code)) {
			return $this->source_code.'/'.$this->table_code;
		}
		return null;
	}
	
	public function getSourceCode() {
		return isset($this->source_code) ? $this->source_code : null;
	}
	
	public function getTableCode() {
		return isset($this->table_code) ? $this->table_code : null;
	}
	
	public function getFormat() {
		return $this->format;
	}
	
	public function manipulate($name, $value) {
			
		$manipulation = new Quandl_Manipulation($name, $value);

		$this->manipulations[strtolower($name)] = $manipulation;
		
		return $this;
	}
	
	public function addManipulations(array $manipulations) {
		foreach($manipulations as $key => $value) {
			$this->manipulate($key, $value);
		}
		return $this;
	}
	
	public function getUrl() {
			
		if (! $quandl_code = $this->getQuandlCode()) {
			throw new RuntimeException("Must set Quandl code to build URL.");
		}
		
		$url = self::BASEURL.'/'.$quandl_code.$this->format.'?';
		
		if (! empty($this->manipulations)) {
			foreach($this->manipulations as $object) {
				$url .= $object.'&';
			}
		}
		
		if ($token = Quandl::getAuthToken()) {
			$url .= 'auth_token='.$token;
		} else if (defined('QUANDL_AUTH_TOKEN')) {
			$url .= 'auth_token='.QUANDL_AUTH_TOKEN;
		}
		
		return rtrim($url, '&?');
	}
	
	public function __toString() {	
		try {
			return $this->getUrl();
		} catch (RuntimeException $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
		return '';
	}
	
}

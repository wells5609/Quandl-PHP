<?php

namespace Quandl;

/**
 * Represents a URL for a Quandl API request.
 */
class Url {
	
	/**
	 * Base URL for the Quandl API.
	 * @var string
	 */
	const BASEURL = 'http://www.quandl.com/api/v1/datasets';
	
	/**
	 * Quandl Source code
	 * 
	 * @var string
	 */
	protected $sourceCode;
	
	/**
	 * Quandl Table code
	 * 
	 * @var string
	 */
	protected $tableCode;
	
	/**
	 * Array of Quandl\Manipulation objects.
	 * 
	 * @var array
	 */
	protected $manipulations = array();
	
	/**
	 * Requested response format. Default is json
	 * 
	 * @var string
	 */
	protected $format = '.json';
	
	/**
	 * Valid response formats.
	 * 
	 * @var array
	 */
	protected static $formats = array(
		'JSON'	=> '.json',
		'XML'	=> '.xml',
		'CSV'	=> '.csv',
	);
	
	/**
	 * Sets the Quandl code, if given.
	 * 
	 * @param string|null $quandl_code [Optional]
	 */
	public function __construct($quandl_code = null) {
		if (isset($quandl_code)) {
			$this->setQuandlCode($quandl_code);
		}
	}
	
	/**
	 * Sets the source code.
	 * 
	 * @param string $code
	 */
	public function setSourceCode($code) {
		$this->sourceCode = strtoupper($code);
	}
	
	/**
	 * Sets the table code.
	 * 
	 * @param string $code
	 */
	public function setTableCode($code) {
		$this->tableCode = strtoupper($code);
	}
	
	/**
	 * Sets the Quandl code.
	 * 
	 * @param string $code
	 * 
	 * @throws InvalidArgumentException if missing a source or table code.
	 */
	public function setQuandlCode($code) {
		
		if (! strpos($code, '/')) {
			throw new \InvalidArgumentException("Quandl code must have both a source code and table code.");
		}
		
		list($source, $table) = explode('/', $code);
		
		$this->setSourceCode($source);
		$this->setTableCode($table);
	}
	
	/**
	 * Returns the source code.
	 * 
	 * @return string
	 */
	public function getSourceCode() {
		return isset($this->sourceCode) ? $this->sourceCode : null;
	}
	
	/**
	 * Returns the table code.
	 * 
	 * @return string
	 */
	public function getTableCode($code) {
		return isset($this->tableCode) ? $this->tableCode : null;
	}
	
	/**
	 * Returns the Quandl code, if both the source and table codes are set.
	 * 
	 * @return string|null
	 */
	public function getQuandlCode() {
		
		if (isset($this->sourceCode) && isset($this->tableCode)) {
			return $this->sourceCode.'/'.$this->tableCode;
		}
		
		return null;
	}
	
	/**
	 * Sets the response format.
	 * 
	 * @param string $format
	 * @return boolean False if invalid format, or true if set.
	 */
	public function setFormat($format) {
		
		$format = strtoupper($format);
		
		if (! isset(static::$formats[$format])) {
			throw new \InvalidArgumentException("Invalid format given: '$format'.");
		}
		
		$this->format = static::$formats[$format];
	}
	
	/**
	 * Returns the format.
	 * 
	 * @return string
	 */
	public function getFormat() {
		return $this->format;
	}
	
	/**
	 * Adds a URL manipulation.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function manipulate($name, $value) {
		
		$manipulation = new Manipulation($name, $value);
		
		$this->manipulations[strtolower($name)] = $manipulation;
	}
	
	/**
	 * Adds an array of URL manipulations.
	 * 
	 * @param array $manipulations
	 */
	public function addManipulations(array $manipulations) {
		foreach($manipulations as $key => $value) {
			$this->manipulate($key, $value);
		}
	}
	
	/**
	 * Returns an array of URL manipulations set.
	 * 
	 * @return array
	 */
	public function getManipulations() {
		return $this->manipulations;
	}
	
	public function getManipulation($name) {
		$name = strtolower($name);
		return isset($this->manipulations[$name]) ? $this->manipulations[$name] : null;
	}
	
	/**
	 * Returns the complete URL as a string.
	 * 
	 * @return string
	 * 
	 * @throws RuntimeException if Quandl code is not set.
	 */
	public function getUrl() {
			
		if (! $quandl_code = $this->getQuandlCode()) {
			throw new \RuntimeException("Must set Quandl code to build URL.");
		}
		
		$url = static::BASEURL.'/'.$quandl_code.$this->format.'?';
		
		if (! empty($this->manipulations)) {
			foreach($this->manipulations as $object) {
				$url .= $object.'&';
			}
		}
		
		if ($token = Quandl::getAuthToken()) {
			$url .= 'auth_token='.$token;
		}
		
		return $this->str = rtrim($url, '&?');
	}
	
	/**
	 * Returns the complete URL as a string.
	 * 
	 * @return string
	 */
	public function __toString() {
			
		try {
			return $this->getUrl();
		
		} catch (\RuntimeException $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
		
		return '';
	}
	
}

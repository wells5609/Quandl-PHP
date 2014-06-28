<?php

namespace Quandl;

use InvalidArgumentException;
use RuntimeException;

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
	protected $source_code;
	
	/**
	 * Quandl Table code
	 * 
	 * @var string
	 */
	protected $table_code;
	
	/**
	 * Array of Quandl_Manipulation objects.
	 * 
	 * @var array
	 */
	protected $manipulations;
	
	/**
	 * Requested response format. Default is json
	 * 
	 * @var string
	 */
	protected $format;
	
	/**
	 * Valid response formats.
	 * 
	 * @var array
	 */
	protected static $formats = array(
		'CSV'	=> '.csv',
		'JSON'	=> '.json',
		'XML'	=> '.xml',
		'HTML'	=> '.plain',
	);
	
	/**
	 * Sets the Quandl code, if given.
	 * 
	 * @param string|null $quandl_code [Optional]
	 */
	public function __construct($quandl_code = null) {
			
		$this->manipulations = array();
		
		// default to JSON
		$this->format = '.json';
		
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
		$this->source_code = strtoupper($code);
	}
	
	/**
	 * Returns the source code.
	 * 
	 * @return string
	 */
	public function getSourceCode() {
		return isset($this->source_code) ? $this->source_code : null;
	}
	
	/**
	 * Sets the table code.
	 * 
	 * @param string $code
	 */
	public function setTableCode($code) {
		$this->table_code = strtoupper($code);
	}
	
	/**
	 * Returns the table code.
	 * 
	 * @return string
	 */
	public function getTableCode($code) {
		return isset($this->table_code) ? $this->table_code : null;
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
			throw new InvalidArgumentException("Quandl code must have both a source code and table code.");
		}
		
		list($source, $table) = explode('/', $code);
		
		$this->setSourceCode($source);
		$this->setTableCode($table);
	}
	
	/**
	 * Returns the Quandl code, if both the source and table codes are set.
	 * 
	 * @return string|null
	 */
	public function getQuandlCode() {
		
		if (isset($this->source_code) && isset($this->table_code)) {
			return $this->source_code.'/'.$this->table_code;
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
		
		if (isset(self::$formats[$format])) {
			$this->format = self::$formats[$format];
			return true;
		}
		
		return false;
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
	 * Returns the complete URL as a string.
	 * 
	 * @return string
	 * 
	 * @throws RuntimeException if Quandl code is not set.
	 */
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
		
		if ($token = \Quandl::getAuthToken()) {
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
		} catch (RuntimeException $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}
		return '';
	}
	
}

<?php

namespace Quandl;

class Manipulation {
	
	const TYPE_BOOL = 1;
	const TYPE_NUM = 2;
	const TYPE_STR = 4;
	const TYPE_ENUM = 8;
	const TYPE_DATE = 16;
	
	protected $name;
	protected $value;
	
	protected static $types = array(
		'sort_order'		=> self::TYPE_ENUM,
		'exclude_headers'	=> self::TYPE_BOOL,
		'rows'				=> self::TYPE_NUM,
		'trim_start'		=> self::TYPE_DATE,
		'trim_end'			=> self::TYPE_DATE,
		'column'			=> self::TYPE_NUM,
		'collapse'			=> self::TYPE_ENUM,
		'transformation'	=> self::TYPE_ENUM,
	);
	
	/**
	 * Build and validate the manipulation with the given name and value.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * 
	 * @throws InvalidArgumentException if manipulation is unknown or value is invalid. 
	 */
	public function __construct($name, $value) {
		
		$name = strtolower($name);
		
		if (! static::exists($name)) {
			throw new \InvalidArgumentException("Unknown manipulation given: '$name'.");
		}
		
		if (! static::isValid($name, $value)) {
			throw new \InvalidArgumentException("Invalid manipulation value given for '$name': '$value'.");
		}
		
		if (static::TYPE_DATE === static::$types[$name]) {
			$value = date('Y-m-d', strtotime($value));
		}
		
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * Returns the manipulation for use in URL query string.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->name.'='.$this->value;
	}
	
	/**
	 * Checks whether a given manipulation exists.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function exists($name) {
		return isset(static::$types[$name]);
	}
	
	/**
	 * Returns whether the given manipulation expects a date value.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function expectsDate($name) {
		return static::exists($name) && static::TYPE_DATE === static::$types[$name];
	}
	
	/**
	 * Checks whether the given value is valid for the given manipulation.
	 * 
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 */
	public static function isValid($name, $value) {
		
		if (! static::exists($name)) {
			throw new \InvalidArgumentException("Invalid manipulation: '$name'.");
		}
		
		switch(static::$types[$name]) {
				
			case static::TYPE_NUM:
				return is_numeric($value);
			
			case static::TYPE_STR:
				return is_string($value);
			
			case static::TYPE_BOOL:
				return is_bool($value);
			
			case static::TYPE_DATE:
				return (bool) strtotime($value);
				
			case static::TYPE_ENUM:
				if (! $values = static::getEnumValues($name)) {
					return false;
				}
				return in_array(strtolower($value), $values, true);
			
			default:
				return false;
		}
	}
	
	/**
	 * Returns the enum values allowed for a given manipulation.
	 * 
	 * @param string $name
	 * @return array|null
	 */
	public static function getEnumValues($name) {
		
		switch($name) {
				
			case 'sort_order':
				return array('asc', 'desc');
			
			case 'collapse':
				return array('none', 'daily', 'weekly', 'monthly', 'quarterly', 'annual');
			
			case 'transformation':
				return array('diff', 'rdiff', 'cumul', 'normalize');
		}
	}
	
}

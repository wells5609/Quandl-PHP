<?php

namespace Quandl;

use InvalidArgumentException;

class Manipulation {
	
	const TYPE_BOOL = 1;
	const TYPE_NUM = 2;
	const TYPE_STR = 4;
	const TYPE_ENUM = 8;
	const TYPE_DATE = 16;
	
	protected $name;
	protected $value;
	
	protected static $manipulations = array(
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
		
		if (! self::exists($name)) {
			throw new InvalidArgumentException("Unknown manipulation given: '$name'.");
		}
		
		if (self::isDate($name)) {
			
			if (! $value = strtotime($value)) {
				throw new InvalidArgumentException("Invalid value given for '$name': must be date.");
			}
			
			$value = date('Y-m-d', $value);
			
		} else if (! self::validate($name, $value)) {
			throw new InvalidArgumentException("Invalid value given for manipulation '$name'.");
		}
		
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * Returns the manipulation as a string suitable for a URL.
	 * 
	 * @return string
	 */
	public function __toString() {
		return $this->name.'='.$this->value;
	}
	
	/**
	 * Returns the allowed enum values for a given manipulation.
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
			default:
				return null;
		}
	}
	
	/**
	 * Checks whether a given manipulation is valid.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function exists($name) {
		return isset(self::$manipulations[$name]);
	}
	
	/**
	 * Returns whether the given manipulation expects a date value.
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public static function isDate($name) {
		return isset(self::$manipulations[$name]) && self::TYPE_DATE === self::$manipulations[$name];
	}
	
	/**
	 * Checks whether the given value is valid for the given manipulation.
	 * 
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 */
	public static function validate($name, $value) {
		
		switch(self::$manipulations[$name]) {
				
			case self::TYPE_NUM:
				return is_numeric($value);
			
			case self::TYPE_STR:
				return is_string($value);
			
			case self::TYPE_BOOL:
				return is_bool($value);
			
			case self::TYPE_DATE:
				return (bool) strtotime($value);
				
			case self::TYPE_ENUM:
				if (! $values = self::getEnumValues($name)) {
					return false;
				}
				return in_array(strtolower($value), $values, true);
			
			default:
				return false;
		}
	}
	
}

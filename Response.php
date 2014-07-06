<?php

namespace Quandl;

use InvalidArgumentException;

class Response implements \Serializable {
	
	protected $errors;
	protected $id;
	protected $source_name;
	protected $source_code;
	protected $code;
	protected $name;
	protected $urlize_name;
	protected $description;
	protected $updated_at;
	protected $frequency;
	protected $from_date;
	protected $to_date;
	protected $column_names;
	protected $private;
	protected $type;
	protected $display_url;
	protected $data;
	
	/**
	 * Creates a new Response from a JSON response string.
	 * 
	 * @param string $json JSON string returned from Quandl API.
	 * @return \Quandl\Response
	 */
	public static function createFromJson($json) {
		return new static(json_decode($json));
	}
	
	/**
	 * Creates a new Response from a CSV response string.
	 * 
	 * @param string $csv CSV string returned from Quandl API.
	 * @return \Quandl\Response
	 */
	public static function createFromCsv($csv) {
		
		$rows = str_getcsv($csv, "\n");
		$headers = str_getcsv(array_shift($rows), ',');
		
		$data = array(
			'column_names' => $headers,
			'data' => array()
		);
		
		foreach($rows as $row) {
			$data['data'][] = str_getcsv($row, ',');
		}
		
		return new static($data);
	}
	
	/**
	 * Creates a new Response from an XML response string.
	 * 
	 * @param string $xml XML string returned from Quandl API.
	 * @return \Quandl\Response
	 */
	public static function createFromXml($xml) {
		
		$parsed = json_decode(json_encode(simplexml_load_string($xml)), true);
		$data = array();
		
		foreach($parsed as $key => $value) {
			
			$key = str_replace('-', '_', $key);
			
			switch($key) {
				case 'column_names':
					$value = $value['column-name'];
					break;
				case 'data':
					foreach($value['datum'] as &$obs) {
						$obs = $obs['datum'];
					}
					$value = $value['datum'];
					break;
				case 'type':
					unset($value['@attributes']);
					$value = empty($value) ? null : $value;
					break;
				default:
					break;
			}
			
			$data[$key] = $value;
		}
		
		return new static($data);
	}
	
	/**
	 * Returns an object property, if set.
	 * 
	 * @param string $var
	 * @return mixed
	 */
	public function get($var) {
		return isset($this->$var) ? $this->$var : null;
	}
	
	/**
	 * Returns the index for a given column name.
	 * 
	 * The 'Date' column is always 0. Data columns begin at 1.
	 * 
	 * @param string $name
	 * @return int
	 */
	public function getColumnIndex($name) {
		return array_search($name, $this->column_names, true);
	}
	
	/**
	 * Returns the date on which the dataset was last updated.
	 * 
	 * Will not work for CSV requests.
	 * 
	 * @return string
	 */
	public function getLastUpdate() {
		return isset($this->updated_at) ? $this->updated_at : null;
	}
	
	/**
	 * Returns the number of observations.
	 * 
	 * @return int
	 */
	public function getObservationCount() {
		return count($this->data);
	}
	
	/**
	 * Returns the first observation from the dataset.
	 * 
	 * Does NOT account for sort order - i.e. in ASC order,
	 * returns the oldest observation; in DESC order, returns the
	 * newest observation.
	 * 
	 * @return mixed
	 */
	public function getFirstObservation() {
		$data = $this->data;
		return array_pop($data);
	}
	
	/**
	 * Returns the last observation from the dataset.
	 * 
	 * Does NOT account for sort order - i.e. in ASC order,
	 * returns the newest observation; in DESC order, returns the
	 * oldest observation.
	 * 
	 * @return mixed
	 */
	public function getLastObservation() {
		$data = $this->data;
		return array_shift($data);
	}
	
	/**
	 * Returns the entire dataset.
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
	
	public function mergeData(array $newdata) {
		$this->data = array_merge_recursive($this->data, $newdata);
		return $this;
	}
	
	/**
	 * Checks whether any errors were returned.
	 * 
	 * @return boolean
	 */
	public function isError() {
		if (! empty($this->errors) && $vars = get_object_vars($this->errors)) {
			return ! empty($vars);	
		}
		return false;
	}
	
	/**
	 * Returns the observation for a given date, if it exists.
	 * 
	 * @param string $date Date in any PHP-recognized format (uses strtotime()).
	 * @return array|null|boolean
	 */
	public function getDataFrom($date) {
		
		if (! $time = strtotime($date)) {
			return false;
		}
		if ($time < strtotime($this->from_date)) {
			return false;
		}
		if ($time > strtotime($this->to_date)) {
			return false;
		}
		
		foreach($this->data as $observation) {
			if ($time == strtotime($observation['Date'])) {
				return $observation;
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the observations between two dates.
	 * 
	 * If no ending date is given, returns the observations
	 * up to and including the most recent.
	 * 
	 * @param string $start_date A PHP-recognized date.
	 * @param string $end_date [Optional]
	 * @return array
	 */
	public function getDataBetween($start_date, $end_date = null) {
		
		if (! $start = strtotime($start_date)) {
			return false;
		}
		
		if (! isset($end_date)) {
			$end = time();	
		} else if (! $end = strtotime($end_date)) {
			return false;
		}
		
		$data = array();
		
		foreach($this->data as $key => $observation) {
			
			$obs = strtotime($observation['Date']);
			
			if ($start <= $obs && $end >= $obs) {
				$data[$key] = $observation;
			}
		}
		
		return $data;
	}
	
	/**
	 * Constructs the object using response data.
	 * 
	 * @param mixed $response
	 */
	public function __construct($response) {
		$this->import($response);
	}
	
	/**
	 * Imports data into the object as properties.
	 * 
	 * Additionally attempts to add column names to each 
	 * observation as item keys.
	 * 
	 * @param mixed $data
	 */
	public function import($data) {
		
		foreach((array)$data as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
		
		if (! empty($this->column_names) && ! empty($this->data)) {
			foreach($this->data as &$array) {
				$array = array_combine($this->column_names, $array);
			}
		}
	}
	
	public function export() {
		return get_object_vars($this);
	}
	
	/**
	 * "Upgrades" the object to a new class.
	 * 
	 * @param string $class Classname of object to upgrade to.
	 * @return Object New instance of given class, if it exists.
	 * @throws InvalidArgumentException if class does not exist.
	 */
	public function upgradeObject($class) {
		
		if (! class_exists($class, true)) {
			throw new InvalidArgumentException("Unknown class '$class'.");
		}
		
		return new $class($this->export());
	}
	
	public function serialize() {
		return serialize($this->export());
	}
	
	public function unserialize($serial) {
		$this->import(unserialize($serial));
	}
	
}
